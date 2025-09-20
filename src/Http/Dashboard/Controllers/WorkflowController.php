<?php
/*
 * This file is part of Setka CMS.
 *
 * @package   Setka CMS
 */

declare(strict_types=1);

namespace Setka\Cms\Http\Dashboard\Controllers;

use InvalidArgumentException;
use RuntimeException;
use Setka\Cms\Application\Elements\ElementVersionService;
use Setka\Cms\Contracts\Elements\ElementRepositoryInterface;
use Setka\Cms\Contracts\Workflow\WorkflowRepositoryInterface;
use Setka\Cms\Contracts\Workflow\WorkflowStateRepositoryInterface;
use Setka\Cms\Contracts\Workflow\WorkflowTransitionRepositoryInterface;
use Setka\Cms\Domain\Elements\Element;
use Setka\Cms\Domain\Workflow\Workflow;
use Setka\Cms\Domain\Workflow\WorkflowState;
use Setka\Cms\Domain\Workflow\WorkflowStateType;
use Setka\Cms\Domain\Workflow\WorkflowTransition;
use Setka\Cms\Domain\Workspaces\Workspace;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

final class WorkflowController extends Controller
{
    private const DEFAULT_ROLES = ['author', 'editor', 'publisher', 'admin'];

    public function __construct(
        $id,
        $module,
        private readonly WorkflowRepositoryInterface $workflowRepository,
        private readonly WorkflowStateRepositoryInterface $stateRepository,
        private readonly WorkflowTransitionRepositoryInterface $transitionRepository,
        private readonly ElementRepositoryInterface $elementRepository,
        private readonly ElementVersionService $versionService,
        array $config = []
    ) {
        parent::__construct($id, $module, $config);
    }

    public function actionIndex(): string
    {
        return $this->render('index');
    }

    public function actionStates(): string
    {
        return $this->render('states');
    }

    public function actionTransitions(): string
    {
        return $this->render('transitions');
    }

    public function actionStatesData(): Response
    {
        $workflow = $this->getWorkflow();
        $states = $this->stateRepository->findByWorkflow($workflow);

        return $this->asJson([
            'items' => array_map([$this, 'serialiseState'], $states),
        ]);
    }

    public function actionCreateState(): Response
    {
        $workflow = $this->getWorkflow();
        $payload = Yii::$app->request->post();
        $handle = (string) ($payload['handle'] ?? '');
        $name = (string) ($payload['name'] ?? '');
        $type = $this->resolveStateType($payload['type'] ?? null);
        $color = (string) ($payload['color'] ?? '#3c8dbc');
        $isInitial = isset($payload['is_initial']) ? (bool) $payload['is_initial'] : false;

        $position = count($this->stateRepository->findByWorkflow($workflow)) + 1;
        $state = new WorkflowState(
            workflow: $workflow,
            handle: $handle !== '' ? $handle : 'state-' . $position,
            name: $name !== '' ? $name : 'Новый статус',
            type: $type,
            color: $color !== '' ? $color : '#3c8dbc',
            initial: $isInitial,
            position: $position,
        );

        $this->stateRepository->save($state);

        return $this->asJson([
            'item' => $this->serialiseState($state),
        ]);
    }

    public function actionUpdateState(int $id): Response
    {
        $workflow = $this->getWorkflow();
        $state = $this->stateRepository->findById($workflow, $id);
        if ($state === null) {
            throw new NotFoundHttpException('Статус не найден.');
        }

        $payload = Yii::$app->request->post();
        if (isset($payload['name'])) {
            $state->setName((string) $payload['name']);
        }

        if (isset($payload['handle'])) {
            $state->setHandle((string) $payload['handle']);
        }

        if (isset($payload['type'])) {
            $state->setType($this->resolveStateType($payload['type']));
        }

        if (isset($payload['color'])) {
            $state->setColor((string) $payload['color']);
        }

        if (isset($payload['is_initial'])) {
            $state->markInitial((bool) $payload['is_initial']);
        }

        $this->stateRepository->save($state);

        return $this->asJson([
            'item' => $this->serialiseState($state),
        ]);
    }

    public function actionDeleteState(int $id): Response
    {
        $workflow = $this->getWorkflow();
        $this->stateRepository->delete($workflow, $id);

        return $this->asJson(['success' => true]);
    }

    public function actionReorderStates(): Response
    {
        $workflow = $this->getWorkflow();
        $payload = Yii::$app->request->post();
        $ids = $payload['ids'] ?? [];
        if (!is_array($ids)) {
            throw new BadRequestHttpException('Некорректный список идентификаторов.');
        }

        $orderedIds = array_map(static fn($value) => (int) $value, $ids);
        $this->stateRepository->reorder($workflow, $orderedIds);

        return $this->asJson(['success' => true]);
    }

    public function actionTransitionsData(): Response
    {
        $workflow = $this->getWorkflow();
        $transitions = $this->transitionRepository->findByWorkflow($workflow);

        return $this->asJson([
            'items' => array_map([$this, 'serialiseTransition'], $transitions),
            'roles' => self::DEFAULT_ROLES,
        ]);
    }

    public function actionCreateTransition(): Response
    {
        $workflow = $this->getWorkflow();
        $payload = Yii::$app->request->post();
        $name = (string) ($payload['name'] ?? '');
        $fromId = isset($payload['from_state_id']) ? (int) $payload['from_state_id'] : null;
        $toId = isset($payload['to_state_id']) ? (int) $payload['to_state_id'] : null;
        if ($fromId === null || $toId === null) {
            throw new BadRequestHttpException('Не указаны исходный или целевой статус.');
        }

        $fromState = $this->stateRepository->findById($workflow, $fromId);
        $toState = $this->stateRepository->findById($workflow, $toId);
        if ($fromState === null || $toState === null) {
            throw new NotFoundHttpException('Статус для перехода не найден.');
        }

        $roles = $this->normaliseRoles($payload['roles'] ?? []);
        $transition = new WorkflowTransition(
            workflow: $workflow,
            from: $fromState,
            to: $toState,
            name: $name !== '' ? $name : sprintf('%s → %s', $fromState->getName(), $toState->getName()),
            roles: $roles,
        );

        $this->transitionRepository->save($transition);

        return $this->asJson([
            'item' => $this->serialiseTransition($transition),
        ]);
    }

    public function actionUpdateTransition(int $id): Response
    {
        $workflow = $this->getWorkflow();
        $existing = $this->transitionRepository->findById($workflow, $id);
        if ($existing === null) {
            throw new NotFoundHttpException('Переход не найден.');
        }

        $payload = Yii::$app->request->post();
        $fromId = isset($payload['from_state_id']) ? (int) $payload['from_state_id'] : $existing->getFrom()->getId();
        $toId = isset($payload['to_state_id']) ? (int) $payload['to_state_id'] : $existing->getTo()->getId();
        if ($fromId === null || $toId === null) {
            throw new BadRequestHttpException('Не указаны исходный или целевой статус.');
        }

        $fromState = $this->stateRepository->findById($workflow, $fromId);
        $toState = $this->stateRepository->findById($workflow, $toId);
        if ($fromState === null || $toState === null) {
            throw new NotFoundHttpException('Статус для перехода не найден.');
        }

        $roles = $this->normaliseRoles($payload['roles'] ?? $existing->getRoles());
        $updated = new WorkflowTransition(
            workflow: $workflow,
            from: $fromState,
            to: $toState,
            name: isset($payload['name']) ? (string) $payload['name'] : $existing->getName(),
            roles: $roles,
            id: $existing->getId(),
            uid: $existing->getUid(),
            createdAt: $existing->getCreatedAt(),
        );

        $this->transitionRepository->save($updated);

        return $this->asJson([
            'item' => $this->serialiseTransition($updated),
        ]);
    }

    public function actionDeleteTransition(int $id): Response
    {
        $workflow = $this->getWorkflow();
        $this->transitionRepository->delete($workflow, $id);

        return $this->asJson(['success' => true]);
    }

    public function actionApplyTransition(): Response
    {
        $payload = Yii::$app->request->post();
        $transitionId = isset($payload['transition_id']) ? (int) $payload['transition_id'] : null;
        $role = (string) ($payload['role'] ?? '');
        $elementId = isset($payload['element_id']) ? (int) $payload['element_id'] : null;
        $workspaceId = isset($payload['workspace_id']) ? (int) $payload['workspace_id'] : null;
        $locale = isset($payload['locale']) ? (string) $payload['locale'] : '';
        $version = isset($payload['version']) ? (int) $payload['version'] : null;
        if ($transitionId === null) {
            throw new BadRequestHttpException('Не указан переход.');
        }

        $workflow = $this->getWorkflow();
        $transition = $this->transitionRepository->findById($workflow, $transitionId);
        if ($transition === null) {
            throw new NotFoundHttpException('Переход не найден.');
        }

        if ($role === '') {
            throw new BadRequestHttpException('Не указана роль.');
        }

        $allowed = $transition->canExecute($role);
        if (!$allowed) {
            return $this->asJson([
                'success' => false,
                'message' => 'Роль не имеет прав на выполнение перехода.',
            ]);
        }

        if ($elementId === null || $elementId <= 0) {
            throw new BadRequestHttpException('Не указан элемент для изменения статуса.');
        }

        if ($workspaceId === null || $workspaceId <= 0) {
            throw new BadRequestHttpException('Не указан рабочий простор.');
        }

        $locale = trim($locale);
        if ($locale === '') {
            throw new BadRequestHttpException('Не указан язык элемента.');
        }

        if ($version !== null && $version <= 0) {
            $version = null;
        }

        $workspace = $this->createWorkspaceStub($workspaceId, $locale);
        $element = $this->elementRepository->findById($workspace, $elementId, $locale);
        if ($element === null) {
            throw new NotFoundHttpException('Элемент не найден или недоступен.');
        }

        $fromState = $transition->getFrom();
        $this->assertTransitionCompatible($element, $fromState);

        $targetState = $transition->getTo();
        if ($targetState->getId() === null) {
            throw new BadRequestHttpException('Целевой статус недоступен для применения.');
        }
        $element->setWorkflowState($targetState);

        try {
            $this->applyStateEffects($element, $targetState, $locale, $version);
        } catch (RuntimeException|InvalidArgumentException $exception) {
            return $this->asJson([
                'success' => false,
                'message' => $exception->getMessage(),
            ]);
        }

        return $this->asJson([
            'success' => true,
            'target_state' => $this->serialiseState($targetState),
            'element' => $this->serialiseElement($element),
            'message' => sprintf('Переход «%s» выполнен.', $transition->getName()),
        ]);
    }

    private function getWorkflow(): Workflow
    {
        $workflow = $this->workflowRepository->findDefault();
        if ($workflow !== null) {
            return $workflow;
        }

        $workflow = new Workflow('default', 'Стандартный процесс');
        $this->workflowRepository->save($workflow);

        return $workflow;
    }

    private function resolveStateType(mixed $value): WorkflowStateType
    {
        if (is_string($value)) {
            $type = WorkflowStateType::tryFrom($value);
            if ($type !== null) {
                return $type;
            }
        }

        return WorkflowStateType::Draft;
    }

    /**
     * @param string[] $roles
     * @return string[]
     */
    private function normaliseRoles(array $roles): array
    {
        $filtered = [];
        foreach ($roles as $role) {
            if (!is_string($role) || $role === '') {
                continue;
            }

            $filtered[] = $role;
        }

        return array_values(array_unique($filtered));
    }

    private function serialiseState(WorkflowState $state): array
    {
        return [
            'id' => $state->getId(),
            'handle' => $state->getHandle(),
            'name' => $state->getName(),
            'type' => $state->getType()->value,
            'color' => $state->getColor(),
            'is_initial' => $state->isInitial(),
            'position' => $state->getPosition(),
        ];
    }

    private function serialiseTransition(WorkflowTransition $transition): array
    {
        return [
            'id' => $transition->getId(),
            'name' => $transition->getName(),
            'roles' => $transition->getRoles(),
            'from' => $this->serialiseState($transition->getFrom()),
            'to' => $this->serialiseState($transition->getTo()),
        ];
    }

    private function serialiseElement(Element $element): array
    {
        return [
            'id' => $element->getId(),
            'status' => $element->getStatus()->value,
            'workflow_state_id' => $element->getWorkflowStateId(),
            'workflow_state_type' => $element->getWorkflowStateType()?->value,
        ];
    }

    private function createWorkspaceStub(int $workspaceId, string $locale): Workspace
    {
        $handle = 'workspace-' . $workspaceId;
        $name = 'Workspace #' . $workspaceId;

        return new Workspace($handle, $name, [$locale], [], $workspaceId);
    }

    private function assertTransitionCompatible(Element $element, WorkflowState $fromState): void
    {
        $currentStateId = $element->getWorkflowStateId();
        $fromId = $fromState->getId();

        if ($fromId === null) {
            return;
        }

        if ($currentStateId === null) {
            if ($fromState->isInitial()) {
                return;
            }

            throw new BadRequestHttpException('Элемент ещё не привязан к начальному статусу.');
        }

        if ($currentStateId !== $fromId) {
            throw new BadRequestHttpException('Текущий статус элемента не соответствует выбранному переходу.');
        }
    }

    private function applyStateEffects(Element $element, WorkflowState $targetState, string $locale, ?int $version): void
    {
        $workspace = $element->getCollection()->getWorkspace();
        $type = $targetState->getType();

        if ($type === WorkflowStateType::Published) {
            $this->versionService->publish($element, $locale, $version);

            return;
        }

        if ($type === WorkflowStateType::Archived) {
            $this->versionService->archive($element, $locale);

            return;
        }

        if ($type === WorkflowStateType::Draft) {
            $current = $element->getCurrentVersion($locale);
            if ($current === null || !$current->getStatus()->isDraft()) {
                $this->versionService->createDraft($element, $locale);

                return;
            }
        }

        $this->elementRepository->save($workspace, $element, $locale);
    }
}

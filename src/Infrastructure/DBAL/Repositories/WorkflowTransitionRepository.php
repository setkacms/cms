<?php
/*
 * This file is part of Setka CMS.
 *
 * @package   Setka CMS
 */

declare(strict_types=1);

namespace Setka\Cms\Infrastructure\DBAL\Repositories;

use DateTimeImmutable;
use InvalidArgumentException;
use Setka\Cms\Contracts\Workflow\WorkflowTransitionRepositoryInterface;
use Setka\Cms\Domain\Workflow\Workflow;
use Setka\Cms\Domain\Workflow\WorkflowState;
use Setka\Cms\Domain\Workflow\WorkflowStateType;
use Setka\Cms\Domain\Workflow\WorkflowTransition;
use yii\db\Connection;
use yii\db\Query;

final class WorkflowTransitionRepository implements WorkflowTransitionRepositoryInterface
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function findByWorkflow(Workflow $workflow): array
    {
        $workflowId = $workflow->getId();
        if ($workflowId === null) {
            return [];
        }

        $rows = $this->createBaseQuery($workflowId)
            ->orderBy(['t.id' => SORT_ASC])
            ->all($this->db);

        if ($rows === []) {
            return [];
        }

        $transitions = [];
        foreach ($rows as $row) {
            $transitions[] = $this->hydrate($workflow, $row);
        }

        if ($transitions === []) {
            return [];
        }

        $this->loadRoles($transitions);

        return $transitions;
    }

    public function findById(Workflow $workflow, int $id): ?WorkflowTransition
    {
        $workflowId = $workflow->getId();
        if ($workflowId === null) {
            return null;
        }

        $row = $this->createBaseQuery($workflowId)
            ->andWhere(['t.id' => $id])
            ->limit(1)
            ->one($this->db);

        if ($row === false || $row === null) {
            return null;
        }

        $transition = $this->hydrate($workflow, $row);
        $this->loadRoles([$transition]);

        return $transition;
    }

    public function save(WorkflowTransition $transition): void
    {
        $workflow = $transition->getWorkflow();
        $workflowId = $workflow->getId();
        if ($workflowId === null) {
            throw new InvalidArgumentException('Workflow must be persisted before saving transitions.');
        }

        $fromState = $transition->getFrom();
        $toState = $transition->getTo();
        $fromId = $fromState->getId();
        $toId = $toState->getId();
        if ($fromId === null || $toId === null) {
            throw new InvalidArgumentException('Workflow transition states must be persisted.');
        }

        $timestamp = time();
        $data = [
            'uid' => $transition->getUid(),
            'workflow_id' => $workflowId,
            'name' => $transition->getName(),
            'from_state_id' => $fromId,
            'to_state_id' => $toId,
            'updated_at' => $timestamp,
        ];

        $id = $transition->getId();
        if ($id === null) {
            $data['created_at'] = $timestamp;
            $this->db->createCommand()
                ->insert('{{%workflow_transition}}', $data)
                ->execute();

            $generatedId = (int) $this->db->getLastInsertID();
            $date = new DateTimeImmutable('@' . $timestamp);
            $transition->markPersisted($generatedId, $date, $date);
            $this->persistRoles($generatedId, $transition->getRoles());

            return;
        }

        $this->db->createCommand()
            ->update('{{%workflow_transition}}', $data, ['id' => $id, 'workflow_id' => $workflowId])
            ->execute();

        $transition->markPersisted(
            $id,
            $transition->getCreatedAt(),
            new DateTimeImmutable('@' . $timestamp)
        );
        $this->persistRoles($id, $transition->getRoles());
    }

    public function delete(Workflow $workflow, int $id): void
    {
        $workflowId = $workflow->getId();
        if ($workflowId === null) {
            return;
        }

        $this->db->createCommand()
            ->delete('{{%workflow_transition_role}}', ['transition_id' => $id])
            ->execute();

        $this->db->createCommand()
            ->delete('{{%workflow_transition}}', ['id' => $id, 'workflow_id' => $workflowId])
            ->execute();
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrate(Workflow $workflow, array $row): WorkflowTransition
    {
        $from = $this->hydrateState($workflow, $row, 'from');
        $to = $this->hydrateState($workflow, $row, 'to');

        $transition = new WorkflowTransition(
            workflow: $workflow,
            from: $from,
            to: $to,
            name: (string) ($row['name'] ?? 'Transition'),
            roles: [],
            id: isset($row['id']) ? (int) $row['id'] : null,
            uid: isset($row['uid']) ? (string) $row['uid'] : null,
            createdAt: isset($row['created_at']) && $row['created_at'] !== null
                ? new DateTimeImmutable('@' . (int) $row['created_at'])
                : null,
            updatedAt: isset($row['updated_at']) && $row['updated_at'] !== null
                ? new DateTimeImmutable('@' . (int) $row['updated_at'])
                : null,
        );

        return $transition;
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrateState(Workflow $workflow, array $row, string $prefix): WorkflowState
    {
        $typeKey = $prefix . '_type';
        $typeValue = isset($row[$typeKey]) ? (string) $row[$typeKey] : WorkflowStateType::Draft->value;
        $type = WorkflowStateType::tryFrom($typeValue) ?? WorkflowStateType::Draft;

        return new WorkflowState(
            workflow: $workflow,
            handle: (string) ($row[$prefix . '_handle'] ?? 'state'),
            name: (string) ($row[$prefix . '_name'] ?? 'State'),
            type: $type,
            color: (string) ($row[$prefix . '_color'] ?? '#3c8dbc'),
            initial: isset($row[$prefix . '_is_initial']) ? ((int) $row[$prefix . '_is_initial']) === 1 : false,
            position: isset($row[$prefix . '_position']) ? (int) $row[$prefix . '_position'] : 0,
            id: isset($row[$prefix . '_id']) ? (int) $row[$prefix . '_id'] : null,
            uid: isset($row[$prefix . '_uid']) ? (string) $row[$prefix . '_uid'] : null,
            createdAt: isset($row[$prefix . '_created_at']) && $row[$prefix . '_created_at'] !== null
                ? new DateTimeImmutable('@' . (int) $row[$prefix . '_created_at'])
                : null,
            updatedAt: isset($row[$prefix . '_updated_at']) && $row[$prefix . '_updated_at'] !== null
                ? new DateTimeImmutable('@' . (int) $row[$prefix . '_updated_at'])
                : null,
        );
    }

    /**
     * @param WorkflowTransition[] $transitions
     */
    private function loadRoles(array $transitions): void
    {
        $ids = [];
        foreach ($transitions as $transition) {
            $id = $transition->getId();
            if ($id !== null) {
                $ids[] = $id;
            }
        }

        if ($ids === []) {
            return;
        }

        $rows = (new Query())
            ->from('{{%workflow_transition_role}}')
            ->where(['transition_id' => $ids])
            ->all($this->db);

        if ($rows === []) {
            return;
        }

        $map = [];
        foreach ($rows as $row) {
            $transitionId = isset($row['transition_id']) ? (int) $row['transition_id'] : null;
            $role = isset($row['role']) ? (string) $row['role'] : '';
            if ($transitionId === null || $role === '') {
                continue;
            }

            $map[$transitionId][] = $role;
        }

        foreach ($transitions as $transition) {
            $id = $transition->getId();
            if ($id === null) {
                continue;
            }

            $transition->replaceRoles($map[$id] ?? []);
        }
    }

    private function persistRoles(int $transitionId, array $roles): void
    {
        $this->db->createCommand()
            ->delete('{{%workflow_transition_role}}', ['transition_id' => $transitionId])
            ->execute();

        if ($roles === []) {
            return;
        }

        foreach ($roles as $role) {
            if ($role === '') {
                continue;
            }

            $this->db->createCommand()
                ->insert('{{%workflow_transition_role}}', [
                    'transition_id' => $transitionId,
                    'role' => $role,
                ])
                ->execute();
        }
    }

    private function createBaseQuery(int $workflowId): Query
    {
        return (new Query())
            ->select([
                't.id',
                't.uid',
                't.workflow_id',
                't.name',
                't.from_state_id',
                't.to_state_id',
                't.created_at',
                't.updated_at',
                'from_id' => 'fs.id',
                'from_uid' => 'fs.uid',
                'from_handle' => 'fs.handle',
                'from_name' => 'fs.name',
                'from_type' => 'fs.type',
                'from_color' => 'fs.color',
                'from_is_initial' => 'fs.is_initial',
                'from_position' => 'fs.position',
                'from_created_at' => 'fs.created_at',
                'from_updated_at' => 'fs.updated_at',
                'to_id' => 'ts.id',
                'to_uid' => 'ts.uid',
                'to_handle' => 'ts.handle',
                'to_name' => 'ts.name',
                'to_type' => 'ts.type',
                'to_color' => 'ts.color',
                'to_is_initial' => 'ts.is_initial',
                'to_position' => 'ts.position',
                'to_created_at' => 'ts.created_at',
                'to_updated_at' => 'ts.updated_at',
            ])
            ->from(['t' => '{{%workflow_transition}}'])
            ->innerJoin(['fs' => '{{%workflow_state}}'], 'fs.id = t.from_state_id')
            ->innerJoin(['ts' => '{{%workflow_state}}'], 'ts.id = t.to_state_id')
            ->where(['t.workflow_id' => $workflowId]);
    }
}

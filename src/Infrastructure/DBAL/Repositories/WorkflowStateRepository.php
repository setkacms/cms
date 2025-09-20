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
use Setka\Cms\Contracts\Workflow\WorkflowStateRepositoryInterface;
use Setka\Cms\Domain\Workflow\Workflow;
use Setka\Cms\Domain\Workflow\WorkflowState;
use Setka\Cms\Domain\Workflow\WorkflowStateType;
use yii\db\Connection;
use yii\db\Query;

final class WorkflowStateRepository implements WorkflowStateRepositoryInterface
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

        $rows = (new Query())
            ->from('{{%workflow_state}}')
            ->where(['workflow_id' => $workflowId])
            ->orderBy(['position' => SORT_ASC, 'id' => SORT_ASC])
            ->all($this->db);

        if ($rows === []) {
            return [];
        }

        $states = [];
        foreach ($rows as $row) {
            $states[] = $this->hydrate($workflow, $row);
        }

        return $states;
    }

    public function findById(Workflow $workflow, int $id): ?WorkflowState
    {
        $workflowId = $workflow->getId();
        if ($workflowId === null) {
            return null;
        }

        $row = (new Query())
            ->from('{{%workflow_state}}')
            ->where(['id' => $id, 'workflow_id' => $workflowId])
            ->limit(1)
            ->one($this->db);

        if ($row === false || $row === null) {
            return null;
        }

        return $this->hydrate($workflow, $row);
    }

    public function save(WorkflowState $state): void
    {
        $workflow = $state->getWorkflow();
        $workflowId = $workflow->getId();
        if ($workflowId === null) {
            throw new InvalidArgumentException('Workflow must be persisted before saving states.');
        }

        $timestamp = time();
        $data = [
            'uid' => $state->getUid(),
            'workflow_id' => $workflowId,
            'handle' => $state->getHandle(),
            'name' => $state->getName(),
            'type' => $state->getType()->value,
            'color' => $state->getColor(),
            'is_initial' => $state->isInitial() ? 1 : 0,
            'position' => $state->getPosition(),
            'updated_at' => $timestamp,
        ];

        $id = $state->getId();
        if ($id === null) {
            $data['created_at'] = $timestamp;
            $this->db->createCommand()
                ->insert('{{%workflow_state}}', $data)
                ->execute();

            $generatedId = (int) $this->db->getLastInsertID();
            $date = new DateTimeImmutable('@' . $timestamp);
            $state->markPersisted($generatedId, $date, $date);

            return;
        }

        $this->db->createCommand()
            ->update('{{%workflow_state}}', $data, ['id' => $id, 'workflow_id' => $workflowId])
            ->execute();

        $state->markPersisted(
            $id,
            $state->getCreatedAt(),
            new DateTimeImmutable('@' . $timestamp)
        );
    }

    public function delete(Workflow $workflow, int $id): void
    {
        $workflowId = $workflow->getId();
        if ($workflowId === null) {
            return;
        }

        $this->db->createCommand()
            ->delete('{{%workflow_state}}', ['id' => $id, 'workflow_id' => $workflowId])
            ->execute();
    }

    public function reorder(Workflow $workflow, array $orderedIds): void
    {
        $workflowId = $workflow->getId();
        if ($workflowId === null) {
            return;
        }

        $position = 1;
        foreach ($orderedIds as $id) {
            $this->db->createCommand()
                ->update('{{%workflow_state}}', ['position' => $position], ['id' => $id, 'workflow_id' => $workflowId])
                ->execute();
            $position++;
        }
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrate(Workflow $workflow, array $row): WorkflowState
    {
        $typeValue = isset($row['type']) ? (string) $row['type'] : WorkflowStateType::Draft->value;
        $type = WorkflowStateType::tryFrom($typeValue) ?? WorkflowStateType::Draft;

        $state = new WorkflowState(
            workflow: $workflow,
            handle: (string) ($row['handle'] ?? 'state'),
            name: (string) ($row['name'] ?? 'State'),
            type: $type,
            color: (string) ($row['color'] ?? '#3c8dbc'),
            initial: isset($row['is_initial']) ? ((int) $row['is_initial']) === 1 : false,
            position: isset($row['position']) ? (int) $row['position'] : 0,
            id: isset($row['id']) ? (int) $row['id'] : null,
            uid: isset($row['uid']) ? (string) $row['uid'] : null,
            createdAt: isset($row['created_at']) && $row['created_at'] !== null
                ? new DateTimeImmutable('@' . (int) $row['created_at'])
                : null,
            updatedAt: isset($row['updated_at']) && $row['updated_at'] !== null
                ? new DateTimeImmutable('@' . (int) $row['updated_at'])
                : null,
        );

        return $state;
    }
}

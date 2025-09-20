<?php
/*
 * This file is part of Setka CMS.
 *
 * @package   Setka CMS
 */

declare(strict_types=1);

namespace Setka\Cms\Infrastructure\DBAL\Repositories;

use DateTimeImmutable;
use Setka\Cms\Contracts\Workflow\WorkflowRepositoryInterface;
use Setka\Cms\Domain\Workflow\Workflow;
use yii\db\Connection;
use yii\db\Query;

final class WorkflowRepository implements WorkflowRepositoryInterface
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function findById(int $id): ?Workflow
    {
        $row = (new Query())
            ->from('{{%workflow}}')
            ->where(['id' => $id])
            ->limit(1)
            ->one($this->db);

        if ($row === false || $row === null) {
            return null;
        }

        return $this->hydrate($row);
    }

    public function findDefault(): ?Workflow
    {
        $row = (new Query())
            ->from('{{%workflow}}')
            ->orderBy(['id' => SORT_ASC])
            ->limit(1)
            ->one($this->db);

        if ($row === false || $row === null) {
            return null;
        }

        return $this->hydrate($row);
    }

    public function save(Workflow $workflow): void
    {
        $timestamp = time();
        $data = [
            'uid' => $workflow->getUid(),
            'handle' => $workflow->getHandle(),
            'name' => $workflow->getName(),
            'description' => $workflow->getDescription(),
            'updated_at' => $timestamp,
        ];

        $id = $workflow->getId();
        if ($id === null) {
            $data['created_at'] = $timestamp;
            $this->db->createCommand()
                ->insert('{{%workflow}}', $data)
                ->execute();

            $generatedId = (int) $this->db->getLastInsertID();
            $date = new DateTimeImmutable('@' . $timestamp);
            $workflow->markPersisted($generatedId, $workflow->getUid(), $date, $date);

            return;
        }

        $this->db->createCommand()
            ->update('{{%workflow}}', $data, ['id' => $id])
            ->execute();

        $workflow->markPersisted(
            $id,
            $workflow->getUid(),
            $workflow->getCreatedAt(),
            new DateTimeImmutable('@' . $timestamp)
        );
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrate(array $row): Workflow
    {
        $workflow = new Workflow(
            handle: (string) ($row['handle'] ?? 'default'),
            name: (string) ($row['name'] ?? 'Workflow'),
            description: (string) ($row['description'] ?? ''),
            id: isset($row['id']) ? (int) $row['id'] : null,
            uid: isset($row['uid']) ? (string) $row['uid'] : null,
            createdAt: isset($row['created_at']) && $row['created_at'] !== null
                ? new DateTimeImmutable('@' . (int) $row['created_at'])
                : null,
            updatedAt: isset($row['updated_at']) && $row['updated_at'] !== null
                ? new DateTimeImmutable('@' . (int) $row['updated_at'])
                : null,
        );

        return $workflow;
    }
}

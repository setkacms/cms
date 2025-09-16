<?php
/*
 * This file is part of Setka CMS.
 *
 * Copyright (c) 2025 Vitaliy Kamelin. All rights reserved.
 * Proprietary license. Unauthorized copying, modification or distribution
 * of this file, via any medium, is strictly prohibited without prior written permission.
 *
 * @package   Setka CMS
 * @version   1.0.0
 * @author    Vitaliy Kamelin <v.kamelin@gmail.com>
 * @license   Proprietary
 *
 * https://github.com/setkacms/cms
 * See LICENSE file for details.
*/

declare(strict_types=1);

namespace Setka\Cms\Infrastructure\DBAL\Repositories;

use InvalidArgumentException;
use Setka\Cms\Contracts\Fields\FieldRepositoryInterface;
use Setka\Cms\Domain\Fields\Field;
use Setka\Cms\Domain\Fields\FieldType;
use Setka\Cms\Domain\Workspaces\Workspace;
use yii\db\Connection;
use yii\db\Query;

final class FieldRepository implements FieldRepositoryInterface
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function findById(Workspace $workspace, int $id, ?string $locale = null): ?Field
    {
        $row = (new Query())
            ->from('{{%field}}')
            ->where([
                'id' => $id,
                'workspace_id' => $this->requireWorkspaceId($workspace),
            ])
            ->one($this->db);

        return $row ? $this->hydrate($row) : null;
    }

    public function findByHandle(Workspace $workspace, string $handle, ?string $locale = null): ?Field
    {
        $row = (new Query())
            ->from('{{%field}}')
            ->where([
                'handle' => $handle,
                'workspace_id' => $this->requireWorkspaceId($workspace),
            ])
            ->one($this->db);

        return $row ? $this->hydrate($row) : null;
    }

    public function save(Workspace $workspace, Field $field, ?string $locale = null): void
    {
        $workspaceId = $this->requireWorkspaceId($workspace);
        $existing = $this->findByHandle($workspace, $field->getHandle(), $locale);

        $data = [
            'handle' => $field->getHandle(),
            'name' => $field->getHandle(),
            'type' => $field->getType()->value,
            'required' => $field->isRequired() ? 1 : 0,
            'workspace_id' => $workspaceId,
            'updated_at' => time(),
        ];

        if ($existing) {
            $uid = $this->getRowUidByHandle($workspaceId, $field->getHandle());
            $data['uid'] = $uid ?? $this->generateUid();
            $this->db->createCommand()
                ->update('{{%field}}', $data, [
                    'handle' => $field->getHandle(),
                    'workspace_id' => $workspaceId,
                ])
                ->execute();

            return;
        }

        $data['uid'] = $this->generateUid();
        $data['created_at'] = time();
        $this->db->createCommand()
            ->insert('{{%field}}', $data)
            ->execute();
    }

    public function delete(Workspace $workspace, int $id, ?string $locale = null): void
    {
        $this->db->createCommand()
            ->delete('{{%field}}', [
                'id' => $id,
                'workspace_id' => $this->requireWorkspaceId($workspace),
            ])
            ->execute();
    }

    /**
     * @param array<string,mixed> $row
     */
    private function hydrate(array $row): Field
    {
        $type = FieldType::from((string) $row['type']);

        return new Field(
            handle: (string) $row['handle'],
            name: (string) ($row['name'] ?? $row['handle']),
            type: $type,
            required: (bool) $row['required'],
            id: isset($row['id']) ? (int) $row['id'] : null,
            uid: isset($row['uid']) ? (string) $row['uid'] : null,
        );
    }

    private function getRowUidByHandle(int $workspaceId, string $handle): ?string
    {
        $uid = (new Query())
            ->select('uid')
            ->from('{{%field}}')
            ->where([
                'handle' => $handle,
                'workspace_id' => $workspaceId,
            ])
            ->scalar($this->db);

        return $uid ? (string) $uid : null;
    }

    private function generateUid(): string
    {
        return bin2hex(random_bytes(16));
    }

    private function requireWorkspaceId(Workspace $workspace): int
    {
        $workspaceId = $workspace->getId();
        if ($workspaceId === null) {
            throw new InvalidArgumentException('Workspace must have an identifier to be used with repository operations.');
        }

        return $workspaceId;
    }
}

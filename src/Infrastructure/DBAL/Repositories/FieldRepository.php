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
use JsonException;
use Setka\Cms\Contracts\Fields\FieldRepositoryInterface;
use Setka\Cms\Domain\Fields\Field;
use Setka\Cms\Domain\Fields\FieldType;
use Setka\Cms\Domain\Workspaces\Workspace;
use yii\db\Connection;
use yii\db\Query;
use function is_array;
use function json_decode;
use function json_encode;

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
            'uid' => $existing?->getUid() ?? $field->getUid(),
            'handle' => $field->getHandle(),
            'name' => $field->getName(),
            'type' => $field->getType()->value,
            'required' => $field->isRequired() ? 1 : 0,
            'settings' => $this->encodeSettings($field->getSettings()),
            'localized' => $field->isLocalized() ? 1 : 0,
            'is_unique' => $field->isUnique() ? 1 : 0,
            'searchable' => $field->isSearchable() ? 1 : 0,
            'multi_valued' => $field->isMultiValued() ? 1 : 0,
            'workspace_id' => $workspaceId,
            'updated_at' => time(),
        ];

        if ($existing) {
            $id = $existing->getId();
            $condition = [
                'handle' => $field->getHandle(),
                'workspace_id' => $workspaceId,
            ];

            if ($id !== null) {
                $condition = ['id' => $id, 'workspace_id' => $workspaceId];
            }

            $this->db->createCommand()
                ->update('{{%field}}', $data, $condition)
                ->execute();

            return;
        }

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
            settings: $this->decodeSettings($row['settings'] ?? null),
            localized: (bool) ($row['localized'] ?? false),
            unique: (bool) ($row['is_unique'] ?? false),
            searchable: (bool) ($row['searchable'] ?? false),
            multiValued: (bool) ($row['multi_valued'] ?? false),
            id: isset($row['id']) ? (int) $row['id'] : null,
            uid: isset($row['uid']) ? (string) $row['uid'] : null,
        );
    }

    private function encodeSettings(array $settings): string
    {
        try {
            return json_encode($settings, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return '{}';
        }
    }

    private function decodeSettings(mixed $settings): array
    {
        if (is_array($settings)) {
            return $settings;
        }

        if ($settings === null || $settings === '') {
            return [];
        }

        try {
            $decoded = json_decode((string) $settings, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return [];
        }

        return is_array($decoded) ? $decoded : [];
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


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
 * @author    Vitaliy Kamelин <v.kamelин@gmail.com>
 * @license   Proprietary
 *
 * https://github.com/setkacms/cms
 * See LICENSE file for details.
 */

declare(strict_types=1);

namespace Setka\Cms\Infrastructure\DBAL\Repositories;

use DateTimeImmutable;
use Exception;
use JsonException;
use Setka\Cms\Contracts\Assets\ElementAssetRepositoryInterface;
use Setka\Cms\Domain\Assets\ElementAsset;
use Setka\Cms\Domain\Assets\ElementAssetCollection;
use yii\db\Connection;
use yii\db\Query;
use function array_map;
use function array_values;
use function is_array;
use function is_numeric;
use function json_decode;
use function json_encode;
use function time;

final class ElementAssetRepository implements ElementAssetRepositoryInterface
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function findByElement(int $workspaceId, int $elementId, ?string $role = null): ElementAssetCollection
    {
        $query = (new Query())
            ->from('{{%element_asset}}')
            ->where([
                'workspace_id' => $workspaceId,
                'element_id' => $elementId,
            ])
            ->orderBy(['role' => SORT_ASC, 'position' => SORT_ASC, 'id' => SORT_ASC]);

        if ($role !== null && $role !== '') {
            $query->andWhere(['role' => $role]);
        }

        $rows = $query->all($this->db);
        $attachments = [];
        foreach ($rows as $row) {
            $attachments[] = $this->hydrate($row);
        }

        return new ElementAssetCollection(...$attachments);
    }

    public function findOne(int $workspaceId, int $elementId, int $assetId, string $role): ?ElementAsset
    {
        $row = (new Query())
            ->from('{{%element_asset}}')
            ->where([
                'workspace_id' => $workspaceId,
                'element_id' => $elementId,
                'asset_id' => $assetId,
                'role' => $role,
            ])
            ->one($this->db);

        return $row ? $this->hydrate($row) : null;
    }

    public function save(ElementAsset $attachment): void
    {
        $now = time();
        $data = [
            'workspace_id' => $attachment->getWorkspaceId(),
            'element_id' => $attachment->getElementId(),
            'asset_id' => $attachment->getAssetId(),
            'role' => $attachment->getRole(),
            'position' => $attachment->getPosition(),
            'variants' => $this->encodeVariants($attachment->getVariants()),
            'updated_at' => $now,
        ];

        $id = $attachment->getId();
        if ($id === null) {
            $data['created_at'] = $now;
            $this->db->createCommand()
                ->insert('{{%element_asset}}', $data)
                ->execute();

            $attachment->defineId((int) $this->db->getLastInsertID());

            return;
        }

        $this->db->createCommand()
            ->update('{{%element_asset}}', $data, ['id' => $id])
            ->execute();
    }

    public function delete(ElementAsset $attachment): void
    {
        $id = $attachment->getId();
        if ($id !== null) {
            $this->db->createCommand()
                ->delete('{{%element_asset}}', ['id' => $id])
                ->execute();

            return;
        }

        $this->db->createCommand()
            ->delete(
                '{{%element_asset}}',
                [
                    'workspace_id' => $attachment->getWorkspaceId(),
                    'element_id' => $attachment->getElementId(),
                    'asset_id' => $attachment->getAssetId(),
                    'role' => $attachment->getRole(),
                ]
            )
            ->execute();
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrate(array $row): ElementAsset
    {
        return new ElementAsset(
            workspaceId: isset($row['workspace_id']) ? (int) $row['workspace_id'] : 0,
            elementId: isset($row['element_id']) ? (int) $row['element_id'] : 0,
            assetId: isset($row['asset_id']) ? (int) $row['asset_id'] : 0,
            role: (string) ($row['role'] ?? ''),
            position: isset($row['position']) ? (int) $row['position'] : 0,
            variants: $this->decodeVariants($row['variants'] ?? null),
            id: isset($row['id']) ? (int) $row['id'] : null,
            createdAt: $this->createDateTime($row['created_at'] ?? null),
            updatedAt: $this->createDateTime($row['updated_at'] ?? null),
        );
    }

    /**
     * @param string[] $variants
     */
    private function encodeVariants(array $variants): string
    {
        try {
            return json_encode($variants, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return '[]';
        }
    }

    /**
     * @return string[]
     */
    private function decodeVariants(mixed $variants): array
    {
        if ($variants === null || $variants === '') {
            return [];
        }

        if (is_array($variants)) {
            return array_values(array_map(static fn(mixed $value): string => (string) $value, $variants));
        }

        try {
            $decoded = json_decode((string) $variants, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return [];
        }

        if (!is_array($decoded)) {
            return [];
        }

        return array_values(array_map(static fn(mixed $value): string => (string) $value, $decoded));
    }

    private function createDateTime(mixed $timestamp): DateTimeImmutable
    {
        if ($timestamp instanceof DateTimeImmutable) {
            return $timestamp;
        }

        $value = is_numeric($timestamp) ? (int) $timestamp : time();

        try {
            return (new DateTimeImmutable())->setTimestamp($value);
        } catch (Exception) {
            return new DateTimeImmutable();
        }
    }
}

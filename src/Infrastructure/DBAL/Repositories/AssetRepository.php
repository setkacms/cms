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
use InvalidArgumentException;
use JsonException;
use Setka\Cms\Contracts\Assets\AssetRepositoryInterface;
use Setka\Cms\Domain\Assets\Asset;
use Setka\Cms\Domain\Assets\AssetVariant;
use Setka\Cms\Domain\Workspaces\Workspace;
use yii\db\Connection;
use yii\db\Query;
use function is_array;
use function is_numeric;
use function json_decode;
use function json_encode;
use function time;

final class AssetRepository implements AssetRepositoryInterface
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function findById(Workspace $workspace, int $id): ?Asset
    {
        $row = (new Query())
            ->from('{{%asset}}')
            ->where([
                'id' => $id,
                'workspace_id' => $this->requireWorkspaceId($workspace),
            ])
            ->one($this->db);

        return $row ? $this->hydrate($workspace, $row) : null;
    }

    public function findByUid(Workspace $workspace, string $uid): ?Asset
    {
        $row = (new Query())
            ->from('{{%asset}}')
            ->where([
                'uid' => $uid,
                'workspace_id' => $this->requireWorkspaceId($workspace),
            ])
            ->one($this->db);

        return $row ? $this->hydrate($workspace, $row) : null;
    }

    public function save(Asset $asset): void
    {
        $workspaceId = $this->requireWorkspaceId($asset->getWorkspace());
        $now = time();

        $data = [
            'uid' => $asset->getUid(),
            'workspace_id' => $workspaceId,
            'file_name' => $asset->getFileName(),
            'storage_path' => $asset->getStoragePath(),
            'mime_type' => $asset->getMimeType(),
            'size' => $asset->getSize(),
            'meta' => $this->encodeMeta($asset->getMeta()),
            'variants' => $this->encodeVariants($asset->getVariants()),
            'updated_at' => $now,
        ];

        $id = $asset->getId();
        if ($id === null) {
            $data['created_at'] = $now;
            $this->db->createCommand()
                ->insert('{{%asset}}', $data)
                ->execute();

            $asset->defineId((int) $this->db->getLastInsertID());

            return;
        }

        $this->db->createCommand()
            ->update('{{%asset}}', $data, ['id' => $id])
            ->execute();
    }

    public function delete(Asset $asset): void
    {
        $id = $asset->getId();
        if ($id === null) {
            return;
        }

        $this->db->createCommand()
            ->delete('{{%asset}}', ['id' => $id])
            ->execute();
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrate(Workspace $workspace, array $row): Asset
    {
        $meta = $this->decodeMeta($row['meta'] ?? null);
        $variants = $this->decodeVariants($row['variants'] ?? null);

        return new Asset(
            workspace: $workspace,
            fileName: (string) ($row['file_name'] ?? ''),
            mimeType: (string) ($row['mime_type'] ?? ''),
            size: isset($row['size']) ? (int) $row['size'] : 0,
            storagePath: (string) ($row['storage_path'] ?? ''),
            meta: $meta,
            variants: $variants,
            id: isset($row['id']) ? (int) $row['id'] : null,
            uid: isset($row['uid']) ? (string) $row['uid'] : null,
            createdAt: $this->createDateTime($row['created_at'] ?? null),
            updatedAt: $this->createDateTime($row['updated_at'] ?? null),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeMeta(mixed $meta): array
    {
        if ($meta === null || $meta === '') {
            return [];
        }

        if (is_array($meta)) {
            return $meta;
        }

        try {
            $decoded = json_decode((string) $meta, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return [];
        }

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param array<string, mixed> $meta
     */
    private function encodeMeta(array $meta): string
    {
        try {
            return json_encode($meta, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return '{}';
        }
    }

    /**
     * @param AssetVariant[] $variants
     */
    private function encodeVariants(array $variants): string
    {
        $payload = [];
        foreach ($variants as $variant) {
            $payload[] = $variant->toArray();
        }

        try {
            return json_encode($payload, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return '[]';
        }
    }

    /**
     * @return AssetVariant[]
     */
    private function decodeVariants(mixed $variants): array
    {
        if ($variants === null || $variants === '') {
            return [];
        }

        $decoded = $variants;
        if (!is_array($decoded)) {
            try {
                $decoded = json_decode((string) $variants, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException) {
                return [];
            }
        }

        if (!is_array($decoded)) {
            return [];
        }

        $result = [];
        foreach ($decoded as $item) {
            if (!is_array($item)) {
                continue;
            }

            try {
                $result[] = AssetVariant::fromArray($item);
            } catch (InvalidArgumentException) {
                continue;
            }
        }

        return $result;
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

    private function requireWorkspaceId(Workspace $workspace): int
    {
        $workspaceId = $workspace->getId();
        if ($workspaceId === null) {
            throw new InvalidArgumentException('Workspace must have an identifier to work with assets.');
        }

        return $workspaceId;
    }
}

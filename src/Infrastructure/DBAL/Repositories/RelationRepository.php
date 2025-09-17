<?php
/*
 * This file is part of Setka CMS.
 *
 * Copyright (c) 2025 Vitaliy Kamelин. All rights reserved.
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

use DateTimeImmutable;
use Exception;
use JsonException;
use Setka\Cms\Contracts\Relations\RelationRepositoryInterface;
use Setka\Cms\Domain\Relations\Relation;
use Setka\Cms\Domain\Relations\RelationCollection;
use yii\db\Connection;
use yii\db\Query;
use function array_map;
use function is_array;
use function is_numeric;
use function json_decode;
use function json_encode;

final class RelationRepository implements RelationRepositoryInterface
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function findByFrom(int $fromElementId, ?string $role = null): RelationCollection
    {
        $query = (new Query())
            ->from('{{%relation}}')
            ->where(['from_element_id' => $fromElementId])
            ->orderBy(['role' => SORT_ASC, 'position' => SORT_ASC, 'id' => SORT_ASC]);

        if ($role !== null && $role !== '') {
            $query->andWhere(['role' => $role]);
        }

        $rows = $query->all($this->db);
        $relations = array_map(fn(array $row): Relation => $this->hydrate($row), $rows);

        return new RelationCollection(...$relations);
    }

    public function findOne(int $fromElementId, int $toElementId, string $role): ?Relation
    {
        $row = (new Query())
            ->from('{{%relation}}')
            ->where([
                'from_element_id' => $fromElementId,
                'to_element_id' => $toElementId,
                'role' => $role,
            ])
            ->one($this->db);

        return $row ? $this->hydrate($row) : null;
    }

    public function save(Relation $relation): void
    {
        $now = time();
        $data = [
            'from_element_id' => $relation->getFromElementId(),
            'to_element_id' => $relation->getToElementId(),
            'role' => $relation->getRole(),
            'position' => $relation->getPosition(),
            'meta' => $this->encodeMeta($relation->getMeta()),
            'updated_at' => $now,
        ];

        $id = $relation->getId();
        if ($id === null) {
            $data['created_at'] = $now;
            $this->db->createCommand()->insert('{{%relation}}', $data)->execute();
            $relation->defineId((int) $this->db->getLastInsertID());

            return;
        }

        $this->db->createCommand()
            ->update('{{%relation}}', $data, ['id' => $id])
            ->execute();
    }

    public function delete(Relation $relation): void
    {
        $id = $relation->getId();
        if ($id !== null) {
            $this->db->createCommand()
                ->delete('{{%relation}}', ['id' => $id])
                ->execute();

            return;
        }

        $this->db->createCommand()
            ->delete(
                '{{%relation}}',
                [
                    'from_element_id' => $relation->getFromElementId(),
                    'to_element_id' => $relation->getToElementId(),
                    'role' => $relation->getRole(),
                ]
            )
            ->execute();
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrate(array $row): Relation
    {
        return new Relation(
            fromElementId: (int) $row['from_element_id'],
            toElementId: (int) $row['to_element_id'],
            role: (string) $row['role'],
            position: (int) $row['position'],
            meta: $this->decodeMeta($row['meta'] ?? null),
            id: isset($row['id']) ? (int) $row['id'] : null,
            createdAt: $this->createDateTime($row['created_at'] ?? null),
            updatedAt: $this->createDateTime($row['updated_at'] ?? null),
        );
    }

    /**
     * @param array<int|string, mixed>|string|null $meta
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

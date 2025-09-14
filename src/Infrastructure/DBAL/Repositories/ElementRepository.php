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

use Setka\Cms\Contracts\Elements\ElementRepositoryInterface;
use Setka\Cms\Domain\Elements\Collection;
use Setka\Cms\Domain\Elements\Element;
use yii\db\Connection;
use yii\db\Query;

final class ElementRepository implements ElementRepositoryInterface
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function findById(int $id): ?Element
    {
        $row = (new Query())
            ->from(['e' => '{{%element}}'])
            ->innerJoin(['c' => '{{%collection}}'], 'c.id = e.collection_id')
            ->where(['e.id' => $id])
            ->one($this->db);

        return $row ? $this->hydrate($row) : null;
    }

    public function findByUid(string $uid): ?Element
    {
        $row = (new Query())
            ->from(['e' => '{{%element}}'])
            ->innerJoin(['c' => '{{%collection}}'], 'c.id = e.collection_id')
            ->where(['e.uid' => $uid])
            ->one($this->db);

        return $row ? $this->hydrate($row) : null;
    }

    public function save(Element $element): void
    {
        // The current domain model does not expose collection accessor or mutators
        // for status/timestamps, so a robust save() cannot be implemented yet.
        // Intentionally left as a no-op until the domain API is expanded.
        // @see Setka\\Cms\\Domain\\Elements\\Element
    }

    public function delete(int $id): void
    {
        $this->db->createCommand()->delete('{{%element}}', ['id' => $id])->execute();
    }

    /**
     * @param array<string,mixed> $row
     */
    private function hydrate(array $row): Element
    {
        // Build collection minimal instance
        $collection = new Collection(
            name: (string) ($row['name'] ?? 'collection'),
            id: isset($row['collection_id']) ? (int) $row['collection_id'] : null,
            uid: isset($row['uid']) ? (string) $row['uid'] : null,
        );

        // Build element with id and uid
        return new Element(
            collection: $collection,
            id: isset($row['id']) ? (int) $row['id'] : null,
            uid: isset($row['uid']) ? (string) $row['uid'] : null,
        );
    }
}

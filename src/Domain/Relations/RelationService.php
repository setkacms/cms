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

namespace Setka\Cms\Domain\Relations;

use Setka\Cms\Contracts\Relations\RelationRepositoryInterface;

final class RelationService
{
    public function __construct(private readonly RelationRepositoryInterface $repository)
    {
    }

    /**
     * Создаёт связь между элементами. Если связь уже существует — возвращает её.
     *
     * @param array<int|string, mixed> $meta
     */
    public function attach(int $fromElementId, int $toElementId, string $role, array $meta = []): Relation
    {
        $existing = $this->repository->findOne($fromElementId, $toElementId, $role);
        if ($existing !== null) {
            return $existing;
        }

        $collection = $this->repository->findByFrom($fromElementId, $role);
        $position = $collection->maxPosition();
        $position = $position >= 0 ? $position + 1 : 0;

        $relation = new Relation($fromElementId, $toElementId, $role, $position, $meta);
        $this->repository->save($relation);

        return $relation;
    }

    public function detach(int $fromElementId, int $toElementId, string $role): void
    {
        $relation = $this->repository->findOne($fromElementId, $toElementId, $role);
        if ($relation === null) {
            return;
        }

        $this->repository->delete($relation);

        $remaining = $this->repository->findByFrom($fromElementId, $role);
        $remaining->sortByPosition();

        $position = 0;
        foreach ($remaining as $item) {
            if ($item->getPosition() === $position) {
                ++$position;
                continue;
            }

            $item->moveToPosition($position);
            ++$position;
            $this->repository->save($item);
        }
    }

    /**
     * @param array<int, int> $orderedTargets
     */
    public function reorder(int $fromElementId, string $role, array $orderedTargets): void
    {
        $collection = $this->repository->findByFrom($fromElementId, $role);
        if ($collection->isEmpty()) {
            return;
        }

        $collection->reorder($orderedTargets);

        foreach ($collection as $relation) {
            $this->repository->save($relation);
        }
    }

    public function list(int $fromElementId, ?string $role = null): RelationCollection
    {
        $collection = $this->repository->findByFrom($fromElementId, $role);
        $collection->sortByPosition();

        return $collection;
    }
}

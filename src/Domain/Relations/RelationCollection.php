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
 * @author    Vitaliy Kamelин <v.kamelin@gmail.com>
 * @license   Proprietary
 *
 * https://github.com/setkacms/cms
 * See LICENSE file for details.
 */

declare(strict_types=1);

namespace Setka\Cms\Domain\Relations;

use Countable;
use IteratorAggregate;
use Traversable;
use function array_filter;
use function array_key_exists;
use function array_values;
use function in_array;
use function strcmp;
use function usort;

/**
 * Коллекция связей элемента.
 */
final class RelationCollection implements IteratorAggregate, Countable
{
    /**
     * @var Relation[]
     */
    private array $relations = [];

    public function __construct(Relation ...$relations)
    {
        foreach ($relations as $relation) {
            $this->relations[] = $relation;
        }

        $this->sortByPosition();
    }

    public function add(Relation $relation): void
    {
        $this->relations[] = $relation;
        $this->sortByPosition();
    }

    public function remove(Relation $relation): void
    {
        foreach ($this->relations as $index => $existing) {
            if ($existing === $relation) {
                unset($this->relations[$index]);
                continue;
            }

            if ($relation->getId() !== null && $existing->getId() === $relation->getId()) {
                unset($this->relations[$index]);
                continue;
            }

            if (
                $existing->getFromElementId() === $relation->getFromElementId()
                && $existing->getToElementId() === $relation->getToElementId()
                && $existing->getRole() === $relation->getRole()
            ) {
                unset($this->relations[$index]);
            }
        }

        $this->relations = array_values($this->relations);
    }

    public function removeByTarget(int $toElementId): ?Relation
    {
        foreach ($this->relations as $index => $relation) {
            if ($relation->getToElementId() === $toElementId) {
                unset($this->relations[$index]);
                $this->relations = array_values($this->relations);

                return $relation;
            }
        }

        return null;
    }

    public function containsTarget(int $toElementId): bool
    {
        foreach ($this->relations as $relation) {
            if ($relation->getToElementId() === $toElementId) {
                return true;
            }
        }

        return false;
    }

    public function getByTarget(int $toElementId): ?Relation
    {
        foreach ($this->relations as $relation) {
            if ($relation->getToElementId() === $toElementId) {
                return $relation;
            }
        }

        return null;
    }

    public function maxPosition(): int
    {
        $max = -1;
        foreach ($this->relations as $relation) {
            $position = $relation->getPosition();
            if ($position > $max) {
                $max = $position;
            }
        }

        return $max;
    }

    /**
     * @param array<int, int> $orderedTargets
     */
    public function reorder(array $orderedTargets): void
    {
        if ($this->relations === []) {
            return;
        }

        $map = [];
        foreach ($this->relations as $relation) {
            $map[$relation->getToElementId()] = $relation;
        }

        $position = 0;
        foreach ($orderedTargets as $target) {
            $targetId = (int) $target;
            if (!array_key_exists($targetId, $map)) {
                continue;
            }

            $map[$targetId]->moveToPosition($position);
            ++$position;
        }

        foreach ($this->relations as $relation) {
            if (in_array($relation->getToElementId(), $orderedTargets, true)) {
                continue;
            }

            $relation->moveToPosition($position);
            ++$position;
        }

        $this->sortByPosition();
    }

    public function sortByPosition(): void
    {
        usort(
            $this->relations,
            static function (Relation $a, Relation $b): int {
                $roleComparison = strcmp($a->getRole(), $b->getRole());
                if ($roleComparison !== 0) {
                    return $roleComparison;
                }

                $positionComparison = $a->getPosition() <=> $b->getPosition();
                if ($positionComparison !== 0) {
                    return $positionComparison;
                }

                $aKey = $a->getId() ?? $a->getToElementId();
                $bKey = $b->getId() ?? $b->getToElementId();

                return $aKey <=> $bKey;
            }
        );
    }

    public function filterByRole(string $role): self
    {
        $filtered = array_filter(
            $this->relations,
            static fn(Relation $relation): bool => $relation->getRole() === $role
        );

        return new self(...array_values($filtered));
    }

    /**
     * @return array<string, Relation[]>
     */
    public function groupByRole(): array
    {
        $grouped = [];
        foreach ($this->relations as $relation) {
            $grouped[$relation->getRole()][] = $relation;
        }

        foreach ($grouped as &$items) {
            usort(
                $items,
                static fn(Relation $a, Relation $b): int => $a->getPosition() <=> $b->getPosition()
            );
        }
        unset($items);

        return $grouped;
    }

    /**
     * @return Relation[]
     */
    public function toArray(): array
    {
        $this->sortByPosition();

        return array_values($this->relations);
    }

    public function isEmpty(): bool
    {
        return $this->relations === [];
    }

    public function count(): int
    {
        return count($this->relations);
    }

    /**
     * @return Traversable<array-key, Relation>
     */
    public function getIterator(): Traversable
    {
        foreach ($this->toArray() as $relation) {
            yield $relation;
        }
    }
}

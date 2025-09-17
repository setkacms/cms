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

namespace Setka\Cms\Domain\Assets;

use Countable;
use IteratorAggregate;
use Traversable;
use function array_filter;
use function array_values;
use function in_array;
use function strcmp;
use function usort;

final class ElementAssetCollection implements IteratorAggregate, Countable
{
    /**
     * @var ElementAsset[]
     */
    private array $items = [];

    public function __construct(ElementAsset ...$attachments)
    {
        foreach ($attachments as $attachment) {
            $this->items[] = $attachment;
        }

        $this->sortByPosition();
    }

    public function add(ElementAsset $attachment): void
    {
        $this->items[] = $attachment;
        $this->sortByPosition();
    }

    public function remove(ElementAsset $attachment): void
    {
        foreach ($this->items as $index => $item) {
            if ($item === $attachment) {
                unset($this->items[$index]);
                continue;
            }

            if ($attachment->getId() !== null && $item->getId() === $attachment->getId()) {
                unset($this->items[$index]);
                continue;
            }

            if (
                $item->getWorkspaceId() === $attachment->getWorkspaceId()
                && $item->getElementId() === $attachment->getElementId()
                && $item->getAssetId() === $attachment->getAssetId()
                && $item->getRole() === $attachment->getRole()
            ) {
                unset($this->items[$index]);
            }
        }

        $this->items = array_values($this->items);
    }

    public function removeByAsset(int $assetId): ?ElementAsset
    {
        foreach ($this->items as $index => $item) {
            if ($item->getAssetId() === $assetId) {
                unset($this->items[$index]);
                $this->items = array_values($this->items);

                return $item;
            }
        }

        return null;
    }

    public function containsAsset(int $assetId): bool
    {
        foreach ($this->items as $item) {
            if ($item->getAssetId() === $assetId) {
                return true;
            }
        }

        return false;
    }

    public function getByAsset(int $assetId): ?ElementAsset
    {
        foreach ($this->items as $item) {
            if ($item->getAssetId() === $assetId) {
                return $item;
            }
        }

        return null;
    }

    public function maxPosition(): int
    {
        $max = -1;
        foreach ($this->items as $item) {
            $position = $item->getPosition();
            if ($position > $max) {
                $max = $position;
            }
        }

        return $max;
    }

    /**
     * @param array<int, int> $orderedAssetIds
     */
    public function reorder(array $orderedAssetIds): void
    {
        if ($this->items === []) {
            return;
        }

        $map = [];
        foreach ($this->items as $item) {
            $map[$item->getAssetId()] = $item;
        }

        $position = 0;
        foreach ($orderedAssetIds as $assetId) {
            $assetKey = (int) $assetId;
            if (!isset($map[$assetKey])) {
                continue;
            }

            $map[$assetKey]->moveToPosition($position);
            ++$position;
        }

        foreach ($this->items as $item) {
            if (in_array($item->getAssetId(), $orderedAssetIds, true)) {
                continue;
            }

            $item->moveToPosition($position);
            ++$position;
        }

        $this->sortByPosition();
    }

    public function sortByPosition(): void
    {
        usort(
            $this->items,
            static function (ElementAsset $a, ElementAsset $b): int {
                $roleComparison = strcmp($a->getRole(), $b->getRole());
                if ($roleComparison !== 0) {
                    return $roleComparison;
                }

                $positionComparison = $a->getPosition() <=> $b->getPosition();
                if ($positionComparison !== 0) {
                    return $positionComparison;
                }

                return $a->getAssetId() <=> $b->getAssetId();
            }
        );
    }

    public function filterByRole(string $role): self
    {
        $filtered = array_filter(
            $this->items,
            static fn(ElementAsset $item): bool => $item->getRole() === $role
        );

        return new self(...array_values($filtered));
    }

    public function isEmpty(): bool
    {
        return $this->items === [];
    }

    /**
     * @return ElementAsset[]
     */
    public function toArray(): array
    {
        return $this->items;
    }

    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->items);
    }

    public function count(): int
    {
        return count($this->items);
    }
}

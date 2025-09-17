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

use InvalidArgumentException;
use Setka\Cms\Contracts\Assets\ElementAssetRepositoryInterface;
use Setka\Cms\Domain\Workspaces\Workspace;

final class ElementAssetService
{
    public function __construct(private readonly ElementAssetRepositoryInterface $repository)
    {
    }

    /**
     * @param string[] $variants
     */
    public function attach(Workspace $workspace, Asset $asset, int $elementId, string $role, array $variants = []): ElementAsset
    {
        $workspaceId = $this->requireWorkspaceId($workspace);
        $assetId = $this->requireAssetId($asset);

        $existing = $this->repository->findOne($workspaceId, $elementId, $assetId, $role);
        if ($existing !== null) {
            $existing->setVariants($variants);
            $this->repository->save($existing);

            return $existing;
        }

        $collection = $this->repository->findByElement($workspaceId, $elementId, $role);
        $position = $collection->maxPosition();
        $position = $position >= 0 ? $position + 1 : 0;

        $attachment = new ElementAsset(
            workspaceId: $workspaceId,
            elementId: $elementId,
            assetId: $assetId,
            role: $role,
            position: $position,
            variants: $variants,
        );
        $attachment->attachAsset($asset);

        $this->repository->save($attachment);

        return $attachment;
    }

    public function detach(Workspace $workspace, Asset $asset, int $elementId, string $role): void
    {
        $workspaceId = $this->requireWorkspaceId($workspace);
        $assetId = $this->requireAssetId($asset);

        $attachment = $this->repository->findOne($workspaceId, $elementId, $assetId, $role);
        if ($attachment === null) {
            return;
        }

        $this->repository->delete($attachment);

        $remaining = $this->repository->findByElement($workspaceId, $elementId, $role);
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
     * @param array<int, int> $orderedAssetIds
     */
    public function reorder(Workspace $workspace, int $elementId, string $role, array $orderedAssetIds): void
    {
        $workspaceId = $this->requireWorkspaceId($workspace);
        $collection = $this->repository->findByElement($workspaceId, $elementId, $role);
        if ($collection->isEmpty()) {
            return;
        }

        $collection->reorder($orderedAssetIds);

        foreach ($collection as $attachment) {
            $this->repository->save($attachment);
        }
    }

    public function list(Workspace $workspace, int $elementId, ?string $role = null): ElementAssetCollection
    {
        $workspaceId = $this->requireWorkspaceId($workspace);
        $collection = $this->repository->findByElement($workspaceId, $elementId, $role);
        $collection->sortByPosition();

        return $collection;
    }

    private function requireWorkspaceId(Workspace $workspace): int
    {
        $workspaceId = $workspace->getId();
        if ($workspaceId === null) {
            throw new InvalidArgumentException('Workspace must have an identifier to manage asset attachments.');
        }

        return $workspaceId;
    }

    private function requireAssetId(Asset $asset): int
    {
        $assetId = $asset->getId();
        if ($assetId === null) {
            throw new InvalidArgumentException('Asset must be persisted before it can be attached to an element.');
        }

        return $assetId;
    }
}

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

use DateTimeImmutable;
use InvalidArgumentException;
use function array_key_exists;
use function array_values;
use function in_array;
use function array_search;
use function trim;

final class ElementAsset
{
    private ?int $id;

    private int $workspaceId;

    private int $elementId;

    private int $assetId;

    private string $role;

    private int $position;

    /** @var string[] */
    private array $variants;

    private DateTimeImmutable $createdAt;

    private DateTimeImmutable $updatedAt;

    private ?Asset $asset = null;

    /**
     * @param string[] $variants
     */
    public function __construct(
        int $workspaceId,
        int $elementId,
        int $assetId,
        string $role,
        int $position = 0,
        array $variants = [],
        ?int $id = null,
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $updatedAt = null,
    ) {
        $this->workspaceId = $this->assertIdentifier($workspaceId, 'workspace');
        $this->elementId = $this->assertIdentifier($elementId, 'element');
        $this->assetId = $this->assertIdentifier($assetId, 'asset');
        $this->role = $this->assertRole($role);
        $this->position = $this->assertPosition($position);
        $this->variants = $this->normaliseVariants($variants);
        $this->id = $id;
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
        $this->updatedAt = $updatedAt ?? new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function defineId(int $id): void
    {
        if ($id <= 0) {
            throw new InvalidArgumentException('Attachment identifier must be positive.');
        }

        if ($this->id !== null && $this->id !== $id) {
            throw new InvalidArgumentException('Attachment identifier is already defined.');
        }

        if ($this->id === $id) {
            return;
        }

        $this->id = $id;
    }

    public function getWorkspaceId(): int
    {
        return $this->workspaceId;
    }

    public function getElementId(): int
    {
        return $this->elementId;
    }

    public function getAssetId(): int
    {
        return $this->assetId;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function changeRole(string $role): void
    {
        $role = $this->assertRole($role);
        if ($this->role === $role) {
            return;
        }

        $this->role = $role;
        $this->touch();
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function moveToPosition(int $position): void
    {
        $position = $this->assertPosition($position);
        if ($this->position === $position) {
            return;
        }

        $this->position = $position;
        $this->touch();
    }

    /**
     * @return string[]
     */
    public function getVariants(): array
    {
        return $this->variants;
    }

    /**
     * @param string[] $variants
     */
    public function setVariants(array $variants): void
    {
        $normalised = $this->normaliseVariants($variants);
        if ($this->variants === $normalised) {
            return;
        }

        $this->variants = $normalised;
        $this->touch();
    }

    public function addVariant(string $variant): void
    {
        $variant = $this->assertVariant($variant);
        if (in_array($variant, $this->variants, true)) {
            return;
        }

        $this->variants[] = $variant;
        $this->touch();
    }

    public function removeVariant(string $variant): void
    {
        $variant = $this->assertVariant($variant);
        $index = array_search($variant, $this->variants, true);
        if ($index === false) {
            return;
        }

        unset($this->variants[$index]);
        $this->variants = array_values($this->variants);
        $this->touch();
    }

    public function hasVariant(string $variant): bool
    {
        $variant = $this->assertVariant($variant);

        return in_array($variant, $this->variants, true);
    }

    public function attachAsset(Asset $asset): void
    {
        if ($asset->getId() !== null && $asset->getId() !== $this->assetId) {
            throw new InvalidArgumentException('Attached asset identifier mismatch.');
        }

        if ($asset->getWorkspaceId() !== null && $asset->getWorkspaceId() !== $this->workspaceId) {
            throw new InvalidArgumentException('Asset workspace mismatch.');
        }

        $this->asset = $asset;
    }

    public function getAsset(): ?Asset
    {
        return $this->asset;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    private function assertIdentifier(int $value, string $label): int
    {
        if ($value <= 0) {
            throw new InvalidArgumentException($label . ' identifier must be positive.');
        }

        return $value;
    }

    private function assertRole(string $role): string
    {
        $role = trim($role);
        if ($role === '') {
            throw new InvalidArgumentException('Role must not be empty.');
        }

        return $role;
    }

    private function assertPosition(int $position): int
    {
        if ($position < 0) {
            throw new InvalidArgumentException('Position must be a non-negative integer.');
        }

        return $position;
    }

    private function assertVariant(string $variant): string
    {
        $variant = trim($variant);
        if ($variant === '') {
            throw new InvalidArgumentException('Variant name must not be empty.');
        }

        return $variant;
    }

    /**
     * @param string[] $variants
     * @return string[]
     */
    private function normaliseVariants(array $variants): array
    {
        $normalised = [];
        foreach ($variants as $variant) {
            if (!is_string($variant)) {
                continue;
            }

            $trimmed = trim($variant);
            if ($trimmed === '') {
                continue;
            }

            if (array_key_exists($trimmed, $normalised)) {
                continue;
            }

            $normalised[$trimmed] = $trimmed;
        }

        return array_values($normalised);
    }

    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }
}

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

use DateTimeImmutable;
use InvalidArgumentException;
use function array_key_exists;

final class Relation
{
    private ?int $id;

    private int $fromElementId;

    private int $toElementId;

    private string $role;

    private int $position;

    /**
     * @var array<string, mixed>
     */
    private array $meta;

    private DateTimeImmutable $createdAt;

    private DateTimeImmutable $updatedAt;

    /**
     * @param array<int|string, mixed> $meta
     */
    public function __construct(
        int $fromElementId,
        int $toElementId,
        string $role,
        int $position = 0,
        array $meta = [],
        ?int $id = null,
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $updatedAt = null,
    ) {
        $this->fromElementId = $this->assertIdentifier($fromElementId, 'from element');
        $this->toElementId = $this->assertIdentifier($toElementId, 'to element');
        $this->role = $this->assertRole($role);
        $this->position = $this->assertPosition($position);
        $this->meta = $this->normaliseMeta($meta);
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
            throw new InvalidArgumentException('Relation identifier must be a positive integer.');
        }

        if ($this->id !== null && $this->id !== $id) {
            throw new InvalidArgumentException('Relation identifier is already defined.');
        }

        if ($this->id === $id) {
            return;
        }

        $this->id = $id;
    }

    public function getFromElementId(): int
    {
        return $this->fromElementId;
    }

    public function getToElementId(): int
    {
        return $this->toElementId;
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
     * @return array<string, mixed>
     */
    public function getMeta(): array
    {
        return $this->meta;
    }

    /**
     * @param array<int|string, mixed> $meta
     */
    public function setMeta(array $meta): void
    {
        $normalised = $this->normaliseMeta($meta);
        if ($this->meta === $normalised) {
            return;
        }

        $this->meta = $normalised;
        $this->touch();
    }

    public function setMetaValue(string $key, mixed $value): void
    {
        $key = $this->assertMetaKey($key);
        if (array_key_exists($key, $this->meta) && $this->meta[$key] === $value) {
            return;
        }

        $this->meta[$key] = $value;
        $this->touch();
    }

    public function removeMetaValue(string $key): void
    {
        $key = $this->assertMetaKey($key);
        if (!array_key_exists($key, $this->meta)) {
            return;
        }

        unset($this->meta[$key]);
        $this->touch();
    }

    public function getMetaValue(string $key, mixed $default = null): mixed
    {
        $key = $this->assertMetaKey($key);

        return $this->meta[$key] ?? $default;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }

    private function assertIdentifier(int $value, string $label): int
    {
        if ($value <= 0) {
            throw new InvalidArgumentException(sprintf('%s must be a positive integer.', ucfirst($label)));
        }

        return $value;
    }

    private function assertRole(string $role): string
    {
        $role = trim($role);
        if ($role === '') {
            throw new InvalidArgumentException('Relation role must not be empty.');
        }

        if (!preg_match('/^[A-Za-z0-9._:-]+$/', $role)) {
            throw new InvalidArgumentException('Relation role contains unsupported characters.');
        }

        return $role;
    }

    private function assertPosition(int $position): int
    {
        if ($position < 0) {
            throw new InvalidArgumentException('Relation position must be zero or positive.');
        }

        return $position;
    }

    private function assertMetaKey(string $key): string
    {
        $key = trim($key);
        if ($key === '') {
            throw new InvalidArgumentException('Relation meta key must not be empty.');
        }

        return $key;
    }

    /**
     * @param array<int|string, mixed> $meta
     * @return array<string, mixed>
     */
    private function normaliseMeta(array $meta): array
    {
        $normalised = [];
        foreach ($meta as $key => $value) {
            $normalisedKey = is_string($key) ? trim($key) : trim((string) $key);
            if ($normalisedKey === '') {
                continue;
            }

            $normalised[$normalisedKey] = $value;
        }

        return $normalised;
    }
}

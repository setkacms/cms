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

namespace Setka\Cms\Domain\Taxonomy;

use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Термин таксономии.
 */
class Term
{
    private ?int $id;

    private string $uid;

    private Taxonomy $taxonomy;

    private string $slug;

    private string $name;

    private string $locale;

    private int $position;

    private ?self $parent = null;

    /** @var array<string, self> */
    private array $children = [];

    private DateTimeImmutable $createdAt;

    private DateTimeImmutable $updatedAt;

    public function __construct(
        Taxonomy $taxonomy,
        string $slug,
        string $name,
        string $locale,
        int $position = 0,
        ?int $id = null,
        ?string $uid = null
    ) {
        $this->taxonomy = $taxonomy;
        $this->slug = $this->assertSlug($slug);
        $this->name = $this->assertName($name);
        $this->locale = $this->assertLocale($locale);
        $this->position = $this->assertPosition($position);
        $this->id = $id;
        $this->uid = $uid ?? self::generateUid();
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public static function generateUid(): string
    {
        return bin2hex(random_bytes(16));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUid(): string
    {
        return $this->uid;
    }

    public function getTaxonomy(): Taxonomy
    {
        return $this->taxonomy;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): void
    {
        $normalised = $this->assertSlug($slug);
        if ($this->slug === $normalised) {
            return;
        }

        $this->slug = $normalised;
        $this->touch();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function rename(string $name): void
    {
        $normalised = $this->assertName($name);
        if ($this->name === $normalised) {
            return;
        }

        $this->name = $normalised;
        $this->touch();
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $position = $this->assertPosition($position);
        if ($this->position === $position) {
            return;
        }

        $this->position = $position;
        $this->touch();
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): void
    {
        if ($parent === $this) {
            throw new InvalidArgumentException('Term cannot be its own parent.');
        }

        if ($parent !== null) {
            if ($parent->getTaxonomy() !== $this->taxonomy) {
                throw new InvalidArgumentException('Parent term must belong to the same taxonomy.');
            }

            $this->assertNoCycle($parent);
        }

        if ($this->parent === $parent) {
            return;
        }

        if ($this->parent !== null) {
            unset($this->parent->children[$this->uid]);
        }

        $this->parent = $parent;
        if ($parent !== null) {
            $parent->children[$this->uid] = $this;
        }

        $this->touch();
    }

    public function addChild(self $child): void
    {
        $child->setParent($this);
    }

    public function removeChild(self $child): void
    {
        if (!isset($this->children[$child->uid])) {
            return;
        }

        $child->setParent(null);
    }

    public function isRoot(): bool
    {
        return $this->parent === null;
    }

    public function isLeaf(): bool
    {
        return $this->children === [];
    }

    /**
     * @return self[]
     */
    public function getChildren(): array
    {
        $children = $this->children;
        usort($children, static fn(self $a, self $b): int => $a->position <=> $b->position);

        return array_values($children);
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    private function assertSlug(string $slug): string
    {
        $slug = strtolower(trim($slug));
        if ($slug === '') {
            throw new InvalidArgumentException('Term slug must not be empty.');
        }

        if (!preg_match('/^[a-z0-9][a-z0-9_\-]*$/', $slug)) {
            throw new InvalidArgumentException('Term slug contains invalid characters.');
        }

        return $slug;
    }

    private function assertName(string $name): string
    {
        $name = trim($name);
        if ($name === '') {
            throw new InvalidArgumentException('Term name must not be empty.');
        }

        return $name;
    }

    private function assertLocale(string $locale): string
    {
        $locale = trim($locale);
        if ($locale === '') {
            throw new InvalidArgumentException('Term locale must not be empty.');
        }

        if (!$this->taxonomy->getWorkspace()->supportsLocale($locale)) {
            throw new InvalidArgumentException(sprintf('Locale "%s" is not supported by workspace.', $locale));
        }

        return $locale;
    }

    private function assertPosition(int $position): int
    {
        if ($position < 0) {
            throw new InvalidArgumentException('Term position must be non-negative.');
        }

        return $position;
    }

    private function assertNoCycle(self $parent): void
    {
        $current = $parent;
        while ($current !== null) {
            if ($current === $this) {
                throw new InvalidArgumentException('Cyclical term hierarchy detected.');
            }

            $current = $current->parent;
        }
    }

    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }
}

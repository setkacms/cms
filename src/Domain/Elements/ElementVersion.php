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

namespace Setka\Cms\Domain\Elements;

use DateTimeImmutable;
use InvalidArgumentException;
use Setka\Cms\Contracts\Elements\ElementStatus;
use function array_key_exists;

final class ElementVersion
{
    private ?int $id;

    private string $uid;

    private Element $element;

    private string $locale;

    private int $number;

    /** @var array<string, mixed> */
    private array $values;

    private ElementStatus $status;

    private DateTimeImmutable $createdAt;

    private DateTimeImmutable $updatedAt;

    private ?DateTimeImmutable $publishedAt;

    private ?DateTimeImmutable $archivedAt;

    /**
     * @param array<string, mixed> $values
     */
    public function __construct(
        Element $element,
        string $locale,
        int $number,
        array $values = [],
        ?int $id = null,
        ?string $uid = null,
        ElementStatus $status = ElementStatus::Draft,
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $updatedAt = null,
        ?DateTimeImmutable $publishedAt = null,
        ?DateTimeImmutable $archivedAt = null,
    ) {
        if ($number <= 0) {
            throw new InvalidArgumentException('Version number must be positive.');
        }

        $this->element = $element;
        $this->locale = $this->assertLocale($locale);
        $this->number = $number;
        $this->values = $this->normaliseValues($values);
        $this->id = $id;
        $this->uid = $uid ?? Element::generateUid();
        $this->status = $status;
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
        $this->updatedAt = $updatedAt ?? new DateTimeImmutable();
        $this->publishedAt = $publishedAt;
        $this->archivedAt = $archivedAt;
    }

    public function markPersisted(
        int $id,
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $updatedAt = null,
        ?DateTimeImmutable $publishedAt = null,
        ?DateTimeImmutable $archivedAt = null
    ): void {
        if ($id <= 0) {
            throw new InvalidArgumentException('Persisted identifier must be positive.');
        }

        $this->id = $id;

        if ($createdAt !== null) {
            $this->createdAt = $createdAt;
        }

        if ($updatedAt !== null) {
            $this->updatedAt = $updatedAt;
        }

        if ($publishedAt !== null || $this->publishedAt !== null) {
            $this->publishedAt = $publishedAt;
        }

        if ($archivedAt !== null || $this->archivedAt !== null) {
            $this->archivedAt = $archivedAt;
        }
    }

    /**
     * @param array<string, mixed> $values
     */
    public function replaceValues(array $values): void
    {
        $this->values = $this->normaliseValues($values);
    }

    public function getElement(): Element
    {
        return $this->element;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUid(): string
    {
        return $this->uid;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    public function getStatus(): ElementStatus
    {
        return $this->status;
    }

    public function markDraft(): void
    {
        $this->status = ElementStatus::Draft;
        $this->archivedAt = null;
        $this->touch();
    }

    public function markPublished(): void
    {
        $this->status = ElementStatus::Published;
        $this->publishedAt = new DateTimeImmutable();
        $this->archivedAt = null;
        $this->touch();
    }

    public function markArchived(): void
    {
        $this->status = ElementStatus::Archived;
        $this->archivedAt = new DateTimeImmutable();
        $this->touch();
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getPublishedAt(): ?DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function getArchivedAt(): ?DateTimeImmutable
    {
        return $this->archivedAt;
    }

    public function setValue(string $handle, mixed $value): void
    {
        $handle = $this->assertHandle($handle);
        $this->values[$handle] = $value;
        $this->touch();
    }

    public function hasValue(string $handle): bool
    {
        return array_key_exists($handle, $this->values);
    }

    public function getValueByHandle(string $handle): mixed
    {
        $handle = $this->assertHandle($handle);

        return $this->values[$handle] ?? null;
    }

    /**
     * @return array<string, mixed>
     */
    public function getValues(): array
    {
        return $this->values;
    }

    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }

    private function assertLocale(string $locale): string
    {
        $locale = trim($locale);
        if ($locale === '') {
            throw new InvalidArgumentException('Locale must not be empty.');
        }

        return $locale;
    }

    private function assertHandle(string $handle): string
    {
        $handle = trim($handle);
        if ($handle === '') {
            throw new InvalidArgumentException('Field handle must not be empty.');
        }

        return $handle;
    }

    /**
     * @param array<string, mixed> $values
     * @return array<string, mixed>
     */
    private function normaliseValues(array $values): array
    {
        $normalised = [];
        foreach ($values as $handle => $value) {
            if (!is_string($handle)) {
                continue;
            }

            $key = trim($handle);
            if ($key === '') {
                continue;
            }

            $normalised[$key] = $value;
        }

        return $normalised;
    }
}

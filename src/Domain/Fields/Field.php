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
 * @author    Vitaliy Кamelин <v.kamelин@gmail.com>
 * @license   Proprietary
 *
 * https://github.com/setkacms/cms
 * See LICENSE file for details.
 */

declare(strict_types=1);

namespace Setka\Cms\Domain\Fields;

use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Описание поля данных.
 */
class Field
{
    private ?int $id;

    private string $uid;

    private string $handle;

    private string $name;

    private FieldType $type;

    private bool $required;

    /** @var array<string, mixed> */
    private array $settings;

    private bool $localized;

    private bool $unique;

    private bool $searchable;

    private bool $multiValued;

    private DateTimeImmutable $createdAt;

    private DateTimeImmutable $updatedAt;

    /**
     * @param array<string, mixed> $settings
     */
    public function __construct(
        string $handle,
        string $name,
        FieldType $type,
        bool $required = false,
        array $settings = [],
        bool $localized = false,
        bool $unique = false,
        bool $searchable = false,
        bool $multiValued = false,
        ?int $id = null,
        ?string $uid = null
    ) {
        $this->handle = $handle;
        $this->name = $name;
        $this->type = $type;
        $this->required = $required;
        $this->settings = $settings;
        $this->localized = $localized;
        $this->unique = $unique;
        $this->searchable = $searchable;
        $this->multiValued = $multiValued;
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

    public function getHandle(): string
    {
        return $this->handle;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function getType(): FieldType
    {
        return $this->type;
    }

    /**
     * @return array<string, mixed>
     */
    public function getSettings(): array
    {
        return $this->settings;
    }

    public function isLocalized(): bool
    {
        return $this->localized;
    }

    public function isUnique(): bool
    {
        return $this->unique;
    }

    public function isSearchable(): bool
    {
        return $this->searchable;
    }

    public function isMultiValued(): bool
    {
        return $this->multiValued;
    }

    public function validate(mixed $value): void
    {
        if ($value === null) {
            if ($this->required) {
                throw new InvalidArgumentException("Field {$this->handle} is required");
            }

            return;
        }

        if ($this->localized) {
            if (!is_array($value)) {
                $this->assertValidValue($value);

                return;
            }

            foreach ($value as $locale => $localizedValue) {
                if (!is_string($locale) || $locale === '') {
                    throw new InvalidArgumentException("Field {$this->handle} contains invalid locale key");
                }

                $this->assertValidValue($localizedValue);
            }

            return;
        }

        $this->assertValidValue($value);
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    private function assertValidValue(mixed $value): void
    {
        if ($this->multiValued) {
            if (!is_iterable($value)) {
                throw new InvalidArgumentException("Field {$this->handle} expects iterable values");
            }

            foreach ($value as $singleValue) {
                if (!$this->type->validate($singleValue, $this->settings)) {
                    throw new InvalidArgumentException("Invalid value for field {$this->handle}");
                }
            }

            return;
        }

        if (!$this->type->validate($value, $this->settings)) {
            throw new InvalidArgumentException("Invalid value for field {$this->handle}");
        }
    }
}


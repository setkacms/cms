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
use Setka\Cms\Domain\Fields\Field;

/**
 * Domain element representation with locale awareness.
 */
class Element
{
    private ?int $id;

    private string $uid;

    private Collection $collection;

    private ?int $schemaId;

    private string $locale;

    /** @var array<string, mixed> */
    private array $values = [];

    private ?ElementVersion $currentVersion = null;

    private string $status = 'draft';

    private DateTimeImmutable $createdAt;

    private DateTimeImmutable $updatedAt;

    public function __construct(
        Collection $collection,
        string $locale,
        ?int $id = null,
        ?string $uid = null,
        ?int $schemaId = null
    )
    {
        $this->collection = $collection;
        $this->locale = $locale;
        $this->id = $id;
        $this->uid = $uid ?? self::generateUid();
        $this->schemaId = $this->filterSchemaId($schemaId);
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

    public function getCollection(): Collection
    {
        return $this->collection;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getSchemaId(): ?int
    {
        return $this->schemaId;
    }

    public function setSchemaId(?int $schemaId): void
    {
        $schemaId = $this->filterSchemaId($schemaId);
        if ($this->schemaId === $schemaId) {
            return;
        }

        $this->schemaId = $schemaId;
        $this->touch();
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function publish(): void
    {
        $this->status = 'published';
        $this->touch();
    }

    public function archive(): void
    {
        $this->status = 'archived';
        $this->touch();
    }

    public function setValue(Field $field, mixed $value): void
    {
        $field->validate($value);
        $this->values[$field->getHandle()] = $value;
        $this->touch();
    }

    public function getValue(Field $field): mixed
    {
        return $this->values[$field->getHandle()] ?? null;
    }

    public function validate(): bool
    {
        foreach ($this->collection->getFields() as $field) {
            if ($field->isRequired() && !array_key_exists($field->getHandle(), $this->values)) {
                return false;
            }

            if (array_key_exists($field->getHandle(), $this->values)) {
                $field->validate($this->values[$field->getHandle()]);
            }
        }

        return true;
    }

    /** @return array<string, mixed> */
    public function getValues(): array
    {
        return $this->values;
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

    private function filterSchemaId(?int $schemaId): ?int
    {
        if ($schemaId === null) {
            return null;
        }

        if ($schemaId <= 0) {
            throw new InvalidArgumentException('Schema identifier must be positive.');
        }

        return $schemaId;
    }
}

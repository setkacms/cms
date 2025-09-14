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
 * @author    Vitaliy Kamelин <v.kamelин@gmail.com>
 * @license   Proprietary
 *
 * https://github.com/setkacms/cms
 * See LICENSE file for details.
 */

declare(strict_types=1);

namespace Setka\Cms\Domain\Elements;

use DateTimeImmutable;
use Setka\Cms\Domain\Fields\Field;

/**
 * Набор элементов одного типа.
 */
class Collection
{
    private ?int $id;

    private string $uid;

    private string $name;

    /** @var array<string, Field> */
    private array $fields = [];

    private DateTimeImmutable $createdAt;

    private DateTimeImmutable $updatedAt;

    public function __construct(string $name, ?int $id = null, ?string $uid = null)
    {
        $this->name = $name;
        $this->id = $id;
        $this->uid = $uid ?? Element::generateUid();
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUid(): string
    {
        return $this->uid;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function addField(Field $field): void
    {
        $this->fields[$field->getHandle()] = $field;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function removeField(string $handle): void
    {
        unset($this->fields[$handle]);
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getField(string $handle): ?Field
    {
        return $this->fields[$handle] ?? null;
    }

    /** @return Field[] */
    public function getFields(): array
    {
        return array_values($this->fields);
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }
}


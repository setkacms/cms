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

    private DateTimeImmutable $createdAt;

    private DateTimeImmutable $updatedAt;

    public function __construct(string $handle, string $name, FieldType $type, bool $required = false, ?int $id = null, ?string $uid = null)
    {
        $this->handle = $handle;
        $this->name = $name;
        $this->type = $type;
        $this->required = $required;
        $this->id = $id;
        $this->uid = $uid ?? self::generateUid();
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public static function generateUid(): string
    {
        return bin2hex(random_bytes(16));
    }

    public function getHandle(): string
    {
        return $this->handle;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function getType(): FieldType
    {
        return $this->type;
    }

    public function validate(mixed $value): void
    {
        if ($value === null) {
            if ($this->required) {
                throw new InvalidArgumentException("Field {$this->handle} is required");
            }

            return;
        }

        if (!$this->type->validate($value)) {
            throw new InvalidArgumentException("Invalid value for field {$this->handle}");
        }
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


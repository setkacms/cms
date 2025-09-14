<?php
/*
 * This file is part of Setka CMS.
 *
 * Copyright (c) 2025 Vitaliy Кamelин. All rights reserved.
 * Proprietary license. Unauthorized copying, modification or distribution
 * of this file, via any medium, is strictly prohibited without prior written permission.
 *
 * @package   Setka CMS
 * @version   1.0.0
 * @author    Vitaliy Кamelин <v.kamelin@gmail.com>
 * @license   Proprietary
 *
 * https://github.com/setkacms/cms
 * See LICENSE file for details.
 */

declare(strict_types=1);

namespace Setka\Cms\Domain\Fields;

/**
 * Типы полей.
 */
enum FieldType: string
{
    case TEXT = 'text';
    case INTEGER = 'integer';
    case BOOLEAN = 'boolean';
    case DATE = 'date';

    public function validate(mixed $value): bool
    {
        return match ($this) {
            self::TEXT => is_string($value),
            self::INTEGER => is_int($value),
            self::BOOLEAN => is_bool($value),
            self::DATE => $value instanceof \DateTimeInterface,
        };
    }
}


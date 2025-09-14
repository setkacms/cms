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
 * Контейнер значений полей.
 */
class ValueAccess
{
    /** @var array<string, mixed> */
    private array $values = [];

    public function set(Field $field, mixed $value): void
    {
        $field->validate($value);
        $this->values[$field->getHandle()] = $value;
    }

    public function get(Field $field): mixed
    {
        return $this->values[$field->getHandle()] ?? null;
    }
}


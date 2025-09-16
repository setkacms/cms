<?php
/*
 * This file is part of Setka CMS.
 *
 * Copyright (c) 2025 Vitaliy Камелин. All rights reserved.
 * Proprietary license. Unauthorized copying, modification or distribution
 * of this file, via any medium, is strictly prohibited without prior written permission.
 *
 * @package   Setka CMS
 * @version   1.0.0
 * @author    Vitaliy Камелин <v.kamelin@gmail.com>
 * @license   Proprietary
 *
 * https://github.com/setkacms/cms
 * See LICENSE file for details.
 */

declare(strict_types=1);

namespace Setka\Cms\Domain\Fields;

use DateTimeInterface;

/**
 * Типы полей.
 */
enum FieldType: string
{
    case TEXT = 'text';
    case RICHTEXT = 'richtext';
    case INTEGER = 'integer';
    case FLOAT = 'float';
    case BOOLEAN = 'boolean';
    case DATE = 'date';
    case DATETIME = 'datetime';
    case SELECT = 'select';
    case ENUM = 'enum';
    case RELATION = 'relation';
    case ASSET = 'asset';
    case MATRIX = 'matrix';
    case JSON = 'json';

    /**
     * @param array<string, mixed> $settings
     */
    public function validate(mixed $value, array $settings = []): bool
    {
        return match ($this) {
            self::TEXT => is_string($value),
            self::RICHTEXT => is_string($value),
            self::INTEGER => is_int($value),
            self::FLOAT => is_float($value) || is_int($value),
            self::BOOLEAN => is_bool($value),
            self::DATE, self::DATETIME => $value instanceof DateTimeInterface,
            self::SELECT, self::ENUM => $this->validateSelect($value, $settings),
            self::RELATION => $this->validateRelationList($value),
            self::ASSET => $this->validateAssetList($value),
            self::MATRIX => $this->validateMatrix($value, $settings),
            self::JSON => is_array($value) || is_object($value),
        };
    }

    private function validateRelationList(mixed $value): bool
    {
        if (!is_array($value)) {
            return false;
        }

        foreach ($value as $item) {
            if (!is_int($item) && !is_string($item)) {
                return false;
            }

            if ($item === '' || (is_int($item) && $item <= 0)) {
                return false;
            }
        }

        return true;
    }

    private function validateAssetList(mixed $value): bool
    {
        if (!is_array($value)) {
            return false;
        }

        foreach ($value as $item) {
            if (!is_array($item)) {
                return false;
            }

            if (!isset($item['assetId']) || (!is_int($item['assetId']) && !is_string($item['assetId']))) {
                return false;
            }

            if (isset($item['variants']) && !is_array($item['variants'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<string, mixed> $settings
     */
    private function validateMatrix(mixed $value, array $settings): bool
    {
        if (!is_array($value)) {
            return false;
        }

        foreach ($value as $block) {
            if (!is_array($block)) {
                return false;
            }

            if (!isset($block['type']) || !is_string($block['type']) || $block['type'] === '') {
                return false;
            }

            if (!isset($block['values']) || !is_array($block['values'])) {
                return false;
            }

            if (isset($settings['blockTypes']) && is_array($settings['blockTypes'])) {
                $allowed = $settings['blockTypes'];
                if (!in_array($block['type'], $allowed, true)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param array<string, mixed> $settings
     */
    private function validateSelect(mixed $value, array $settings): bool
    {
        $options = [];
        if (isset($settings['options']) && is_array($settings['options'])) {
            $options = array_map(static fn(mixed $option): string => (string) $option, $settings['options']);
        }

        if (is_array($value)) {
            if ($value === []) {
                return true;
            }

            foreach ($value as $item) {
                if (!is_string($item) && !is_int($item)) {
                    return false;
                }

                if ($options !== [] && !in_array((string) $item, $options, true)) {
                    return false;
                }
            }

            return true;
        }

        if (!is_string($value) && !is_int($value)) {
            return false;
        }

        if ($options === []) {
            return true;
        }

        return in_array((string) $value, $options, true);
    }
}


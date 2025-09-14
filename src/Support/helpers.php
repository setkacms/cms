<?php

declare(strict_types=1);

/**
 * Copyright (c) 2025. Vitaliy Kamelin <v.kamelin@gmail.com>
 */

if (!function_exists('env')) {
    /**
     * Get the value of an environment variable or return the default value.
     */
    function env(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
        if ($value === false || $value === null) {
            return $default;
        }
        $value = trim((string) $value);
        return match (strtolower($value)) {
            'true', '(true)' => true,
            'false', '(false)' => false,
            'empty', '(empty)' => '',
            'null', '(null)' => null,
            default => $value,
        };
    }
}

if (!function_exists('array_get')) {
    /**
     * Retrieve an item from an array using "dot" notation.
     *
     * @param array<mixed> $array
     * @param string|int|null $key
     * @param mixed $default
     */
    function array_get(array $array, string|int|null $key, mixed $default = null): mixed
    {
        if ($key === null || $key === '') {
            return $array;
        }
        if (array_key_exists($key, $array)) {
            return $array[$key];
        }
        $segments = is_int($key) ? [$key] : explode('.', (string) $key);
        foreach ($segments as $segment) {
            if (is_array($array) && array_key_exists($segment, $array)) {
                $array = $array[$segment];
            } else {
                return $default;
            }
        }
        return $array;
    }
}

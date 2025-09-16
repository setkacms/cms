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

namespace Setka\Cms\Domain\Workspaces;

use DateTimeImmutable;
use InvalidArgumentException;

final class Workspace
{
    private ?int $id;

    private string $uid;

    private string $handle;

    private string $name;

    /** @var string[] */
    private array $locales;

    /** @var array<string, mixed> */
    private array $globalSettings;

    private DateTimeImmutable $createdAt;

    private DateTimeImmutable $updatedAt;

    /**
     * @param string[] $locales
     * @param array<string, mixed> $globalSettings
     */
    public function __construct(
        string $handle,
        string $name,
        array $locales,
        array $globalSettings = [],
        ?int $id = null,
        ?string $uid = null
    ) {
        $this->assertHandle($handle);
        $this->handle = $handle;
        $this->name = $name;
        $this->setLocales($locales);
        $this->globalSettings = $globalSettings;
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

    /**
     * @return string[]
     */
    public function getLocales(): array
    {
        return $this->locales;
    }

    public function supportsLocale(string $locale): bool
    {
        return in_array($locale, $this->locales, true);
    }

    public function addLocale(string $locale): void
    {
        $locale = trim($locale);
        if ($locale === '') {
            throw new InvalidArgumentException('Locale must not be empty.');
        }

        if (!$this->supportsLocale($locale)) {
            $this->locales[] = $locale;
            sort($this->locales);
            $this->touch();
        }
    }

    public function removeLocale(string $locale): void
    {
        $index = array_search($locale, $this->locales, true);
        if ($index === false) {
            return;
        }

        if (count($this->locales) === 1) {
            throw new InvalidArgumentException('Workspace must have at least one locale.');
        }

        unset($this->locales[$index]);
        $this->locales = array_values($this->locales);
        $this->touch();
    }

    /**
     * @return array<string, mixed>
     */
    public function getGlobalSettings(): array
    {
        return $this->globalSettings;
    }

    public function getGlobalSetting(string $key, mixed $default = null): mixed
    {
        return $this->globalSettings[$key] ?? $default;
    }

    public function setGlobalSetting(string $key, mixed $value): void
    {
        $key = trim($key);
        if ($key === '') {
            throw new InvalidArgumentException('Global setting key must not be empty.');
        }

        $this->globalSettings[$key] = $value;
        $this->touch();
    }

    public function removeGlobalSetting(string $key): void
    {
        unset($this->globalSettings[$key]);
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

    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }

    /**
     * @param string[] $locales
     */
    private function setLocales(array $locales): void
    {
        if ($locales === []) {
            throw new InvalidArgumentException('Workspace must declare at least one locale.');
        }

        $normalised = [];
        foreach ($locales as $locale) {
            if (!is_string($locale) || trim($locale) === '') {
                throw new InvalidArgumentException('Invalid locale value supplied.');
            }

            $normalised[trim($locale)] = trim($locale);
        }

        $this->locales = array_values($normalised);
    }

    private function assertHandle(string $handle): void
    {
        $handle = trim($handle);
        if ($handle === '') {
            throw new InvalidArgumentException('Workspace handle must not be empty.');
        }

        if (!preg_match('/^[a-zA-Z0-9][a-zA-Z0-9_\-.]*$/', $handle)) {
            throw new InvalidArgumentException('Workspace handle contains invalid characters.');
        }
    }
}

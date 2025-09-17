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

namespace Setka\Cms\Domain\Assets;

use DateTimeImmutable;
use InvalidArgumentException;
use Setka\Cms\Domain\Workspaces\Workspace;
use function array_key_exists;
use function array_values;
use function bin2hex;
use function ksort;
use function random_bytes;
use function trim;

final class Asset
{
    private ?int $id;

    private string $uid;

    private Workspace $workspace;

    private string $fileName;

    private string $storagePath;

    private string $mimeType;

    private int $size;

    /** @var array<string, mixed> */
    private array $meta;

    /** @var array<string, AssetVariant> */
    private array $variants = [];

    private DateTimeImmutable $createdAt;

    private DateTimeImmutable $updatedAt;

    /**
     * @param array<string, mixed> $meta
     * @param AssetVariant[]        $variants
     */
    public function __construct(
        Workspace $workspace,
        string $fileName,
        string $mimeType,
        int $size = 0,
        ?string $storagePath = null,
        array $meta = [],
        array $variants = [],
        ?int $id = null,
        ?string $uid = null,
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $updatedAt = null,
    ) {
        $this->workspace = $workspace;
        $this->fileName = $this->assertFileName($fileName);
        $this->mimeType = $this->assertMimeType($mimeType);
        $this->size = $this->assertSize($size);
        $this->meta = $this->normaliseMeta($meta);
        $this->id = $id;
        $this->uid = $uid ?? self::generateUid();
        $this->storagePath = $this->normaliseStoragePath($storagePath ?? 'assets/' . $this->uid);
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
        $this->updatedAt = $updatedAt ?? new DateTimeImmutable();

        foreach ($variants as $variant) {
            $this->defineVariant($this->assertVariantInstance($variant));
        }
    }

    public static function generateUid(): string
    {
        return bin2hex(random_bytes(16));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function defineId(int $id): void
    {
        if ($id <= 0) {
            throw new InvalidArgumentException('Asset identifier must be positive.');
        }

        if ($this->id !== null && $this->id !== $id) {
            throw new InvalidArgumentException('Asset identifier is already defined.');
        }

        if ($this->id === $id) {
            return;
        }

        $this->id = $id;
    }

    public function getUid(): string
    {
        return $this->uid;
    }

    public function getWorkspace(): Workspace
    {
        return $this->workspace;
    }

    public function getWorkspaceId(): ?int
    {
        return $this->workspace->getId();
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function rename(string $fileName): void
    {
        $fileName = $this->assertFileName($fileName);
        if ($this->fileName === $fileName) {
            return;
        }

        $this->fileName = $fileName;
        $this->touch();
    }

    public function getStoragePath(): string
    {
        return $this->storagePath;
    }

    public function changeStoragePath(string $storagePath): void
    {
        $storagePath = $this->normaliseStoragePath($storagePath);
        if ($this->storagePath === $storagePath) {
            return;
        }

        $this->storagePath = $storagePath;
        $this->touch();
    }

    public function getFilePath(): string
    {
        return $this->storagePath . '/' . $this->fileName;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function changeMimeType(string $mimeType): void
    {
        $mimeType = $this->assertMimeType($mimeType);
        if ($this->mimeType === $mimeType) {
            return;
        }

        $this->mimeType = $mimeType;
        $this->touch();
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function updateSize(int $size): void
    {
        $size = $this->assertSize($size);
        if ($this->size === $size) {
            return;
        }

        $this->size = $size;
        $this->touch();
    }

    public function updateFileInfo(string $fileName, string $mimeType, int $size, ?string $storagePath = null): void
    {
        $this->rename($fileName);
        $this->changeMimeType($mimeType);
        $this->updateSize($size);

        if ($storagePath !== null) {
            $this->changeStoragePath($storagePath);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function getMeta(): array
    {
        return $this->meta;
    }

    /**
     * @param array<string, mixed> $meta
     */
    public function setMeta(array $meta): void
    {
        $normalised = $this->normaliseMeta($meta);
        if ($this->meta === $normalised) {
            return;
        }

        $this->meta = $normalised;
        $this->touch();
    }

    public function setMetaValue(string $key, mixed $value): void
    {
        $key = trim($key);
        if ($key === '') {
            throw new InvalidArgumentException('Metadata key must not be empty.');
        }

        if (array_key_exists($key, $this->meta) && $this->meta[$key] === $value) {
            return;
        }

        $this->meta[$key] = $value;
        $this->touch();
    }

    public function removeMetaValue(string $key): void
    {
        $key = trim($key);
        if ($key === '') {
            return;
        }

        if (!array_key_exists($key, $this->meta)) {
            return;
        }

        unset($this->meta[$key]);
        $this->touch();
    }

    public function getMetaValue(string $key, mixed $default = null): mixed
    {
        $key = trim($key);
        if ($key === '') {
            return $default;
        }

        return $this->meta[$key] ?? $default;
    }

    public function hasVariant(string $name): bool
    {
        $name = $this->assertVariantName($name);

        return array_key_exists($name, $this->variants);
    }

    public function getVariant(string $name): ?AssetVariant
    {
        $name = $this->assertVariantName($name);

        return $this->variants[$name] ?? null;
    }

    /**
     * @return AssetVariant[]
     */
    public function getVariants(): array
    {
        ksort($this->variants);

        return array_values($this->variants);
    }

    public function defineVariant(AssetVariant $variant): void
    {
        $name = $variant->getName();
        if (isset($this->variants[$name]) && $this->variants[$name]->equals($variant)) {
            return;
        }

        $this->variants[$name] = $variant;
        $this->touch();
    }

    public function removeVariant(string $name): void
    {
        $name = $this->assertVariantName($name);
        if (!isset($this->variants[$name])) {
            return;
        }

        unset($this->variants[$name]);
        $this->touch();
    }

    public function clearVariants(): void
    {
        if ($this->variants === []) {
            return;
        }

        $this->variants = [];
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

    private function assertFileName(string $fileName): string
    {
        $fileName = trim($fileName);
        if ($fileName === '') {
            throw new InvalidArgumentException('Filename must not be empty.');
        }

        return $fileName;
    }

    private function assertMimeType(string $mimeType): string
    {
        $mimeType = trim($mimeType);
        if ($mimeType === '') {
            throw new InvalidArgumentException('MIME type must not be empty.');
        }

        return $mimeType;
    }

    private function assertSize(int $size): int
    {
        if ($size < 0) {
            throw new InvalidArgumentException('File size must be greater or equal to zero.');
        }

        return $size;
    }

    private function normaliseStoragePath(string $storagePath): string
    {
        $storagePath = trim($storagePath);
        $storagePath = ltrim($storagePath, '/');
        $storagePath = rtrim($storagePath, '/');
        if ($storagePath === '') {
            throw new InvalidArgumentException('Storage path must not be empty.');
        }

        return $storagePath;
    }

    /**
     * @param array<string, mixed> $meta
     * @return array<string, mixed>
     */
    private function normaliseMeta(array $meta): array
    {
        $normalised = [];
        foreach ($meta as $key => $value) {
            if (!is_string($key)) {
                continue;
            }

            $trimmedKey = trim($key);
            if ($trimmedKey === '') {
                continue;
            }

            $normalised[$trimmedKey] = $value;
        }

        return $normalised;
    }

    private function assertVariantName(string $name): string
    {
        $name = trim($name);
        if ($name === '') {
            throw new InvalidArgumentException('Variant name must not be empty.');
        }

        return $name;
    }

    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }

    private function assertVariantInstance(AssetVariant $variant): AssetVariant
    {
        if ($variant->getName() === '') {
            throw new InvalidArgumentException('Variant must have a name.');
        }

        return $variant;
    }
}

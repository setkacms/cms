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
 * @author    Vitaliy Kamelин <v.kamelин@gmail.com>
 * @license   Proprietary
 *
 * https://github.com/setkacms/cms
 * See LICENSE file for details.
 */

declare(strict_types=1);

namespace Setka\Cms\Domain\Assets;

use InvalidArgumentException;
use function trim;

final class AssetVariant
{
    private string $name;

    private string $path;

    private string $mimeType;

    private int $size;

    private ?int $width;

    private ?int $height;

    /** @var array<string, mixed> */
    private array $meta;

    /**
     * @param array<string, mixed> $meta
     */
    public function __construct(
        string $name,
        string $path,
        string $mimeType,
        int $size,
        ?int $width = null,
        ?int $height = null,
        array $meta = [],
    ) {
        $this->name = $this->assertName($name);
        $this->path = $this->assertPath($path);
        $this->mimeType = $this->assertMimeType($mimeType);
        $this->size = $this->assertSize($size);
        $this->width = $width !== null ? $this->assertDimension($width) : null;
        $this->height = $height !== null ? $this->assertDimension($height) : null;
        $this->meta = $meta;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: (string) ($data['name'] ?? ''),
            path: (string) ($data['path'] ?? ''),
            mimeType: (string) ($data['mimeType'] ?? ($data['mime_type'] ?? '')),
            size: isset($data['size']) ? (int) $data['size'] : 0,
            width: isset($data['width']) ? (int) $data['width'] : null,
            height: isset($data['height']) ? (int) $data['height'] : null,
            meta: isset($data['meta']) && is_array($data['meta']) ? $data['meta'] : [],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'path' => $this->path,
            'mimeType' => $this->mimeType,
            'size' => $this->size,
            'width' => $this->width,
            'height' => $this->height,
            'meta' => $this->meta,
        ];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    /**
     * @return array<string, mixed>
     */
    public function getMeta(): array
    {
        return $this->meta;
    }

    public function equals(self $other): bool
    {
        return $this->name === $other->name
            && $this->path === $other->path
            && $this->mimeType === $other->mimeType
            && $this->size === $other->size
            && $this->width === $other->width
            && $this->height === $other->height
            && $this->meta == $other->meta; // phpcs:ignore SlevomatCodingStandard.Operators.DisallowEqualOperators.DisallowedEqualOperator
    }

    private function assertName(string $name): string
    {
        $name = trim($name);
        if ($name === '') {
            throw new InvalidArgumentException('Variant name must not be empty.');
        }

        return $name;
    }

    private function assertPath(string $path): string
    {
        $path = trim($path);
        if ($path === '') {
            throw new InvalidArgumentException('Variant path must not be empty.');
        }

        return ltrim($path, '/');
    }

    private function assertMimeType(string $mimeType): string
    {
        $mimeType = trim($mimeType);
        if ($mimeType === '') {
            throw new InvalidArgumentException('Variant MIME type must not be empty.');
        }

        return $mimeType;
    }

    private function assertSize(int $size): int
    {
        if ($size < 0) {
            throw new InvalidArgumentException('Variant size must be greater or equal to zero.');
        }

        return $size;
    }

    private function assertDimension(int $dimension): int
    {
        if ($dimension <= 0) {
            throw new InvalidArgumentException('Variant dimensions must be positive integers.');
        }

        return $dimension;
    }
}

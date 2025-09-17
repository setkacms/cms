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
use Setka\Cms\Contracts\Assets\AssetStorageInterface;
use function sprintf;
use function strlen;
use function trim;

final class AssetVariantService
{
    public function __construct(private readonly AssetStorageInterface $storage)
    {
    }

    /**
     * @param array<string, mixed> $meta
     */
    public function storeVariant(
        Asset $asset,
        string $variantName,
        string $contents,
        string $mimeType,
        array $meta = [],
        ?string $fileName = null,
    ): AssetVariant {
        $variantName = $this->normaliseVariantName($variantName);
        $fileName = $fileName !== null ? $this->normaliseFileName($fileName) : $asset->getFileName();
        $path = $this->buildVariantPath($asset, $variantName, $fileName);

        $this->storage->write($path, $contents);

        $width = isset($meta['width']) ? (int) $meta['width'] : null;
        $height = isset($meta['height']) ? (int) $meta['height'] : null;

        $variant = new AssetVariant(
            name: $variantName,
            path: $path,
            mimeType: $mimeType,
            size: strlen($contents),
            width: $width,
            height: $height,
            meta: $meta,
        );

        $asset->defineVariant($variant);

        return $variant;
    }

    public function deleteVariant(Asset $asset, string $variantName): void
    {
        $variantName = $this->normaliseVariantName($variantName);
        $variant = $asset->getVariant($variantName);
        if ($variant === null) {
            return;
        }

        $this->storage->delete($variant->getPath());
        $asset->removeVariant($variantName);
    }

    public function readVariant(Asset $asset, string $variantName): string
    {
        $variantName = $this->normaliseVariantName($variantName);
        $variant = $asset->getVariant($variantName);
        if ($variant === null) {
            throw new InvalidArgumentException('Requested variant is not registered for the asset.');
        }

        return $this->storage->read($variant->getPath());
    }

    private function buildVariantPath(Asset $asset, string $variantName, string $fileName): string
    {
        return sprintf('%s/variants/%s/%s', $asset->getStoragePath(), $variantName, $fileName);
    }

    private function normaliseVariantName(string $variantName): string
    {
        $variantName = trim($variantName);
        if ($variantName === '') {
            throw new InvalidArgumentException('Variant name must not be empty.');
        }

        return $variantName;
    }

    private function normaliseFileName(string $fileName): string
    {
        $fileName = trim($fileName);
        if ($fileName === '') {
            throw new InvalidArgumentException('Variant file name must not be empty.');
        }

        return $fileName;
    }
}

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
use function strlen;
use function trim;

final class AssetFileService
{
    public function __construct(private readonly AssetStorageInterface $storage)
    {
    }

    public function store(Asset $asset, string $contents, ?string $fileName = null, ?string $mimeType = null): void
    {
        if ($fileName !== null) {
            $fileName = $this->normaliseFileName($fileName);
            $asset->rename($fileName);
        }

        if ($mimeType !== null) {
            $asset->changeMimeType($mimeType);
        }

        $path = $asset->getFilePath();
        $this->storage->write($path, $contents);
        $asset->updateSize(strlen($contents));
    }

    public function read(Asset $asset): string
    {
        return $this->storage->read($asset->getFilePath());
    }

    public function delete(Asset $asset): void
    {
        $this->storage->delete($asset->getFilePath());
        $asset->updateSize(0);
    }

    private function normaliseFileName(string $fileName): string
    {
        $fileName = trim($fileName);
        if ($fileName === '') {
            throw new InvalidArgumentException('File name must not be empty.');
        }

        return $fileName;
    }
}

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

namespace Setka\Cms\Infrastructure\Storage;

use League\Flysystem\FilesystemOperator;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToWriteFile;

class FlysystemStorage
{
    public function __construct(private FilesystemOperator $fs)
    {
    }

    /**
     * @throws UnableToWriteFile
     */
    public function write(string $path, string $contents): void
    {
        $this->fs->write($path, $contents);
    }

    /**
     * @throws UnableToReadFile
     */
    public function read(string $path): string
    {
        return $this->fs->read($path);
    }

    /**
     * @throws UnableToDeleteFile
     */
    public function delete(string $path): void
    {
        $this->fs->delete($path);
    }
}


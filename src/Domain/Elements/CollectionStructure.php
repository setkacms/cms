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

namespace Setka\Cms\Domain\Elements;

/**
 * Defines how entries inside a collection are organised.
 */
enum CollectionStructure: string
{
    case FLAT = 'flat';
    case TREE = 'tree';

    public function isFlat(): bool
    {
        return $this === self::FLAT;
    }

    public function isTree(): bool
    {
        return $this === self::TREE;
    }
}

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

namespace Setka\Cms\Contracts\Elements;

use Setka\Cms\Domain\Elements\Element;
use Setka\Cms\Domain\Elements\ElementVersion;

interface ElementVersionRepositoryInterface
{
    public function save(ElementVersion $version): void;

    public function saveForLocale(Element $element, string $locale): void;

    public function load(Element $element): void;
}

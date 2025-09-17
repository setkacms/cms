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

namespace Setka\Cms\Contracts\Assets;

use Setka\Cms\Domain\Assets\ElementAsset;
use Setka\Cms\Domain\Assets\ElementAssetCollection;

interface ElementAssetRepositoryInterface
{
    public function findByElement(int $workspaceId, int $elementId, ?string $role = null): ElementAssetCollection;

    public function findOne(int $workspaceId, int $elementId, int $assetId, string $role): ?ElementAsset;

    public function save(ElementAsset $attachment): void;

    public function delete(ElementAsset $attachment): void;
}

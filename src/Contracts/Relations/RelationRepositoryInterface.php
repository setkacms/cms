<?php
/*
 * This file is part of Setka CMS.
 *
 * Copyright (c) 2025 Vitaliy Kamelин. All rights reserved.
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

namespace Setka\Cms\Contracts\Relations;

use Setka\Cms\Domain\Relations\Relation;
use Setka\Cms\Domain\Relations\RelationCollection;

interface RelationRepositoryInterface
{
    public function findByFrom(int $fromElementId, ?string $role = null): RelationCollection;

    public function findOne(int $fromElementId, int $toElementId, string $role): ?Relation;

    public function save(Relation $relation): void;

    public function delete(Relation $relation): void;
}

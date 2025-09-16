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

namespace Setka\Cms\Contracts\Fields;

use Setka\Cms\Domain\Fields\Field;
use Setka\Cms\Domain\Workspaces\Workspace;

interface FieldRepositoryInterface
{
    public function findById(Workspace $workspace, int $id, ?string $locale = null): ?Field;

    public function findByHandle(Workspace $workspace, string $handle, ?string $locale = null): ?Field;

    /**
     * Persist field. Inserts when id is null, otherwise updates.
     */
    public function save(Workspace $workspace, Field $field, ?string $locale = null): void;

    public function delete(Workspace $workspace, int $id, ?string $locale = null): void;
}

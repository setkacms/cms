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

use Setka\Cms\Domain\Elements\ElementVersion;
use Setka\Cms\Domain\Fields\Field;
use Setka\Cms\Domain\Workspaces\Workspace;

interface FieldValueRepositoryInterface
{
    public function find(Workspace $workspace, ElementVersion $version, Field $field, ?string $locale = null): mixed;

    public function save(Workspace $workspace, ElementVersion $version, Field $field, mixed $value, ?string $locale = null): void;

    public function delete(Workspace $workspace, ElementVersion $version, Field $field, ?string $locale = null): void;
}

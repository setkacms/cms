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

namespace Setka\Cms\Contracts\Plugins;

use Setka\Cms\Contracts\Fields\FieldTypeInterface;

final class PluginContext
{
    public function registerFieldType(FieldTypeInterface $type): void
    {}
    
    public function addRoute(string $method, string $path, callable $handler): void
    {}
    
    public function addGraphqlSchema(callable $fn): void
    {}
    
    public function addMigrationPath(string $path): void
    {}
}
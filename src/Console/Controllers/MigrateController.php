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

namespace Setka\Cms\Console\Controllers;

use Setka\Cms\Plugins\PluginRegistry;
use yii\console\controllers\MigrateController as BaseMigrateController;

/**
 * Обёртка над стандартной командой `yii migrate` с поддержкой миграций плагинов.
 */
class MigrateController extends BaseMigrateController
{
    /**
     * Добавляет пути миграций плагинов к стандартным путям.
     */
    public function init(): void
    {
        parent::init();

        $paths = (array) $this->migrationPath;

        // Prefer paths registered via PluginContext
        $ctx = PluginRegistry::getContext();
        if ($ctx) {
            foreach ($ctx->getMigrationPaths() as $p) {
                $paths[] = $p;
            }
        }

        foreach (PluginRegistry::all() as $class) {
            if (method_exists($class, 'migrationsPath')) {
                $paths[] = $class::migrationsPath();
            } elseif (defined("$class::MIGRATIONS_PATH")) {
                /** @phpstan-ignore-next-line */
                $paths[] = $class::MIGRATIONS_PATH;
            }
        }

        $this->migrationPath = $paths;
    }
}


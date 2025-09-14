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

namespace Setka\Cms\Bootstrap;

use Setka\Cms\Plugins\ComposerPluginReader;
use Setka\Cms\Plugins\PluginRegistry;

class PluginBootstrap
{
    public function __construct(private string $projectRoot)
    {
    }

    public function bootstrap(): void
    {
        $reader = new ComposerPluginReader($this->projectRoot);
        $classes = $reader->read();

        $registry = new PluginRegistry();
        foreach ($classes as $class) {
            $registry->register($class);
        }

        foreach ($registry->all() as $class) {
            if (!class_exists($class)) {
                continue;
            }

            $plugin = new $class();
            if (method_exists($plugin, 'bootstrap')) {
                $plugin->bootstrap();
            } elseif (method_exists($plugin, '__invoke')) {
                $plugin();
            }
        }
    }
}

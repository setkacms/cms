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

namespace Setka\Cms\Plugins;

use Setka\Cms\Contracts\Plugins\PluginContext;

class PluginRegistry
{
    /**
     * @var string[]
     */
    private static array $plugins = [];

    private static ?PluginContext $context = null;

    public static function register(string $class): void
    {
        // Avoid duplicates
        if (!in_array($class, self::$plugins, true)) {
            self::$plugins[] = $class;
        }
    }

    /**
     * @return string[]
     */
    public static function all(): array
    {
        return self::$plugins;
    }

    public static function setContext(PluginContext $context): void
    {
        self::$context = $context;
    }

    public static function getContext(): ?PluginContext
    {
        return self::$context;
    }

    /**
     * Instantiate and register all discovered plugins with provided context.
     */
    public static function registerPlugins(PluginContext $ctx): void
    {
        foreach (self::all() as $class) {
            if (!class_exists($class)) {
                continue;
            }

            $plugin = new $class();
            if ($plugin instanceof \Setka\Cms\Contracts\Plugins\PluginInterface) {
                $plugin->register($ctx);
                continue;
            }

            // Backward compatibility
            if (method_exists($plugin, 'bootstrap')) {
                $plugin->bootstrap();
            } elseif (method_exists($plugin, '__invoke')) {
                $plugin();
            }
        }
    }
}

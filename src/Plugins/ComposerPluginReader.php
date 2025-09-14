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

class ComposerPluginReader
{
    public function __construct(private string $projectRoot)
    {
    }

    public function read(): array
    {
        $composerFile = $this->projectRoot . '/composer.json';
        $extra = [];
        if (is_file($composerFile)) {
            $composer = json_decode((string) file_get_contents($composerFile), true);
            $extra = $composer['extra']['setka'] ?? [];
        }

        $pluginType = $extra['plugin-type'] ?? 'setka-plugin';
        $pluginMetaKey = $extra['plugin-meta-key'] ?? 'setka.plugin.class';

        $installedPath = $this->projectRoot . '/vendor/composer/installed.json';
        if (!is_file($installedPath)) {
            return [];
        }

        $installed = json_decode((string) file_get_contents($installedPath), true);
        $packages = $installed['packages'] ?? $installed;

        $plugins = [];
        foreach ($packages as $package) {
            if (($package['type'] ?? '') !== $pluginType) {
                continue;
            }
            $extra = $package['extra'][$pluginMetaKey] ?? null;
            if ($extra) {
                $plugins[] = $extra;
            }
        }

        return $plugins;
    }
}
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

namespace Setka\Cms;

use yii\base\InvalidConfigException;

class Application
{
    private string $type;

    private string $projectRoot;

    private array $overrides;

    public function __construct(string $type, string $projectRoot, array $overrides = [])
    {
        $this->type = $type;
        $this->projectRoot = $projectRoot;
        $this->overrides = $overrides;
    }
    
    /**
     * @throws InvalidConfigException
     */
    public function run(): void
    {
        (new Bootstrap\Kernel($this->projectRoot))->bootstrap();

        $config = $this->loadConfig();

        if ($this->type === 'web') {
            (new \yii\web\Application($config))->run();
        } elseif ($this->type === 'console') {
            (new \yii\console\Application($config))->run();
        } else {
            throw new \RuntimeException("Unknown application type: {$this->type}");
        }
    }
    
    private function loadConfig(): array
    {
        $config = require $this->configPath($this->type);

        if ($this->overrides) {
            $config = array_replace_recursive($config, $this->overrides);
        }

        return $config;
    }

    private function configPath(string $type): string
    {
        return $this->projectRoot . '/config/' . $type . '.php';
    }
}
<?php
/**
 * Copyright (c) 2025. Vitaliy Kamelin <v.kamelin@gmail.com>
 */

namespace Setka\Cms;

use yii\base\InvalidConfigException;

class Application
{
    private string $type;
    
    public function __construct(string $type)
    {
        $this->type = $type;
    }
    
    /**
     * @throws InvalidConfigException
     */
    public function run(): void
    {
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
        $basePath = dirname(__DIR__, 2);
        $configDir = $basePath . '/config';
        
        if ($this->type === 'web') {
            return require $configDir . '/web.php';
        }
        if ($this->type === 'console') {
            return require $configDir . '/console.php';
        }
        
        return [];
    }
}
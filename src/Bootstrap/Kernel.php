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

use Setka\Cms\Bootstrap\Providers\CacheProvider;
use Setka\Cms\Bootstrap\Providers\DatabaseProvider;
use Setka\Cms\Bootstrap\Providers\EventProvider;
use Setka\Cms\Bootstrap\Providers\GraphQLProvider;
use Setka\Cms\Bootstrap\Providers\StorageProvider;
use Setka\Cms\Bootstrap\Providers\HttpClientProvider;
use Setka\Cms\Bootstrap\Providers\LogProvider;
use Setka\Cms\Bootstrap\Providers\ProviderInterface;
use yii\base\Application as YiiApplication;
use yii\base\BootstrapInterface;
use yii\di\Container;

final class Kernel implements BootstrapInterface
{
    /**
     * @var class-string<ProviderInterface>[]
     */
    private array $providers = [
        CacheProvider::class,
        DatabaseProvider::class,
        EventProvider::class,
        GraphQLProvider::class,
        HttpClientProvider::class,
        LogProvider::class,
        StorageProvider::class,
    ];

    public function __construct(private string $projectRoot)
    {
    }

    public function bootstrap($app): void
    {
        /** @var YiiApplication $app */
        $c = \Yii::$container;
        $params = $app->params;

        foreach ($this->providers as $class) {
            $provider = new $class();
            $provider->register($c, $params);
        }

        (new PluginBootstrap($this->projectRoot ?: \Yii::getAlias('@root')))->bootstrap();
    }

    public function init(Container $c, array $params): void
    {
        foreach ($this->providers as $class) {
            $provider = new $class();
            $provider->register($c, $params);
        }

        (new PluginBootstrap($this->projectRoot))->bootstrap();
    }
}

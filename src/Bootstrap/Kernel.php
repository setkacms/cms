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
use Setka\Cms\Bootstrap\Providers\ProviderInterface;
use yii\di\Container;

class Kernel
{
    /**
     * @var class-string<ProviderInterface>[]
     */
    private array $providers = [
        CacheProvider::class,
        DatabaseProvider::class,
        EventProvider::class,
    ];

    public function __construct(private string $projectRoot)
    {
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

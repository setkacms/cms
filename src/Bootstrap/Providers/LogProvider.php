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

namespace Setka\Cms\Bootstrap\Providers;

use Psr\Log\LoggerInterface;
use Setka\Cms\Infrastructure\Log\MonologLogger;
use yii\di\Container;

class LogProvider implements ProviderInterface
{
    public function register(Container $c, array $params = []): void
    {
        $config = $params['log'] ?? [];
        $name = $config['name'] ?? 'app';
        $stream = $config['stream'] ?? 'php://stdout';

        $c->set(LoggerInterface::class, static fn() => new MonologLogger($name, $stream));
        $c->set('logger', LoggerInterface::class);
    }
}


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

use yii\di\Container;
use yii\queue\Queue as BaseQueue;
use yii\queue\redis\Queue as RedisQueue;

class QueueProvider implements ProviderInterface
{
    public function register(Container $c, array $params = []): void
    {
        $config = $params['queue'] ?? [];
        $class = $config['class'] ?? RedisQueue::class;
        // Keep ability to override class via params
        $c->set(BaseQueue::class, static fn() => \Yii::createObject(array_merge(['class' => $class], $config)));
        $c->set(RedisQueue::class, BaseQueue::class);
        // Friendly ID alias
        $c->set('queue', BaseQueue::class);
    }
}


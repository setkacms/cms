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

use yii\db\Connection;
use yii\di\Container;

class DatabaseProvider implements ProviderInterface
{
    public function register(Container $c, array $params = []): void
    {
        $config = $params['db'] ?? [];
        $class = $config['class'] ?? Connection::class;
        unset($config['class']);

        $c->set(Connection::class, static fn() => new $class($config));
        $c->set('db', Connection::class);
    }
}

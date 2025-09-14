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
use yii\rbac\DbManager;
use yii\rbac\ManagerInterface;
use yii2mod\rbac\Module as RbacModule;

class RbacProvider implements ProviderInterface
{
    public function register(Container $c, array $params = []): void
    {
        $config = $params['authManager'] ?? [];
        $class = $config['class'] ?? DbManager::class;
        unset($config['class']);

        $c->set(ManagerInterface::class, static fn() => new $class($config));
        $c->set('authManager', ManagerInterface::class);

        $moduleConfig = $params['modules']['rbac'] ?? [];
        $c->set(RbacModule::class, static fn() => new RbacModule('rbac', \Yii::$app, $moduleConfig));
        $c->set('rbacModule', RbacModule::class);
    }
}

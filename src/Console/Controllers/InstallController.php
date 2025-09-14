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

namespace Setka\Cms\Console\Controllers;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * Команды для первоначальной установки системы.
 */
class InstallController extends Controller
{
    /**
     * Запускает все миграции ядра и создаёт учётную запись администратора.
     */
    public function actionIndex(): int
    {
        $this->stdout("Running core migrations...\n");
        Yii::$app->runAction('migrate/up', ['interactive' => 0]);

        $this->stdout("Creating administrator account...\n");
        $username = $this->prompt('Username:');
        $email = $this->prompt('Email:');
        $password = $this->prompt('Password:');

        Yii::$app->runAction('user/create', [
            'username' => $username,
            'password' => $password,
            'email' => $email,
            'role' => 'admin',
        ]);

        $this->stdout("Installation completed.\n");
        return ExitCode::OK;
    }
}


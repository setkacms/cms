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
 * Команды для управления пользователями.
 */
class UserController extends Controller
{
    /**
     * Создаёт нового пользователя.
     */
    public function actionCreate(string $username, string $password, string $email = '', string $role = 'user'): int
    {
        $db = Yii::$app->db;
        $time = time();

        $db->createCommand()->insert('{{%user}}', [
            'username' => $username,
            'password_hash' => Yii::$app->security->generatePasswordHash($password),
            'email' => $email,
            'role' => $role,
            'auth_key' => Yii::$app->security->generateRandomString(),
            'created_at' => $time,
            'updated_at' => $time,
        ])->execute();

        $this->stdout("User {$username} created.\n");
        return ExitCode::OK;
    }

    /**
     * Смена пароля пользователя.
     */
    public function actionPassword(string $username, string $password): int
    {
        $hash = Yii::$app->security->generatePasswordHash($password);
        Yii::$app->db->createCommand()->update('{{%user}}', [
            'password_hash' => $hash,
            'updated_at' => time(),
        ], ['username' => $username])->execute();

        $this->stdout("Password updated.\n");
        return ExitCode::OK;
    }
}


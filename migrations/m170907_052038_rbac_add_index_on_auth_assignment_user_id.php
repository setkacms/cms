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

use Yii;
use yii\base\InvalidConfigException;
use yii\db\Migration;
use yii\rbac\DbManager;

/**
 * Adds index on `user_id` column in auth assignment table.
 */
class m170907_052038_rbac_add_index_on_auth_assignment_user_id extends Migration
{
    public function up(): void
    {
        $auth = $this->getAuthManager();
        $this->db = $auth->db;
        $this->createIndex('idx-auth_assignment-user_id', $auth->assignmentTable, 'user_id');
    }

    public function down(): void
    {
        $auth = $this->getAuthManager();
        $this->db = $auth->db;
        $this->dropIndex('idx-auth_assignment-user_id', $auth->assignmentTable);
    }

    protected function getAuthManager(): DbManager
    {
        $authManager = Yii::$app->getAuthManager();
        if (!$authManager instanceof DbManager) {
            throw new InvalidConfigException('You should configure "authManager" component to use database before executing this migration.');
        }

        return $authManager;
    }
}

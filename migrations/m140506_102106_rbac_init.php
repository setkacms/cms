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
 * Initializes RBAC tables.
 */
class m140506_102106_rbac_init extends Migration
{
    public function up(): void
    {
        $auth = $this->getAuthManager();
        $this->db = $auth->db;

        $tableOptions = $this->db->driverName === 'mysql'
            ? 'ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci'
            : null;

        $this->createTable($auth->ruleTable, [
            'name' => $this->string(64)->notNull(),
            'data' => $this->binary(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'PRIMARY KEY(name)',
        ], $tableOptions);

        $this->createTable($auth->itemTable, [
            'name' => $this->string(64)->notNull(),
            'type' => $this->smallInteger()->notNull(),
            'description' => $this->text(),
            'rule_name' => $this->string(64),
            'data' => $this->binary(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'PRIMARY KEY(name)',
        ], $tableOptions);

        $this->createTable($auth->itemChildTable, [
            'parent' => $this->string(64)->notNull(),
            'child' => $this->string(64)->notNull(),
            'PRIMARY KEY(parent, child)',
        ], $tableOptions);

        $this->createTable($auth->assignmentTable, [
            'item_name' => $this->string(64)->notNull(),
            'user_id' => $this->string(64)->notNull(),
            'created_at' => $this->integer(),
            'PRIMARY KEY(item_name, user_id)',
        ], $tableOptions);

        $this->addForeignKey(
            'fk_auth_assignment_item_name',
            $auth->assignmentTable,
            'item_name',
            $auth->itemTable,
            'name',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_auth_item_child_parent',
            $auth->itemChildTable,
            'parent',
            $auth->itemTable,
            'name',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_auth_item_child_child',
            $auth->itemChildTable,
            'child',
            $auth->itemTable,
            'name',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_auth_item_rule_name',
            $auth->itemTable,
            'rule_name',
            $auth->ruleTable,
            'name',
            'SET NULL',
            'CASCADE'
        );
    }

    public function down(): void
    {
        $auth = $this->getAuthManager();
        $this->db = $auth->db;

        $this->dropForeignKey('fk_auth_assignment_item_name', $auth->assignmentTable);
        $this->dropForeignKey('fk_auth_item_child_parent', $auth->itemChildTable);
        $this->dropForeignKey('fk_auth_item_child_child', $auth->itemChildTable);
        $this->dropForeignKey('fk_auth_item_rule_name', $auth->itemTable);

        $this->dropTable($auth->assignmentTable);
        $this->dropTable($auth->itemChildTable);
        $this->dropTable($auth->itemTable);
        $this->dropTable($auth->ruleTable);
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

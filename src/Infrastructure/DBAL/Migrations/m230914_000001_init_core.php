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

declare(strict_types=1);

namespace Setka\Cms\Infrastructure\DBAL\Migrations;

use yii\db\Migration;

/**
 * Core schema: collections, elements, fields, users, RBAC, field values.
 */
final class m230914_000001_init_core extends Migration
{
    public function safeUp(): bool
    {
        // collections
        $this->createTable('{{%collection}}', [
            'id' => $this->primaryKey(),
            'uid' => $this->char(32)->notNull()->unique(),
            'name' => $this->string(190)->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        // fields
        $this->createTable('{{%field}}', [
            'id' => $this->primaryKey(),
            'uid' => $this->char(32)->notNull()->unique(),
            'handle' => $this->string(190)->notNull()->unique(),
            'name' => $this->string(190)->notNull(),
            'type' => $this->string(32)->notNull(),
            'required' => $this->boolean()->notNull()->defaultValue(false),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        // collection<->field relation (many-to-many)
        $this->createTable('{{%collection_field}}', [
            'collection_id' => $this->integer()->notNull(),
            'field_id' => $this->integer()->notNull(),
        ]);
        $this->addPrimaryKey('pk_collection_field', '{{%collection_field}}', ['collection_id', 'field_id']);
        $this->createIndex('idx_collection_field_collection', '{{%collection_field}}', 'collection_id');
        $this->createIndex('idx_collection_field_field', '{{%collection_field}}', 'field_id');

        $this->addForeignKey(
            'fk_collection_field_collection',
            '{{%collection_field}}',
            'collection_id',
            '{{%collection}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_collection_field_field',
            '{{%collection_field}}',
            'field_id',
            '{{%field}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // elements
        $this->createTable('{{%element}}', [
            'id' => $this->primaryKey(),
            'uid' => $this->char(32)->notNull()->unique(),
            'collection_id' => $this->integer()->notNull(),
            'status' => $this->string(32)->notNull()->defaultValue('draft'),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);
        $this->createIndex('idx_element_collection', '{{%element}}', 'collection_id');
        $this->addForeignKey(
            'fk_element_collection',
            '{{%element}}',
            'collection_id',
            '{{%collection}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // element versions
        $this->createTable('{{%element_version}}', [
            'id' => $this->primaryKey(),
            'uid' => $this->char(32)->notNull()->unique(),
            'element_id' => $this->integer()->notNull(),
            'version' => $this->integer()->notNull(),
            'status' => $this->string(32)->notNull()->defaultValue('draft'),
            'created_at' => $this->integer()->notNull(),
        ]);
        $this->createIndex('idx_element_version_element', '{{%element_version}}', 'element_id');
        $this->createIndex('ux_element_version_unique', '{{%element_version}}', ['element_id', 'version'], true);
        $this->addForeignKey(
            'fk_element_version_element',
            '{{%element_version}}',
            'element_id',
            '{{%element}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // field values by type
        $this->createTable('{{%field_value_text}}', [
            'id' => $this->primaryKey(),
            'element_id' => $this->integer()->notNull(),
            'field_id' => $this->integer()->notNull(),
            'version_id' => $this->integer(),
            'value' => $this->text(),
        ]);
        $this->createTable('{{%field_value_integer}}', [
            'id' => $this->primaryKey(),
            'element_id' => $this->integer()->notNull(),
            'field_id' => $this->integer()->notNull(),
            'version_id' => $this->integer(),
            'value' => $this->integer(),
        ]);
        $this->createTable('{{%field_value_boolean}}', [
            'id' => $this->primaryKey(),
            'element_id' => $this->integer()->notNull(),
            'field_id' => $this->integer()->notNull(),
            'version_id' => $this->integer(),
            'value' => $this->boolean(),
        ]);
        $this->createTable('{{%field_value_date}}', [
            'id' => $this->primaryKey(),
            'element_id' => $this->integer()->notNull(),
            'field_id' => $this->integer()->notNull(),
            'version_id' => $this->integer(),
            'value' => $this->dateTime(),
        ]);

        foreach (['text', 'integer', 'boolean', 'date'] as $type) {
            $table = "{{%field_value_{$type}}}";
            $this->createIndex("idx_{$type}_element", $table, 'element_id');
            $this->createIndex("idx_{$type}_field", $table, 'field_id');
            $this->createIndex("idx_{$type}_version", $table, 'version_id');
            $this->addForeignKey("fk_{$type}_element", $table, 'element_id', '{{%element}}', 'id', 'CASCADE', 'CASCADE');
            $this->addForeignKey("fk_{$type}_field", $table, 'field_id', '{{%field}}', 'id', 'CASCADE', 'CASCADE');
            $this->addForeignKey("fk_{$type}_version", $table, 'version_id', '{{%element_version}}', 'id', 'CASCADE', 'CASCADE');
        }

        // users
        $this->createTable('{{%user}}', [
            'id' => $this->primaryKey(),
            'uid' => $this->char(32)->notNull()->unique(),
            'username' => $this->string(190)->notNull()->unique(),
            'email' => $this->string(190)->notNull()->unique(),
            'password_hash' => $this->string(255)->notNull(),
            'auth_key' => $this->string(64)->null(),
            'status' => $this->smallInteger()->notNull()->defaultValue(10),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        // roles and permissions (simple RBAC)
        $this->createTable('{{%role}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(190)->notNull()->unique(),
        ]);
        $this->createTable('{{%permission}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(190)->notNull()->unique(),
        ]);
        $this->createTable('{{%role_permission}}', [
            'role_id' => $this->integer()->notNull(),
            'permission_id' => $this->integer()->notNull(),
        ]);
        $this->addPrimaryKey('pk_role_permission', '{{%role_permission}}', ['role_id', 'permission_id']);
        $this->addForeignKey('fk_rp_role', '{{%role_permission}}', 'role_id', '{{%role}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_rp_permission', '{{%role_permission}}', 'permission_id', '{{%permission}}', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('{{%user_role}}', [
            'user_id' => $this->integer()->notNull(),
            'role_id' => $this->integer()->notNull(),
        ]);
        $this->addPrimaryKey('pk_user_role', '{{%user_role}}', ['user_id', 'role_id']);
        $this->addForeignKey('fk_ur_user', '{{%user_role}}', 'user_id', '{{%user}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_ur_role', '{{%user_role}}', 'role_id', '{{%role}}', 'id', 'CASCADE', 'CASCADE');

        return true;
    }

    public function safeDown(): bool
    {
        // drop in reverse order of creation and FKs
        $this->dropForeignKey('fk_ur_role', '{{%user_role}}');
        $this->dropForeignKey('fk_ur_user', '{{%user_role}}');
        $this->dropTable('{{%user_role}}');

        $this->dropForeignKey('fk_rp_permission', '{{%role_permission}}');
        $this->dropForeignKey('fk_rp_role', '{{%role_permission}}');
        $this->dropTable('{{%role_permission}}');
        $this->dropTable('{{%permission}}');
        $this->dropTable('{{%role}}');

        foreach (['text', 'integer', 'boolean', 'date'] as $type) {
            $table = "{{%field_value_{$type}}}";
            $this->dropForeignKey("fk_{$type}_version", $table);
            $this->dropForeignKey("fk_{$type}_field", $table);
            $this->dropForeignKey("fk_{$type}_element", $table);
            $this->dropTable($table);
        }

        $this->dropForeignKey('fk_element_version_element', '{{%element_version}}');
        $this->dropTable('{{%element_version}}');

        $this->dropForeignKey('fk_element_collection', '{{%element}}');
        $this->dropTable('{{%element}}');

        $this->dropForeignKey('fk_collection_field_field', '{{%collection_field}}');
        $this->dropForeignKey('fk_collection_field_collection', '{{%collection_field}}');
        $this->dropTable('{{%collection_field}}');

        $this->dropTable('{{%field}}');
        $this->dropTable('{{%collection}}');

        $this->dropTable('{{%user}}');

        return true;
    }
}

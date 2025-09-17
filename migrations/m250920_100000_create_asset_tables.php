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
 * @author    Vitaliy Kamelин <v.kamelин@gmail.com>
 * @license   Proprietary
 *
 * https://github.com/setkacms/cms
 * See LICENSE file for details.
 */

declare(strict_types=1);

use yii\db\Migration;

/**
 * Создаёт таблицы медиа-активов и их привязок к элементам.
 */
final class m250920_100000_create_asset_tables extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%asset}}', [
            'id' => $this->primaryKey(),
            'uid' => $this->char(32)->notNull(),
            'workspace_id' => $this->integer()->notNull(),
            'file_name' => $this->string(255)->notNull(),
            'storage_path' => $this->string(255)->notNull(),
            'mime_type' => $this->string(127)->notNull(),
            'size' => $this->bigInteger()->notNull()->defaultValue(0),
            'meta' => $this->json()->notNull(),
            'variants' => $this->json()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);
        $this->createIndex('ux_asset_uid', '{{%asset}}', 'uid', true);
        $this->createIndex('idx_asset_workspace', '{{%asset}}', 'workspace_id');
        $this->addForeignKey('fk_asset_workspace', '{{%asset}}', 'workspace_id', '{{%workspace}}', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('{{%element_asset}}', [
            'id' => $this->primaryKey(),
            'workspace_id' => $this->integer()->notNull(),
            'element_id' => $this->integer()->notNull(),
            'asset_id' => $this->integer()->notNull(),
            'role' => $this->string(64)->notNull(),
            'position' => $this->integer()->notNull()->defaultValue(0),
            'variants' => $this->json()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);
        $this->createIndex('idx_element_asset_workspace', '{{%element_asset}}', 'workspace_id');
        $this->createIndex('idx_element_asset_element_role', '{{%element_asset}}', ['element_id', 'role']);
        $this->createIndex('idx_element_asset_asset', '{{%element_asset}}', 'asset_id');
        $this->createIndex('ux_element_asset_unique', '{{%element_asset}}', ['workspace_id', 'element_id', 'role', 'asset_id'], true);

        $this->addForeignKey('fk_element_asset_workspace', '{{%element_asset}}', 'workspace_id', '{{%workspace}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_element_asset_element', '{{%element_asset}}', 'element_id', '{{%element}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_element_asset_asset', '{{%element_asset}}', 'asset_id', '{{%asset}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('fk_element_asset_asset', '{{%element_asset}}');
        $this->dropForeignKey('fk_element_asset_element', '{{%element_asset}}');
        $this->dropForeignKey('fk_element_asset_workspace', '{{%element_asset}}');
        $this->dropIndex('ux_element_asset_unique', '{{%element_asset}}');
        $this->dropIndex('idx_element_asset_asset', '{{%element_asset}}');
        $this->dropIndex('idx_element_asset_element_role', '{{%element_asset}}');
        $this->dropIndex('idx_element_asset_workspace', '{{%element_asset}}');
        $this->dropTable('{{%element_asset}}');

        $this->dropForeignKey('fk_asset_workspace', '{{%asset}}');
        $this->dropIndex('idx_asset_workspace', '{{%asset}}');
        $this->dropIndex('ux_asset_uid', '{{%asset}}');
        $this->dropTable('{{%asset}}');
    }
}

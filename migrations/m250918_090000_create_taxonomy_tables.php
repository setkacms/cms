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

use yii\db\Migration;

/**
 * Создаёт таблицы таксономий, терминов и связей с элементами.
 */
final class m250918_090000_create_taxonomy_tables extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%taxonomy}}', [
            'id' => $this->primaryKey(),
            'uid' => $this->char(32)->notNull(),
            'workspace_id' => $this->integer()->notNull(),
            'collection_id' => $this->integer()->notNull(),
            'handle' => $this->string(190)->notNull(),
            'name' => $this->string(190)->notNull(),
            'structure' => $this->string(16)->notNull()->defaultValue('flat'),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);
        $this->createIndex('ux_taxonomy_uid', '{{%taxonomy}}', 'uid', true);
        $this->createIndex('ux_taxonomy_collection_handle', '{{%taxonomy}}', ['collection_id', 'handle'], true);
        $this->addForeignKey('fk_taxonomy_workspace', '{{%taxonomy}}', 'workspace_id', '{{%workspace}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_taxonomy_collection', '{{%taxonomy}}', 'collection_id', '{{%collection}}', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('{{%term}}', [
            'id' => $this->primaryKey(),
            'uid' => $this->char(32)->notNull(),
            'taxonomy_id' => $this->integer()->notNull(),
            'parent_id' => $this->integer()->null(),
            'slug' => $this->string(190)->notNull(),
            'name' => $this->string(190)->notNull(),
            'locale' => $this->string(12)->notNull(),
            'position' => $this->integer()->notNull()->defaultValue(0),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);
        $this->createIndex('ux_term_uid', '{{%term}}', 'uid', true);
        $this->createIndex('ux_term_taxonomy_locale_slug', '{{%term}}', ['taxonomy_id', 'locale', 'slug'], true);
        $this->createIndex('idx_term_parent', '{{%term}}', 'parent_id');
        $this->createIndex('idx_term_taxonomy_position', '{{%term}}', ['taxonomy_id', 'position']);
        $this->addForeignKey('fk_term_taxonomy', '{{%term}}', 'taxonomy_id', '{{%taxonomy}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_term_parent', '{{%term}}', 'parent_id', '{{%term}}', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('{{%element_term}}', [
            'id' => $this->primaryKey(),
            'element_id' => $this->integer()->notNull(),
            'term_id' => $this->integer()->notNull(),
            'taxonomy_id' => $this->integer()->notNull(),
            'workspace_id' => $this->integer()->notNull(),
            'locale' => $this->string(12)->notNull(),
            'position' => $this->integer()->notNull()->defaultValue(0),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);
        $this->createIndex('ux_element_term_unique', '{{%element_term}}', ['element_id', 'term_id', 'locale'], true);
        $this->createIndex('idx_element_term_term', '{{%element_term}}', 'term_id');
        $this->createIndex('idx_element_term_taxonomy', '{{%element_term}}', 'taxonomy_id');
        $this->createIndex('idx_element_term_workspace', '{{%element_term}}', 'workspace_id');
        $this->createIndex('idx_element_term_locale', '{{%element_term}}', 'locale');
        $this->addForeignKey('fk_element_term_element', '{{%element_term}}', 'element_id', '{{%element}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_element_term_term', '{{%element_term}}', 'term_id', '{{%term}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_element_term_taxonomy', '{{%element_term}}', 'taxonomy_id', '{{%taxonomy}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_element_term_workspace', '{{%element_term}}', 'workspace_id', '{{%workspace}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('fk_element_term_workspace', '{{%element_term}}');
        $this->dropForeignKey('fk_element_term_taxonomy', '{{%element_term}}');
        $this->dropForeignKey('fk_element_term_term', '{{%element_term}}');
        $this->dropForeignKey('fk_element_term_element', '{{%element_term}}');
        $this->dropTable('{{%element_term}}');

        $this->dropForeignKey('fk_term_parent', '{{%term}}');
        $this->dropForeignKey('fk_term_taxonomy', '{{%term}}');
        $this->dropTable('{{%term}}');

        $this->dropForeignKey('fk_taxonomy_collection', '{{%taxonomy}}');
        $this->dropForeignKey('fk_taxonomy_workspace', '{{%taxonomy}}');
        $this->dropTable('{{%taxonomy}}');
    }
}

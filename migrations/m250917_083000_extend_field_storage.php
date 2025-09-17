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

use Setka\Cms\Contracts\Elements\ElementStatus;
use yii\db\Migration;

/**
 * Extends field storage with configuration flags, element versions and value table.
 */
final class m250917_083000_extend_field_storage extends Migration
{
    public function safeUp(): void
    {
        $this->addColumn('{{%field}}', 'settings', $this->text()->notNull()->defaultValue('{}'));
        $this->addColumn('{{%field}}', 'localized', $this->boolean()->notNull()->defaultValue(false));
        $this->addColumn('{{%field}}', 'is_unique', $this->boolean()->notNull()->defaultValue(false));
        $this->addColumn('{{%field}}', 'searchable', $this->boolean()->notNull()->defaultValue(false));
        $this->addColumn('{{%field}}', 'multi_valued', $this->boolean()->notNull()->defaultValue(false));

        $this->createTable('{{%element_version}}', [
            'id' => $this->primaryKey(),
            'uid' => $this->char(32)->notNull()->unique(),
            'element_id' => $this->integer()->notNull(),
            'locale' => $this->string(12)->notNull(),
            'number' => $this->integer()->notNull(),
            'status' => $this->smallInteger()->notNull()->defaultValue(ElementStatus::Draft->value),
            'published_at' => $this->integer()->null(),
            'archived_at' => $this->integer()->null(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->createIndex('ux-element_version-uid', '{{%element_version}}', 'uid', true);
        $this->createIndex('ux-element_version-unique', '{{%element_version}}', ['element_id', 'locale', 'number'], true);
        $this->createIndex('idx-element_version-element', '{{%element_version}}', 'element_id');
        $this->createIndex('idx-element_version-locale', '{{%element_version}}', 'locale');
        $this->addForeignKey('fk-element_version-element', '{{%element_version}}', 'element_id', '{{%element}}', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('{{%field_value}}', [
            'id' => $this->primaryKey(),
            'version_id' => $this->integer()->notNull(),
            'element_id' => $this->integer()->notNull(),
            'field_id' => $this->integer()->notNull(),
            'field_handle' => $this->string(190)->notNull(),
            'workspace_id' => $this->integer()->notNull(),
            'locale' => $this->string(12)->notNull(),
            'value_json' => $this->text()->notNull(),
            'search_value' => $this->string(512)->null(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->createIndex('idx-field_value-version', '{{%field_value}}', 'version_id');
        $this->createIndex('idx-field_value-element', '{{%field_value}}', 'element_id');
        $this->createIndex('idx-field_value-field', '{{%field_value}}', 'field_id');
        $this->createIndex('idx-field_value-workspace', '{{%field_value}}', 'workspace_id');
        $this->createIndex('ux-field_value-version-field', '{{%field_value}}', ['version_id', 'field_id'], true);

        $this->addForeignKey('fk-field_value-version', '{{%field_value}}', 'version_id', '{{%element_version}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-field_value-field', '{{%field_value}}', 'field_id', '{{%field}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-field_value-element', '{{%field_value}}', 'element_id', '{{%element}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-field_value-workspace', '{{%field_value}}', 'workspace_id', '{{%workspace}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('fk-field_value-workspace', '{{%field_value}}');
        $this->dropForeignKey('fk-field_value-element', '{{%field_value}}');
        $this->dropForeignKey('fk-field_value-field', '{{%field_value}}');
        $this->dropForeignKey('fk-field_value-version', '{{%field_value}}');

        $this->dropTable('{{%field_value}}');

        $this->dropForeignKey('fk-element_version-element', '{{%element_version}}');
        $this->dropTable('{{%element_version}}');

        $this->dropColumn('{{%field}}', 'multi_valued');
        $this->dropColumn('{{%field}}', 'searchable');
        $this->dropColumn('{{%field}}', 'is_unique');
        $this->dropColumn('{{%field}}', 'localized');
        $this->dropColumn('{{%field}}', 'settings');
    }
}

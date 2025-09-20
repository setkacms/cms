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

use stdClass;
use yii\db\Migration;

final class m230920_000002_add_workspaces extends Migration
{
    public function safeUp(): bool
    {
        $this->createTable('{{%workspace}}', [
            'id' => $this->primaryKey(),
            'uid' => $this->char(32)->notNull()->unique(),
            'handle' => $this->string(190)->notNull()->unique(),
            'name' => $this->string(190)->notNull(),
            'locales' => $this->json()->notNull(),
            'global_settings' => $this->json()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $now = time();
        $defaultUid = bin2hex(random_bytes(16));
        $this->insert('{{%workspace}}', [
            'uid' => $defaultUid,
            'handle' => 'default',
            'name' => 'Default Workspace',
            'locales' => json_encode(['en-US'], JSON_THROW_ON_ERROR),
            'global_settings' => json_encode(new stdClass(), JSON_THROW_ON_ERROR),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $defaultWorkspaceId = (int) $this->db->getLastInsertID();

        $this->addWorkspaceColumn('{{%collection}}', 'fk_collection_workspace', 'idx_collection_workspace', $defaultWorkspaceId);
        $this->addWorkspaceColumn('{{%field}}', 'fk_field_workspace', 'idx_field_workspace', $defaultWorkspaceId);

        $this->addColumn('{{%element}}', 'workspace_id', $this->integer()->notNull()->defaultValue($defaultWorkspaceId));
        $this->addColumn('{{%element}}', 'locale', $this->string(12)->notNull()->defaultValue('en-US'));
        $this->createIndex('idx_element_workspace', '{{%element}}', 'workspace_id');
        $this->createIndex('idx_element_locale', '{{%element}}', 'locale');
        $this->addForeignKey('fk_element_workspace', '{{%element}}', 'workspace_id', '{{%workspace}}', 'id', 'CASCADE', 'CASCADE');

        $this->addColumn('{{%element_version}}', 'workspace_id', $this->integer()->notNull()->defaultValue($defaultWorkspaceId));
        $this->addColumn('{{%element_version}}', 'locale', $this->string(12)->notNull()->defaultValue('en-US'));
        $this->createIndex('idx_element_version_workspace', '{{%element_version}}', 'workspace_id');
        $this->createIndex('idx_element_version_locale', '{{%element_version}}', 'locale');
        $this->addForeignKey('fk_element_version_workspace', '{{%element_version}}', 'workspace_id', '{{%workspace}}', 'id', 'CASCADE', 'CASCADE');

        foreach (['text', 'integer', 'boolean', 'date'] as $type) {
            $table = "{{%field_value_{$type}}}";
            $this->addColumn($table, 'workspace_id', $this->integer()->notNull()->defaultValue($defaultWorkspaceId));
            $this->addColumn($table, 'locale', $this->string(12)->notNull()->defaultValue('en-US'));
            $this->createIndex("idx_{$type}_workspace", $table, 'workspace_id');
            $this->createIndex("idx_{$type}_locale", $table, 'locale');
            $this->addForeignKey("fk_{$type}_workspace", $table, 'workspace_id', '{{%workspace}}', 'id', 'CASCADE', 'CASCADE');
        }

        $this->addWorkspaceColumn('{{%user}}', 'fk_user_workspace', 'idx_user_workspace', $defaultWorkspaceId);
        $this->addWorkspaceColumn('{{%role}}', 'fk_role_workspace', 'idx_role_workspace', $defaultWorkspaceId);
        $this->addWorkspaceColumn('{{%permission}}', 'fk_permission_workspace', 'idx_permission_workspace', $defaultWorkspaceId);

        return true;
    }

    public function safeDown(): bool
    {
        $this->dropWorkspaceColumn('{{%permission}}', 'fk_permission_workspace', 'idx_permission_workspace');
        $this->dropWorkspaceColumn('{{%role}}', 'fk_role_workspace', 'idx_role_workspace');
        $this->dropWorkspaceColumn('{{%user}}', 'fk_user_workspace', 'idx_user_workspace');

        foreach (['text', 'integer', 'boolean', 'date'] as $type) {
            $table = "{{%field_value_{$type}}}";
            $this->dropForeignKey("fk_{$type}_workspace", $table);
            $this->dropIndex("idx_{$type}_locale", $table);
            $this->dropIndex("idx_{$type}_workspace", $table);
            $this->dropColumn($table, 'locale');
            $this->dropColumn($table, 'workspace_id');
        }

        $this->dropForeignKey('fk_element_version_workspace', '{{%element_version}}');
        $this->dropIndex('idx_element_version_locale', '{{%element_version}}');
        $this->dropIndex('idx_element_version_workspace', '{{%element_version}}');
        $this->dropColumn('{{%element_version}}', 'locale');
        $this->dropColumn('{{%element_version}}', 'workspace_id');

        $this->dropForeignKey('fk_element_workspace', '{{%element}}');
        $this->dropIndex('idx_element_locale', '{{%element}}');
        $this->dropIndex('idx_element_workspace', '{{%element}}');
        $this->dropColumn('{{%element}}', 'locale');
        $this->dropColumn('{{%element}}', 'workspace_id');

        $this->dropWorkspaceColumn('{{%field}}', 'fk_field_workspace', 'idx_field_workspace');
        $this->dropWorkspaceColumn('{{%collection}}', 'fk_collection_workspace', 'idx_collection_workspace');

        $this->dropTable('{{%workspace}}');

        return true;
    }

    private function addWorkspaceColumn(string $table, string $fkName, string $indexName, int $defaultWorkspaceId): void
    {
        $this->addColumn($table, 'workspace_id', $this->integer()->notNull()->defaultValue($defaultWorkspaceId));
        $this->createIndex($indexName, $table, 'workspace_id');
        $this->addForeignKey($fkName, $table, 'workspace_id', '{{%workspace}}', 'id', 'CASCADE', 'CASCADE');
    }

    private function dropWorkspaceColumn(string $table, string $fkName, string $indexName): void
    {
        $this->dropForeignKey($fkName, $table);
        $this->dropIndex($indexName, $table);
        $this->dropColumn($table, 'workspace_id');
    }
}

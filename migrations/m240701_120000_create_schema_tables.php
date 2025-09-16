<?php
/*
 * This file is part of Setka CMS.
 *
 * Copyright (c) 2025 Vitaliy Kamelин. All rights reserved.
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
use yii\db\Schema;

/**
 * Создает таблицы схем и связь с полями и элементами.
 */
final class m240701_120000_create_schema_tables extends Migration
{
    public function safeUp(): bool
    {
        $schema = $this->db->getSchema();

        $this->ensureSchemaTable($schema);
        $this->ensureSchemaFieldTable($schema);
        $this->ensureCollectionForeignKey($schema);
        $this->ensureElementSchemaColumn($schema);

        return true;
    }

    public function safeDown(): bool
    {
        $schema = $this->db->getSchema();

        $this->rollbackElementSchemaColumn($schema);
        $this->rollbackSchemaFieldTable($schema);
        $this->rollbackSchemaTable($schema);

        return true;
    }

    private function ensureSchemaTable(Schema $schema): void
    {
        $schemaTable = $schema->getTableSchema('{{%schema}}', true);
        if ($schemaTable === null) {
            $this->createTable('{{%schema}}', [
                'id' => $this->primaryKey(),
                'uid' => $this->char(32)->notNull()->unique(),
                'workspace_id' => $this->integer()->notNull(),
                'collection_id' => $this->integer()->notNull(),
                'handle' => $this->string(190)->notNull(),
                'name' => $this->string(190)->notNull(),
                'description' => $this->text()->null(),
                'config' => $this->json()->notNull(),
                'created_at' => $this->integer()->notNull(),
                'updated_at' => $this->integer()->notNull(),
            ]);
            $this->createIndex('ux_schema_collection_handle', '{{%schema}}', ['collection_id', 'handle'], true);
            $this->addForeignKey('fk_schema_workspace', '{{%schema}}', 'workspace_id', '{{%workspace}}', 'id', 'CASCADE', 'CASCADE');
            $this->addForeignKey('fk_schema_collection', '{{%schema}}', 'collection_id', '{{%collection}}', 'id', 'CASCADE', 'CASCADE');

            return;
        }

        if ($schemaTable->getColumn('collection_id') === null) {
            $this->addColumn('{{%schema}}', 'collection_id', $this->integer()->null());
        }

        if ($schemaTable->getColumn('description') === null) {
            $this->addColumn('{{%schema}}', 'description', $this->text()->null());
        }

        if ($schemaTable->getColumn('config') === null) {
            $this->addColumn('{{%schema}}', 'config', $this->json()->notNull());
        }

        $indexes = $schema->getTableIndexes('{{%schema}}');
        $hasCollectionHandleIndex = false;
        foreach ($indexes as $index) {
            if ($index->name === 'ux_schema_collection_handle') {
                $hasCollectionHandleIndex = true;
                break;
            }
        }
        if (!$hasCollectionHandleIndex && $schemaTable->getColumn('collection_id') !== null) {
            $this->createIndex('ux_schema_collection_handle', '{{%schema}}', ['collection_id', 'handle'], true);
        }

        $foreignKeys = $schema->getTableForeignKeys('{{%schema}}');
        $hasCollectionFk = false;
        foreach ($foreignKeys as $fk) {
            if ($fk->name === 'fk_schema_collection') {
                $hasCollectionFk = true;
                break;
            }
        }
        if (!$hasCollectionFk && $schemaTable->getColumn('collection_id') !== null) {
            $this->addForeignKey('fk_schema_collection', '{{%schema}}', 'collection_id', '{{%collection}}', 'id', 'CASCADE', 'CASCADE');
        }
    }

    private function ensureSchemaFieldTable(Schema $schema): void
    {
        $schemaFieldTable = $schema->getTableSchema('{{%schema_field}}', true);
        if ($schemaFieldTable === null) {
            $this->createTable('{{%schema_field}}', [
                'id' => $this->primaryKey(),
                'schema_id' => $this->integer()->notNull(),
                'field_id' => $this->integer()->notNull(),
                'group_handle' => $this->string(190)->notNull(),
                'position' => $this->integer()->notNull()->defaultValue(0),
                'config' => $this->json()->notNull(),
                'condition' => $this->json()->null(),
                'created_at' => $this->integer()->notNull(),
                'updated_at' => $this->integer()->notNull(),
            ]);
            $this->createIndex('ux_schema_field_unique', '{{%schema_field}}', ['schema_id', 'field_id'], true);
            $this->createIndex('idx_schema_field_schema', '{{%schema_field}}', 'schema_id');
            $this->addForeignKey('fk_schema_field_schema', '{{%schema_field}}', 'schema_id', '{{%schema}}', 'id', 'CASCADE', 'CASCADE');
            $this->addForeignKey('fk_schema_field_field', '{{%schema_field}}', 'field_id', '{{%field}}', 'id', 'CASCADE', 'CASCADE');

            return;
        }

        if ($schemaFieldTable->getColumn('group_handle') === null) {
            $this->addColumn('{{%schema_field}}', 'group_handle', $this->string(190)->notNull()->defaultValue('default'));
        }

        if ($schemaFieldTable->getColumn('position') === null) {
            $this->addColumn('{{%schema_field}}', 'position', $this->integer()->notNull()->defaultValue(0));
        }

        if ($schemaFieldTable->getColumn('config') === null) {
            $this->addColumn('{{%schema_field}}', 'config', $this->json()->notNull());
        }

        if ($schemaFieldTable->getColumn('condition') === null) {
            $this->addColumn('{{%schema_field}}', 'condition', $this->json()->null());
        }

        $indexes = $schema->getTableIndexes('{{%schema_field}}');
        $hasUnique = false;
        $hasSchemaIndex = false;
        foreach ($indexes as $index) {
            if ($index->name === 'ux_schema_field_unique') {
                $hasUnique = true;
            }
            if ($index->name === 'idx_schema_field_schema') {
                $hasSchemaIndex = true;
            }
        }
        if (!$hasUnique) {
            $this->createIndex('ux_schema_field_unique', '{{%schema_field}}', ['schema_id', 'field_id'], true);
        }
        if (!$hasSchemaIndex) {
            $this->createIndex('idx_schema_field_schema', '{{%schema_field}}', 'schema_id');
        }

        $foreignKeys = $schema->getTableForeignKeys('{{%schema_field}}');
        $hasSchemaFk = false;
        $hasFieldFk = false;
        foreach ($foreignKeys as $fk) {
            if ($fk->name === 'fk_schema_field_schema') {
                $hasSchemaFk = true;
            }
            if ($fk->name === 'fk_schema_field_field') {
                $hasFieldFk = true;
            }
        }
        if (!$hasSchemaFk) {
            $this->addForeignKey('fk_schema_field_schema', '{{%schema_field}}', 'schema_id', '{{%schema}}', 'id', 'CASCADE', 'CASCADE');
        }
        if (!$hasFieldFk) {
            $this->addForeignKey('fk_schema_field_field', '{{%schema_field}}', 'field_id', '{{%field}}', 'id', 'CASCADE', 'CASCADE');
        }
    }

    private function ensureCollectionForeignKey(Schema $schema): void
    {
        $collectionTable = $schema->getTableSchema('{{%collection}}', true);
        if ($collectionTable === null || $collectionTable->getColumn('default_schema_id') === null) {
            return;
        }

        $foreignKeys = $schema->getTableForeignKeys('{{%collection}}');
        foreach ($foreignKeys as $fk) {
            if ($fk->name === 'fk_collection_default_schema') {
                return;
            }
        }

        $this->addForeignKey(
            'fk_collection_default_schema',
            '{{%collection}}',
            'default_schema_id',
            '{{%schema}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    private function ensureElementSchemaColumn(Schema $schema): void
    {
        $elementTable = $schema->getTableSchema('{{%element}}', true);
        if ($elementTable === null) {
            return;
        }

        if ($elementTable->getColumn('schema_id') === null) {
            $this->addColumn('{{%element}}', 'schema_id', $this->integer()->null());
        }

        $indexes = $schema->getTableIndexes('{{%element}}');
        $hasSchemaIndex = false;
        foreach ($indexes as $index) {
            if ($index->name === 'idx_element_schema') {
                $hasSchemaIndex = true;
                break;
            }
        }
        if (!$hasSchemaIndex) {
            $this->createIndex('idx_element_schema', '{{%element}}', 'schema_id');
        }

        $foreignKeys = $schema->getTableForeignKeys('{{%element}}');
        $hasSchemaFk = false;
        foreach ($foreignKeys as $fk) {
            if ($fk->name === 'fk_element_schema') {
                $hasSchemaFk = true;
                break;
            }
        }
        if (!$hasSchemaFk) {
            $this->addForeignKey('fk_element_schema', '{{%element}}', 'schema_id', '{{%schema}}', 'id', 'SET NULL', 'CASCADE');
        }
    }

    private function rollbackElementSchemaColumn(Schema $schema): void
    {
        $elementTable = $schema->getTableSchema('{{%element}}', true);
        if ($elementTable === null) {
            return;
        }

        $foreignKeys = $schema->getTableForeignKeys('{{%element}}');
        foreach ($foreignKeys as $fk) {
            if ($fk->name === 'fk_element_schema') {
                $this->dropForeignKey('fk_element_schema', '{{%element}}');
                break;
            }
        }

        $indexes = $schema->getTableIndexes('{{%element}}');
        foreach ($indexes as $index) {
            if ($index->name === 'idx_element_schema') {
                $this->dropIndex('idx_element_schema', '{{%element}}');
                break;
            }
        }

        if ($elementTable->getColumn('schema_id') !== null) {
            $this->dropColumn('{{%element}}', 'schema_id');
        }
    }

    private function rollbackSchemaFieldTable(Schema $schema): void
    {
        $schemaFieldTable = $schema->getTableSchema('{{%schema_field}}', true);
        if ($schemaFieldTable === null) {
            return;
        }

        $foreignKeys = $schema->getTableForeignKeys('{{%schema_field}}');
        foreach ($foreignKeys as $fk) {
            if ($fk->name === 'fk_schema_field_schema') {
                $this->dropForeignKey('fk_schema_field_schema', '{{%schema_field}}');
            }
            if ($fk->name === 'fk_schema_field_field') {
                $this->dropForeignKey('fk_schema_field_field', '{{%schema_field}}');
            }
        }

        $indexes = $schema->getTableIndexes('{{%schema_field}}');
        foreach ($indexes as $index) {
            if ($index->name === 'ux_schema_field_unique') {
                $this->dropIndex('ux_schema_field_unique', '{{%schema_field}}');
            }
            if ($index->name === 'idx_schema_field_schema') {
                $this->dropIndex('idx_schema_field_schema', '{{%schema_field}}');
            }
        }

        $this->dropTable('{{%schema_field}}');
    }

    private function rollbackSchemaTable(Schema $schema): void
    {
        $schemaTable = $schema->getTableSchema('{{%schema}}', true);
        if ($schemaTable === null) {
            return;
        }

        $collectionTable = $schema->getTableSchema('{{%collection}}', true);
        if ($collectionTable !== null) {
            $collectionForeignKeys = $schema->getTableForeignKeys('{{%collection}}');
            foreach ($collectionForeignKeys as $fk) {
                if ($fk->name === 'fk_collection_default_schema') {
                    $this->dropForeignKey('fk_collection_default_schema', '{{%collection}}');
                    break;
                }
            }
        }

        $foreignKeys = $schema->getTableForeignKeys('{{%schema}}');
        foreach ($foreignKeys as $fk) {
            if ($fk->name === 'fk_schema_collection') {
                $this->dropForeignKey('fk_schema_collection', '{{%schema}}');
            }
        }

        $indexes = $schema->getTableIndexes('{{%schema}}');
        foreach ($indexes as $index) {
            if ($index->name === 'ux_schema_collection_handle') {
                $this->dropIndex('ux_schema_collection_handle', '{{%schema}}');
                break;
            }
        }

        if ($schemaTable->getColumn('collection_id') !== null) {
            $this->dropColumn('{{%schema}}', 'collection_id');
        }

        if ($schemaTable->getColumn('description') !== null) {
            $this->dropColumn('{{%schema}}', 'description');
        }
    }
}

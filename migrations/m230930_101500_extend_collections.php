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

use Setka\Cms\Domain\Elements\CollectionStructure;
use yii\db\Migration;
use yii\db\Query;
use yii\db\Schema;
use function json_encode;
use function preg_replace;
use function strtolower;

final class m230930_101500_extend_collections extends Migration
{
    public function safeUp(): bool
    {
        $schema = $this->db->getSchema();

        if ($schema->getTableSchema('{{%schema}}', true) === null) {
            $this->createTable('{{%schema}}', [
                'id' => $this->primaryKey(),
                'uid' => $this->char(32)->notNull()->unique(),
                'workspace_id' => $this->integer()->notNull(),
                'handle' => $this->string(190)->notNull(),
                'name' => $this->string(190)->notNull(),
                'config' => $this->json()->notNull(),
                'created_at' => $this->integer()->notNull(),
                'updated_at' => $this->integer()->notNull(),
            ]);
            $this->createIndex('ux_schema_workspace_handle', '{{%schema}}', ['workspace_id', 'handle'], true);
            $this->addForeignKey('fk_schema_workspace', '{{%schema}}', 'workspace_id', '{{%workspace}}', 'id', 'CASCADE', 'CASCADE');
        }

        $collectionTable = $schema->getTableSchema('{{%collection}}', true);
        if ($collectionTable === null) {
            return true;
        }

        if ($collectionTable->getColumn('handle') === null) {
            $this->addColumn('{{%collection}}', 'handle', $this->string(190)->notNull()->defaultValue('collection'));
        }

        if ($collectionTable->getColumn('structure') === null) {
            $this->addColumn('{{%collection}}', 'structure', $this->string(16)->notNull()->defaultValue(CollectionStructure::FLAT->value));
        }

        if ($collectionTable->getColumn('default_schema_id') === null) {
            $this->addColumn('{{%collection}}', 'default_schema_id', $this->integer()->null());
        }

        if ($collectionTable->getColumn('url_rules') === null) {
            $this->addColumn('{{%collection}}', 'url_rules', $this->json()->null());
        }

        if ($collectionTable->getColumn('publication_rules') === null) {
            $this->addColumn('{{%collection}}', 'publication_rules', $this->json()->null());
        }

        $rows = (new Query())
            ->select(['id', 'name', 'handle'])
            ->from('{{%collection}}')
            ->all($this->db);

        foreach ($rows as $row) {
            $id = (int) ($row['id'] ?? 0);
            if ($id <= 0) {
                continue;
            }

            $currentHandle = isset($row['handle']) ? (string) $row['handle'] : '';
            if ($currentHandle !== '' && $currentHandle !== 'collection') {
                continue;
            }

            $name = isset($row['name']) ? (string) $row['name'] : '';
            $handle = $this->slugify($name, $id);
            $this->update('{{%collection}}', ['handle' => $handle], ['id' => $id]);
        }

        $this->alterColumn('{{%collection}}', 'handle', $this->string(190)->notNull());
        $this->update('{{%collection}}', ['structure' => CollectionStructure::FLAT->value], ['structure' => null]);

        $emptyJson = json_encode([], JSON_THROW_ON_ERROR);
        $this->update('{{%collection}}', ['url_rules' => $emptyJson], ['url_rules' => null]);
        $this->update('{{%collection}}', ['publication_rules' => $emptyJson], ['publication_rules' => null]);

        $indexes = $schema->getTableIndexes('{{%collection}}');
        $hasHandleIndex = false;
        foreach ($indexes as $index) {
            if ($index->name === 'ux_collection_workspace_handle') {
                $hasHandleIndex = true;
                break;
            }
        }
        if (!$hasHandleIndex) {
            $this->createIndex('ux_collection_workspace_handle', '{{%collection}}', ['workspace_id', 'handle'], true);
        }

        $foreignKeys = $schema->getTableForeignKeys('{{%collection}}');
        $hasDefaultSchemaFk = false;
        foreach ($foreignKeys as $fk) {
            if ($fk->name === 'fk_collection_default_schema') {
                $hasDefaultSchemaFk = true;
                break;
            }
        }
        if (!$hasDefaultSchemaFk) {
            $this->addForeignKey('fk_collection_default_schema', '{{%collection}}', 'default_schema_id', '{{%schema}}', 'id', 'SET NULL', 'CASCADE');
        }

        return true;
    }

    public function safeDown(): bool
    {
        $schema = $this->db->getSchema();
        $collectionTable = $schema->getTableSchema('{{%collection}}', true);

        if ($collectionTable !== null) {
            $foreignKeys = $schema->getTableForeignKeys('{{%collection}}');
            foreach ($foreignKeys as $fk) {
                if ($fk->name === 'fk_collection_default_schema') {
                    $this->dropForeignKey('fk_collection_default_schema', '{{%collection}}');
                    break;
                }
            }

            $indexes = $schema->getTableIndexes('{{%collection}}');
            foreach ($indexes as $index) {
                if ($index->name === 'ux_collection_workspace_handle') {
                    $this->dropIndex('ux_collection_workspace_handle', '{{%collection}}');
                    break;
                }
            }

            if ($collectionTable->getColumn('publication_rules') !== null) {
                $this->dropColumn('{{%collection}}', 'publication_rules');
            }
            if ($collectionTable->getColumn('url_rules') !== null) {
                $this->dropColumn('{{%collection}}', 'url_rules');
            }
            if ($collectionTable->getColumn('default_schema_id') !== null) {
                $this->dropColumn('{{%collection}}', 'default_schema_id');
            }
            if ($collectionTable->getColumn('structure') !== null) {
                $this->dropColumn('{{%collection}}', 'structure');
            }
            if ($collectionTable->getColumn('handle') !== null) {
                $this->dropColumn('{{%collection}}', 'handle');
            }
        }

        $schemaTable = $schema->getTableSchema('{{%schema}}', true);
        if ($schemaTable !== null) {
            $foreignKeys = $schema->getTableForeignKeys('{{%schema}}');
            foreach ($foreignKeys as $fk) {
                if ($fk->name === 'fk_schema_workspace') {
                    $this->dropForeignKey('fk_schema_workspace', '{{%schema}}');
                    break;
                }
            }
            $this->dropTable('{{%schema}}');
        }

        return true;
    }

    private function slugify(string $name, int $id): string
    {
        $lower = strtolower($name);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $lower) ?? '';
        $slug = trim($slug, '-');

        return $slug !== '' ? $slug : 'collection-' . $id;
    }
}

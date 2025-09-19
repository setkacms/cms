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
 * @author    Vitaliy Kamelin <v.kamelin@gmail.com>
 * @license   Proprietary
 *
 * https://github.com/setkacms/cms
 * See LICENSE file for details.
 */

declare(strict_types=1);

use yii\db\Migration;

/**
 * Добавляет поддержку иерархии для таблицы element.
 */
final class m250922_110000_extend_element_tree extends Migration
{
    public function safeUp(): void
    {
        $schema = $this->db->getSchema();
        $table = $schema->getTableSchema('{{%element}}', true);
        if ($table === null) {
            return;
        }

        if ($table->getColumn('parent_id') === null) {
            $this->addColumn('{{%element}}', 'parent_id', $this->integer()->null());
        }

        if ($table->getColumn('position') === null) {
            $this->addColumn('{{%element}}', 'position', $this->integer()->notNull()->defaultValue(0));
            $this->update('{{%element}}', ['position' => 0], ['position' => null]);
        }

        if ($table->getColumn('lft') === null) {
            $this->addColumn('{{%element}}', 'lft', $this->integer()->null());
        }

        if ($table->getColumn('rgt') === null) {
            $this->addColumn('{{%element}}', 'rgt', $this->integer()->null());
        }

        if ($table->getColumn('depth') === null) {
            $this->addColumn('{{%element}}', 'depth', $this->integer()->null());
        }

        $indexes = $schema->getTableIndexes('{{%element}}');
        $indexNames = [];
        foreach ($indexes as $index) {
            $indexNames[] = $index->name;
        }

        if (!in_array('idx_element_parent', $indexNames, true)) {
            $this->createIndex('idx_element_parent', '{{%element}}', 'parent_id');
        }

        if (!in_array('idx_element_parent_position', $indexNames, true)) {
            $this->createIndex('idx_element_parent_position', '{{%element}}', ['parent_id', 'position']);
        }

        if (!in_array('idx_element_lft_rgt', $indexNames, true)) {
            $this->createIndex('idx_element_lft_rgt', '{{%element}}', ['lft', 'rgt']);
        }

        if (!in_array('idx_element_depth', $indexNames, true)) {
            $this->createIndex('idx_element_depth', '{{%element}}', 'depth');
        }

        $foreignKeys = $schema->getTableForeignKeys('{{%element}}');
        $hasParentFk = false;
        foreach ($foreignKeys as $foreignKey) {
            if ($foreignKey->name === 'fk_element_parent') {
                $hasParentFk = true;
                break;
            }
        }

        if (!$hasParentFk) {
            $this->addForeignKey(
                'fk_element_parent',
                '{{%element}}',
                'parent_id',
                '{{%element}}',
                'id',
                'SET NULL',
                'CASCADE'
            );
        }
    }

    public function safeDown(): void
    {
        $schema = $this->db->getSchema();
        $table = $schema->getTableSchema('{{%element}}', true);
        if ($table === null) {
            return;
        }

        $foreignKeys = $schema->getTableForeignKeys('{{%element}}');
        foreach ($foreignKeys as $foreignKey) {
            if ($foreignKey->name === 'fk_element_parent') {
                $this->dropForeignKey('fk_element_parent', '{{%element}}');
                break;
            }
        }

        $indexes = $schema->getTableIndexes('{{%element}}');
        foreach ($indexes as $index) {
            switch ($index->name) {
                case 'idx_element_parent':
                    $this->dropIndex('idx_element_parent', '{{%element}}');
                    break;
                case 'idx_element_parent_position':
                    $this->dropIndex('idx_element_parent_position', '{{%element}}');
                    break;
                case 'idx_element_lft_rgt':
                    $this->dropIndex('idx_element_lft_rgt', '{{%element}}');
                    break;
                case 'idx_element_depth':
                    $this->dropIndex('idx_element_depth', '{{%element}}');
                    break;
            }
        }

        if ($table->getColumn('depth') !== null) {
            $this->dropColumn('{{%element}}', 'depth');
        }

        if ($table->getColumn('rgt') !== null) {
            $this->dropColumn('{{%element}}', 'rgt');
        }

        if ($table->getColumn('lft') !== null) {
            $this->dropColumn('{{%element}}', 'lft');
        }

        if ($table->getColumn('position') !== null) {
            $this->dropColumn('{{%element}}', 'position');
        }

        if ($table->getColumn('parent_id') !== null) {
            $this->dropColumn('{{%element}}', 'parent_id');
        }
    }
}

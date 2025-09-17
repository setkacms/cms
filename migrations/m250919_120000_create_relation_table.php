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
 * Создаёт таблицу relation для хранения связей между элементами.
 */
final class m250919_120000_create_relation_table extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%relation}}', [
            'id' => $this->primaryKey(),
            'from_element_id' => $this->integer()->notNull(),
            'to_element_id' => $this->integer()->notNull(),
            'role' => $this->string(64)->notNull(),
            'position' => $this->integer()->notNull()->defaultValue(0),
            'meta' => $this->text()->notNull()->defaultValue('{}'),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->createIndex('ux_relation_unique', '{{%relation}}', ['from_element_id', 'role', 'to_element_id'], true);
        $this->createIndex('idx_relation_from_role', '{{%relation}}', ['from_element_id', 'role']);
        $this->createIndex('idx_relation_to', '{{%relation}}', 'to_element_id');
        $this->createIndex('idx_relation_role', '{{%relation}}', 'role');
        $this->createIndex('idx_relation_position', '{{%relation}}', 'position');

        $this->addForeignKey(
            'fk_relation_from',
            '{{%relation}}',
            'from_element_id',
            '{{%element}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_relation_to',
            '{{%relation}}',
            'to_element_id',
            '{{%element}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('fk_relation_to', '{{%relation}}');
        $this->dropForeignKey('fk_relation_from', '{{%relation}}');
        $this->dropTable('{{%relation}}');
    }
}

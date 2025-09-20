<?php
/*
 * This file is part of Setka CMS.
 *
 * @package   Setka CMS
 */

declare(strict_types=1);

use yii\db\Migration;

final class m250925_130000_create_workflow_tables extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%workflow}}', [
            'id' => $this->primaryKey(),
            'uid' => $this->char(32)->notNull(),
            'handle' => $this->string(64)->notNull(),
            'name' => $this->string(128)->notNull(),
            'description' => $this->text()->null(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);
        $this->createIndex('ux_workflow_uid', '{{%workflow}}', 'uid', true);
        $this->createIndex('ux_workflow_handle', '{{%workflow}}', 'handle', true);

        $this->createTable('{{%workflow_state}}', [
            'id' => $this->primaryKey(),
            'uid' => $this->char(32)->notNull(),
            'workflow_id' => $this->integer()->notNull(),
            'handle' => $this->string(64)->notNull(),
            'name' => $this->string(128)->notNull(),
            'type' => $this->string(32)->notNull(),
            'color' => $this->string(16)->notNull()->defaultValue('#3c8dbc'),
            'is_initial' => $this->boolean()->notNull()->defaultValue(false),
            'position' => $this->integer()->notNull()->defaultValue(0),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);
        $this->createIndex('ux_workflow_state_uid', '{{%workflow_state}}', 'uid', true);
        $this->createIndex('ux_workflow_state_handle', '{{%workflow_state}}', ['workflow_id', 'handle'], true);
        $this->createIndex('idx_workflow_state_workflow', '{{%workflow_state}}', 'workflow_id');
        $this->addForeignKey(
            'fk_workflow_state_workflow',
            '{{%workflow_state}}',
            'workflow_id',
            '{{%workflow}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->createTable('{{%workflow_transition}}', [
            'id' => $this->primaryKey(),
            'uid' => $this->char(32)->notNull(),
            'workflow_id' => $this->integer()->notNull(),
            'name' => $this->string(128)->notNull(),
            'from_state_id' => $this->integer()->notNull(),
            'to_state_id' => $this->integer()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);
        $this->createIndex('ux_workflow_transition_uid', '{{%workflow_transition}}', 'uid', true);
        $this->createIndex('idx_workflow_transition_workflow', '{{%workflow_transition}}', 'workflow_id');
        $this->createIndex('idx_workflow_transition_from', '{{%workflow_transition}}', 'from_state_id');
        $this->createIndex('idx_workflow_transition_to', '{{%workflow_transition}}', 'to_state_id');
        $this->addForeignKey(
            'fk_workflow_transition_workflow',
            '{{%workflow_transition}}',
            'workflow_id',
            '{{%workflow}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_workflow_transition_from',
            '{{%workflow_transition}}',
            'from_state_id',
            '{{%workflow_state}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_workflow_transition_to',
            '{{%workflow_transition}}',
            'to_state_id',
            '{{%workflow_state}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->createTable('{{%workflow_transition_role}}', [
            'id' => $this->primaryKey(),
            'transition_id' => $this->integer()->notNull(),
            'role' => $this->string(64)->notNull(),
        ]);
        $this->createIndex('idx_workflow_transition_role_transition', '{{%workflow_transition_role}}', 'transition_id');
        $this->createIndex(
            'ux_workflow_transition_role_unique',
            '{{%workflow_transition_role}}',
            ['transition_id', 'role'],
            true
        );
        $this->addForeignKey(
            'fk_workflow_transition_role_transition',
            '{{%workflow_transition_role}}',
            'transition_id',
            '{{%workflow_transition}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addColumn('{{%element}}', 'workflow_state_id', $this->integer()->null());
        $this->createIndex('idx_element_workflow_state', '{{%element}}', 'workflow_state_id');
        $this->addForeignKey(
            'fk_element_workflow_state',
            '{{%element}}',
            'workflow_state_id',
            '{{%workflow_state}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->seedDefaultWorkflow();
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('fk_element_workflow_state', '{{%element}}');
        $this->dropIndex('idx_element_workflow_state', '{{%element}}');
        $this->dropColumn('{{%element}}', 'workflow_state_id');

        $this->dropForeignKey('fk_workflow_transition_role_transition', '{{%workflow_transition_role}}');
        $this->dropTable('{{%workflow_transition_role}}');

        $this->dropForeignKey('fk_workflow_transition_to', '{{%workflow_transition}}');
        $this->dropForeignKey('fk_workflow_transition_from', '{{%workflow_transition}}');
        $this->dropForeignKey('fk_workflow_transition_workflow', '{{%workflow_transition}}');
        $this->dropIndex('idx_workflow_transition_to', '{{%workflow_transition}}');
        $this->dropIndex('idx_workflow_transition_from', '{{%workflow_transition}}');
        $this->dropIndex('idx_workflow_transition_workflow', '{{%workflow_transition}}');
        $this->dropIndex('ux_workflow_transition_uid', '{{%workflow_transition}}');
        $this->dropTable('{{%workflow_transition}}');

        $this->dropForeignKey('fk_workflow_state_workflow', '{{%workflow_state}}');
        $this->dropIndex('idx_workflow_state_workflow', '{{%workflow_state}}');
        $this->dropIndex('ux_workflow_state_handle', '{{%workflow_state}}');
        $this->dropIndex('ux_workflow_state_uid', '{{%workflow_state}}');
        $this->dropTable('{{%workflow_state}}');

        $this->dropIndex('ux_workflow_handle', '{{%workflow}}');
        $this->dropIndex('ux_workflow_uid', '{{%workflow}}');
        $this->dropTable('{{%workflow}}');
    }

    private function seedDefaultWorkflow(): void
    {
        $timestamp = time();
        $workflowUid = bin2hex(random_bytes(16));
        $this->insert('{{%workflow}}', [
            'uid' => $workflowUid,
            'handle' => 'default',
            'name' => 'Стандартный процесс',
            'description' => 'Базовая схема согласования контента.',
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);
        $workflowId = (int) $this->db->getLastInsertID();

        $states = [
            [
                'handle' => 'draft',
                'name' => 'Черновик',
                'type' => 'draft',
                'color' => '#d2d6de',
                'is_initial' => true,
                'position' => 1,
            ],
            [
                'handle' => 'review',
                'name' => 'На ревью',
                'type' => 'review',
                'color' => '#f39c12',
                'is_initial' => false,
                'position' => 2,
            ],
            [
                'handle' => 'published',
                'name' => 'Опубликовано',
                'type' => 'published',
                'color' => '#00a65a',
                'is_initial' => false,
                'position' => 3,
            ],
        ];

        $stateIds = [];
        foreach ($states as $state) {
            $uid = bin2hex(random_bytes(16));
            $this->insert('{{%workflow_state}}', [
                'uid' => $uid,
                'workflow_id' => $workflowId,
                'handle' => $state['handle'],
                'name' => $state['name'],
                'type' => $state['type'],
                'color' => $state['color'],
                'is_initial' => $state['is_initial'],
                'position' => $state['position'],
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);
            $stateIds[$state['handle']] = (int) $this->db->getLastInsertID();
        }

        $transitions = [
            [
                'name' => 'На ревью',
                'from' => 'draft',
                'to' => 'review',
                'roles' => ['author', 'editor'],
            ],
            [
                'name' => 'Вернуть в работу',
                'from' => 'review',
                'to' => 'draft',
                'roles' => ['editor'],
            ],
            [
                'name' => 'Опубликовать',
                'from' => 'review',
                'to' => 'published',
                'roles' => ['editor', 'publisher'],
            ],
        ];

        foreach ($transitions as $transition) {
            $uid = bin2hex(random_bytes(16));
            $this->insert('{{%workflow_transition}}', [
                'uid' => $uid,
                'workflow_id' => $workflowId,
                'name' => $transition['name'],
                'from_state_id' => $stateIds[$transition['from']] ?? null,
                'to_state_id' => $stateIds[$transition['to']] ?? null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);

            $transitionId = (int) $this->db->getLastInsertID();
            foreach ($transition['roles'] as $role) {
                $this->insert('{{%workflow_transition_role}}', [
                    'transition_id' => $transitionId,
                    'role' => $role,
                ]);
            }
        }
    }
}

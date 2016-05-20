<?php

use yii\db\Migration;

class m200501_000000_init extends Migration
{
    public function safeUp()
    {
        $this->createTable('qm_queues', [
            'id' => $this->primaryKey(),
            'tag' => $this->string(15)->notNull(),
            'name' => $this->string(255)->notNull(),
            'description' => $this->string(),
            'scheduler' => $this->string(50),
            'options' => $this->string(),
            'tasks_per_shot' => $this->integer()->defaultValue(1)->notNull()->comment('Number of tasks handled at one shot'),
            'pid' => $this->integer(),
        ], null);

        $this->createTable('qm_tasks', [
            'id' => $this->primaryKey(),
            'time_created' => $this->timestamp(),
            'time_start' => $this->timestamp(),
            'priority' => $this->integer()->defaultValue(100),
            'queue_id' => $this->integer(),
            'route' => $this->string(),
            'params' => $this->string(),
        ], null);

        $this->createIndex('qm_tasks_queue_id_idx', 'qm_tasks', 'queue_id');

        $this->addForeignKey('qm_tasks_queue_id_qm_queues_id_fk', 'qm_tasks', 'queue_id', 'qm_queues', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropTable('qm_queues');
        $this->dropTable('qm_tasks');
    }
}
<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%member}}`.
 */
class m221126_084941_create_member_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%member}}', [
            'id' => $this->primaryKey(),
            'uuid' => $this->string(255),
            'username' => $this->string(255),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%member}}');
    }
}

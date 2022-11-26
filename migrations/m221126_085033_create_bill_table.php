<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%bill}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%member}}`
 */
class m221126_085033_create_bill_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%bill}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string(255),
            'owner_id' => $this->integer(),
            'created_at' => $this->timestamp(),
            'is_closed' => $this->boolean()->defaultValue(false),
        ]);

        // creates index for column `owner_id`
        $this->createIndex(
            '{{%idx-bill-owner_id}}',
            '{{%bill}}',
            'owner_id'
        );

        // add foreign key for table `{{%member}}`
        $this->addForeignKey(
            '{{%fk-bill-owner_id}}',
            '{{%bill}}',
            'owner_id',
            '{{%member}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%member}}`
        $this->dropForeignKey(
            '{{%fk-bill-owner_id}}',
            '{{%bill}}'
        );

        // drops index for column `owner_id`
        $this->dropIndex(
            '{{%idx-bill-owner_id}}',
            '{{%bill}}'
        );

        $this->dropTable('{{%bill}}');
    }
}

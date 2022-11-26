<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%bill_member}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%bill}}`
 * - `{{%member}}`
 */
class m221126_085317_create_bill_member_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%bill_member}}', [
            'id' => $this->primaryKey(),
            'bill_id' => $this->integer(),
            'member_id' => $this->integer(),
        ]);

        // creates index for column `bill_id`
        $this->createIndex(
            '{{%idx-bill_member-bill_id}}',
            '{{%bill_member}}',
            'bill_id'
        );

        // add foreign key for table `{{%bill}}`
        $this->addForeignKey(
            '{{%fk-bill_member-bill_id}}',
            '{{%bill_member}}',
            'bill_id',
            '{{%bill}}',
            'id',
            'CASCADE'
        );

        // creates index for column `member_id`
        $this->createIndex(
            '{{%idx-bill_member-member_id}}',
            '{{%bill_member}}',
            'member_id'
        );

        // add foreign key for table `{{%member}}`
        $this->addForeignKey(
            '{{%fk-bill_member-member_id}}',
            '{{%bill_member}}',
            'member_id',
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
        // drops foreign key for table `{{%bill}}`
        $this->dropForeignKey(
            '{{%fk-bill_member-bill_id}}',
            '{{%bill_member}}'
        );

        // drops index for column `bill_id`
        $this->dropIndex(
            '{{%idx-bill_member-bill_id}}',
            '{{%bill_member}}'
        );

        // drops foreign key for table `{{%member}}`
        $this->dropForeignKey(
            '{{%fk-bill_member-member_id}}',
            '{{%bill_member}}'
        );

        // drops index for column `member_id`
        $this->dropIndex(
            '{{%idx-bill_member-member_id}}',
            '{{%bill_member}}'
        );

        $this->dropTable('{{%bill_member}}');
    }
}

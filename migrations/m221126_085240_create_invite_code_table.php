<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%invite_code}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%bill}}`
 */
class m221126_085240_create_invite_code_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%invite_code}}', [
            'id' => $this->primaryKey(),
            'code' => $this->string(5)->notNull(),
            'bill_id' => $this->integer(),
            'expired_at' => $this->timestamp(),
        ]);

        // creates index for column `bill_id`
        $this->createIndex(
            '{{%idx-invite_code-bill_id}}',
            '{{%invite_code}}',
            'bill_id'
        );

        // add foreign key for table `{{%bill}}`
        $this->addForeignKey(
            '{{%fk-invite_code-bill_id}}',
            '{{%invite_code}}',
            'bill_id',
            '{{%bill}}',
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
            '{{%fk-invite_code-bill_id}}',
            '{{%invite_code}}'
        );

        // drops index for column `bill_id`
        $this->dropIndex(
            '{{%idx-invite_code-bill_id}}',
            '{{%invite_code}}'
        );

        $this->dropTable('{{%invite_code}}');
    }
}

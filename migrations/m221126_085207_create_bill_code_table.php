<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%bill_code}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%bill}}`
 */
class m221126_085207_create_bill_code_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%bill_code}}', [
            'id' => $this->primaryKey(),
            'code' => $this->string(5)->notNull(),
            'bill_id' => $this->integer(),
            'expired_at' => $this->timestamp(),
        ]);

        // creates index for column `bill_id`
        $this->createIndex(
            '{{%idx-bill_code-bill_id}}',
            '{{%bill_code}}',
            'bill_id'
        );

        // add foreign key for table `{{%bill}}`
        $this->addForeignKey(
            '{{%fk-bill_code-bill_id}}',
            '{{%bill_code}}',
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
            '{{%fk-bill_code-bill_id}}',
            '{{%bill_code}}'
        );

        // drops index for column `bill_id`
        $this->dropIndex(
            '{{%idx-bill_code-bill_id}}',
            '{{%bill_code}}'
        );

        $this->dropTable('{{%bill_code}}');
    }
}

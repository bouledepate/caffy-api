<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%dish}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%bill_member}}`
 */
class m221126_085436_create_dish_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%dish}}', [
            'id' => $this->primaryKey(),
            'bill_member_id' => $this->integer(),
            'title' => $this->string(255),
            'cost' => $this->integer(),
            'type' => $this->string(255)->defaultValue('personal'),
        ]);

        // creates index for column `bill_member_id`
        $this->createIndex(
            '{{%idx-dish-bill_member_id}}',
            '{{%dish}}',
            'bill_member_id'
        );

        // add foreign key for table `{{%bill_member}}`
        $this->addForeignKey(
            '{{%fk-dish-bill_member_id}}',
            '{{%dish}}',
            'bill_member_id',
            '{{%bill_member}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%bill_member}}`
        $this->dropForeignKey(
            '{{%fk-dish-bill_member_id}}',
            '{{%dish}}'
        );

        // drops index for column `bill_member_id`
        $this->dropIndex(
            '{{%idx-dish-bill_member_id}}',
            '{{%dish}}'
        );

        $this->dropTable('{{%dish}}');
    }
}

<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%refused_dish}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%dish}}`
 * - `{{%member}}`
 */
class m221126_085526_create_refused_dish_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%refused_dish}}', [
            'id' => $this->primaryKey(),
            'dish_id' => $this->integer(),
            'member_id' => $this->integer(),
            'state' => $this->boolean()->defaultValue(false),
        ]);

        // creates index for column `dish_id`
        $this->createIndex(
            '{{%idx-refused_dish-dish_id}}',
            '{{%refused_dish}}',
            'dish_id'
        );

        // add foreign key for table `{{%dish}}`
        $this->addForeignKey(
            '{{%fk-refused_dish-dish_id}}',
            '{{%refused_dish}}',
            'dish_id',
            '{{%dish}}',
            'id',
            'CASCADE'
        );

        // creates index for column `member_id`
        $this->createIndex(
            '{{%idx-refused_dish-member_id}}',
            '{{%refused_dish}}',
            'member_id'
        );

        // add foreign key for table `{{%member}}`
        $this->addForeignKey(
            '{{%fk-refused_dish-member_id}}',
            '{{%refused_dish}}',
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
        // drops foreign key for table `{{%dish}}`
        $this->dropForeignKey(
            '{{%fk-refused_dish-dish_id}}',
            '{{%refused_dish}}'
        );

        // drops index for column `dish_id`
        $this->dropIndex(
            '{{%idx-refused_dish-dish_id}}',
            '{{%refused_dish}}'
        );

        // drops foreign key for table `{{%member}}`
        $this->dropForeignKey(
            '{{%fk-refused_dish-member_id}}',
            '{{%refused_dish}}'
        );

        // drops index for column `member_id`
        $this->dropIndex(
            '{{%idx-refused_dish-member_id}}',
            '{{%refused_dish}}'
        );

        $this->dropTable('{{%refused_dish}}');
    }
}

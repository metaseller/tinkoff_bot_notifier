<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%stock}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 */
class m210706_080701_create_stock_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%stock}}', [
            'id' => $this->primaryKey(),
            'figi' => $this->string(200)->notNull(),
            'interval' => $this->string(100)->notNull(),
            'change' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
        ]);

        // creates index for column `user_id`
        $this->createIndex(
            '{{%idx-stock-user_id}}',
            '{{%stock}}',
            'user_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-stock-user_id}}',
            '{{%stock}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-stock-user_id}}',
            '{{%stock}}'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            '{{%idx-stock-user_id}}',
            '{{%stock}}'
        );

        $this->dropTable('{{%stock}}');
    }
}

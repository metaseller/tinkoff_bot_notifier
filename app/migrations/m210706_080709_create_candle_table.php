<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%candle}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%stock}}`
 */
class m210706_080709_create_candle_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%candle}}', [
            'id' => $this->primaryKey(),
            'prcopen' => $this->integer(),
            'prcclose' => $this->integer(),
            'prcmin' => $this->integer(),
            'prcmax' => $this->integer(),
            'tradevolume' => $this->integer(),
            'timeq' => $this->datetime()->notNull(),
            'stock_id' => $this->integer()->notNull(),
        ]);

        // creates index for column `stock_id`
        $this->createIndex(
            '{{%idx-candle-stock_id}}',
            '{{%candle}}',
            'stock_id'
        );

        // add foreign key for table `{{%stock}}`
        $this->addForeignKey(
            '{{%fk-candle-stock_id}}',
            '{{%candle}}',
            'stock_id',
            '{{%stock}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%stock}}`
        $this->dropForeignKey(
            '{{%fk-candle-stock_id}}',
            '{{%candle}}'
        );

        // drops index for column `stock_id`
        $this->dropIndex(
            '{{%idx-candle-stock_id}}',
            '{{%candle}}'
        );

        $this->dropTable('{{%candle}}');
    }
}

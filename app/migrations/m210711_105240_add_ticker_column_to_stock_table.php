<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%stock}}`.
 */
class m210711_105240_add_ticker_column_to_stock_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%stock}}', 'ticker', $this->string(200)->notNull());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%stock}}', 'ticker');
    }
}

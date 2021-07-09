<?php


namespace app\models;

use yii\db\ActiveRecord;

class Candle extends ActiveRecord
{
    public function getStock()
    {
        return $this->hasOne(Stock::class, ['id' => 'stock_id']);
    }
}
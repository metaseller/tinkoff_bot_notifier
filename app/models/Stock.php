<?php

namespace app\models;

use yii\db\ActiveRecord;

class Stock extends ActiveRecord
{
    public function getCandle()
    {
        return $this->hasMany(Candle::class, ['stock_id' => 'id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}

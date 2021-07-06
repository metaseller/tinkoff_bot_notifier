<?php

namespace app\models;

use yii\db\ActiveRecord;

class Stock extends ActiveRecord
{

    public $figi;
    public $interval;
    public $change;
    public $user_id;

    public function attributeLabels()
    {
        return [
            'figi'=>'Акция',
            'interval'=>'Интервал проверки',
            'change'=>'Изменение цены',
        ];
    }

    public function getCandle()
    {
        return $this->hasMany(Candle::class, ['stock_id' => 'id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

}

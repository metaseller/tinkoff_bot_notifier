<?php


namespace app\models;

use yii\db\ActiveRecord;

class Candle extends ActiveRecord
{
    public $prcopen;
    public $prcclose;
    public $prcmin;
    public $prcmax;
    public $tradevolume;
    public $timeq;
    public $stock_id;

    public function attributeLabels()
    {
        return [
            'prcopen'=>'Цена открытия свечи',
            'prcclose'=>'Цена закрытия свечи',
            'prcmin'=>'Минимальная цена',
            'prcmax'=>'Максимальная цена',
            'tradevolume'=>'ОбЪём торгов',
            'timeq'=>'Время',

        ];
    }

    public function getStock()
    {
        return $this->hasOne(Stock::class, ['id' => 'stock_id']);
    }

}
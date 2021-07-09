<?php

namespace app\models;

use yii\db\ActiveRecord;

class User extends ActiveRecord
{
    public function getStock()
    {
        return $this->hasMany(Stock::class, ['user_id' => 'id']);
    }
}

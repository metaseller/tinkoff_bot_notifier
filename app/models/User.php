<?php

namespace app\models;

use yii\db\ActiveRecord;

class User extends ActiveRecord
{
    public $iduser;
    public $token;
    public $id;
    public function attributeLabels()
    {
        return [
            'iduser'=>'ID',
            'token'=>'Токен',
        ];
    }

    public function getStock()
    {
        return $this->hasMany(Stock::class, ['user_id' => 'id']);
    }

}

<?php


namespace app\models;


use yii\db\ActiveRecord;

class Mfu extends ActiveRecord
{

    CONST PARAMS_PAGE = [
        'title_list' => [
            'Модель',
            'Расположение',
            'имя хоста',
            'IP адрес',
            'Пользователь'
        ],
        'value_list' => [
            'model',
            'location',
            'host_name',
            'IP_address',
            'user' => 'full_name'
        ],
        'title_table' => 'МФУ',
        'page' => 'mfu',
    ];

    public function getUser() {
        return $this->hasOne(Users::classname(), ['id' => 'user_id']);
    }

}
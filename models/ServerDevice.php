<?php


namespace app\models;


use yii\db\ActiveRecord;

class ServerDevice extends ActiveRecord
{

    CONST PARAMS_PAGE = [
        'title_list' => [
            'Имя хоста',
            'IP адрес',
            'Подробности',
            'Модель',
        ],
        'value_list' => [
            'host_name',
            'IP_address',
            'description',
            'model',
        ],
        'title_table' => 'СЕРВЕРНЫЕ ОБОРОДОВАНИЯ',
        'page' => 'server',
    ];

}
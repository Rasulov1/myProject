<?php


namespace app\models;


use yii\db\ActiveRecord;

class NetworkDevice extends ActiveRecord
{

    CONST PARAMS_PAGE = [
        'title_list' => [
            'Модель',
            'Серия',
            'Расположение',
            'IP адрес',
        ],
        'value_list' => [
            'model',
            'seria',
            'location',
            'IP_address',
        ],
        'title_table' => 'СЕТЕВЫЕ ОБОРУДОВАНИЯ',
        'page' => 'network',
    ];

}
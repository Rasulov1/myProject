<?php


namespace app\models;


use yii\db\ActiveRecord;

class Phone extends ActiveRecord
{

    CONST PARAMS_PAGE = [
        'title_list' => [
            'Внутренний номер',
            'Внешний номер',
            'IP адрес',
            'Расположение',
        ],
        'value_list' => [
            'interior_num',
            'external_num',
            'IP_address',
            'location',
        ],
        'title_table' => 'ТЕЛЕФОННЫЕ СТАНЦИИ',
        'page' => 'phone',
    ];

}
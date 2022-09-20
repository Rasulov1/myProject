<?php


namespace app\models;


use yii\db\ActiveRecord;

class Company extends ActiveRecord
{

    CONST PARAMS_PAGE = [
        'title_list' => [
            'Наименование',
            'Адрес',
            'Директор',
            'ИНН'
        ],
        'value_list' => [
            'name',
            'address',
            'directory',
            'INN'
        ],
        'title_table' => 'ОРГАНИЗАЦИИ',
        'page' => 'company'
    ];



}
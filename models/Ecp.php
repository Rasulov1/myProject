<?php


namespace app\models;


use yii\db\ActiveRecord;

class Ecp extends ActiveRecord
{

    CONST PARAMS_PAGE = [
        'title_list' => [
            'Компания',
            'Дата окончания',
            'Ответственный',
        ],
        'value_list' => [
            'company' => 'name',
            'date_finish',
            'user' => 'full_name',
        ],
        'title_table' => 'ЭЦП',
        'page' => 'ecp',
    ];

    public function getLocation() {
        return $this->hasOne(Users::classname(), ['id' => 'location_id']);
    }

    public function getUser() {
        return $this->hasOne(Users::classname(), ['id' => 'user_id']);
    }

    public function getCompany() {
        return $this->hasOne(Company::classname(), ['id' => 'company_id']);
    }



}
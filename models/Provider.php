<?php


namespace app\models;


use yii\db\ActiveRecord;

class Provider extends ActiveRecord
{

    CONST PARAMS_PAGE = [
        'title_list' => [
            'Провайдер',
            'Номер договора',
            'Организация',
            'Стоимость',
            'Телефон',
        ],
        'value_list' => [
            'name',
            'contract_num',
            'company' => 'name',
            'cost',
            'manager_phone',
        ],
        'title_table' => 'ПРОВАЙДЕРЫ',
        'page' => 'provider',
    ];

    public function getCompany() {
        return $this->hasOne(Company::classname(), ['id' => 'company_id']);
    }

}
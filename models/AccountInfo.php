<?php


namespace app\models;

use yii\db\ActiveRecord;

class AccountInfo extends ActiveRecord
{

    CONST PARAMS_PAGE = [
        'title_list' => [
            'Имя пользователя',
            'Имя компьютера',
            'Логин',
            'Пароль',
            'VPN IP',
            'Домен',
        ],
        'value_list' => [
            'user' => 'full_name',
            'comp_name',
            'lk_login',
            'lk_pass',
            'vpn_ip',
            'domen',
        ],
        'title_table' => 'УЧЕТНЫЕ ЗАПИСИ',
        'page' => 'account',
    ];

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'account_info';
    }

    public function getUser() {
        return $this->hasOne(Users::classname(), ['id' => 'user_id']);
    }


}
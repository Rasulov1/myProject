<?php


namespace app\models;


use yii\db\ActiveRecord;

class CartridgeHistory extends ActiveRecord
{

    CONST PARAMS_PAGE = [
        'title_list' => [
            '',
            'id',
            'Модель',
            'Расположение',
            'Продолжительность',
        ],
        'value_list' => [
            'cartridge_status',
            'cartridge_id',
            'model',
            'location_id',
            'date_action',
        ],
        'title_table' => 'КАТРИДЖИ',
        'page' => 'cartridge',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cartridge_history';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [

        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [

        ];
    }

    public function getCartridge() {
        return $this->hasOne(Cartridge::classname(), ['id' => 'cartridge_id']);
    }

}
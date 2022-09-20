<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "complaints".
 *
 * @property int $id
 * @property string $message
 * @property int|null $user_id
 * @property int $create_at
 * @property int $status
 */
class Complaints extends ActiveRecord
{

    CONST PARAMS_PAGE = [
        'title_list' => [
            '',
            '#',
            'Пользователь',
            'Тема',
            'Ответственный',
            'Дата',
        ],
        'value_list' => [
            '',
            'increment',
            'user' => 'full_name',
            'title',
            'worker' => 'full_name',
            'create_at',
        ],
        'title_table' => 'ЗАЯВКИ',
        'page' => 'complaints',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'complaints';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'message', 'create_at'], 'required'],
            [['status', 'type_id'], 'default', 'value' => 1],
            ['params', 'default', 'value' => '1'],
//            [['params'], 'value' => '1'],
//            [['user_id', 'create_at', 'status'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'message' => 'Message',
            'user_id' => 'User ID',
            'create_at' => 'Create At',
            'status' => 'Status',
            'type_id' => 'Type ID',
            'params' => 'Params',
            'worker_id' => 'Worker Id'
        ];
    }

    public static function findByUserId($user_id) {
        return self::find()->where(['user_id' => $user_id])->orderBy('status, create_at DESC')->asArray()->indexBy('id')->all();
    }

    public function getUser() {
        return $this->hasOne(Users::classname(), ['id' => 'user_id']);
    }

    public function getWorker() {
        return $this->hasOne(Users::classname(), ['id' => 'worker_id']);
    }

    public function getComplaintsStatus() {
        return $this->hasOne(ComplaintsStatus::classname(), ['id' => 'status']);
    }

    public static function getComplaints() {
        return self::find()->with(['user', 'worker', 'complaintsStatus'])->orderBy('status, create_at DESC')->all();
    }

}

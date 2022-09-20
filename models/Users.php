<?php

namespace app\models;

use Yii;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property string $username
 * @property string $auth_key
 * @property string $password_hash
 * @property string|null $password_reset_token
 * @property string $email
 * @property int $status
 * @property int $create_at
 */
class Users extends \yii\db\ActiveRecord implements IdentityInterface
{

    const STATUS_DELETE = 0;
    const STATUS_WAIT = 1;
    const STATUS_ACTIVE = 2;

    CONST PARAMS_PAGE = [
        'title_list' => [
            'ФИО',
            'Организация',
            'Телефон',
            'Почта',
            'Должность',
        ],
        'value_list' => [
            'full_name',
            'company' => 'name',
            'phone',
            'mail',
            'position' => 'name',
        ],
        'create_title' => 'Добавление пользователя',
        'create_data' => [
            'second_name' => [
                'id' => 'second_name',
                'name' => 'Фамилия',
                'type' => 'text'
            ],
            'first_name' => [
                'id' => 'first_name',
                'name' => 'Имя',
                'type' => 'text'
            ],
            'patronymic' => [
                'id' => 'patronymic',
                'name' => 'Отчество',
                'type' => 'text'
            ],
            'phone' => [
                'id' => 'phone',
                'name' => 'Телефон',
                'type' => 'phone'
            ],
            'company' => [
                'id' => 'company',
                'name' => 'Компания',
                'type' => 'select'
            ],
            'position' => [
                'id' => 'position',
                'name' => 'Должность',
                'type' => 'select'
            ],
            'status' => [
                'id' => 'status',
                'name' => 'Права пользователя',
                'type' => 'select'
            ],
            'mail' => [
                'id' => 'mail',
                'name' => 'Почта',
                'type' => 'text'
            ],
        ],
        'title_table' => 'ПОЛЬЗОВАТЕЛИ',
        'page' => 'users',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'users';
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'second_name' => 'Second name',
            'first_name' => 'First name',
            'patronymic' => 'Patronymic',
            'mail' => 'Mail',
            'phone' => 'Phone',
            'company_id' => 'Company id',
            'company_alias' => 'Company alias',
            'create_at' => 'Create at',
            'status_id' => 'Status id',
            'position_id' => 'Position id'
        ];
    }

    public static function findByPhone($phone)
    {
        return self::find()->where(['phone' => $phone])->one();
    }

    public static function findByMail($email)
    {
        return self::find()->where(['mail' => $email, 'delete' => 0])->one();
    }
    
    public function isAdmin() {
        return $this->status_id === 1;
    }

    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAuthKey()
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey)
    {
        return $this->auth_key === $authKey;
    }

    public static function getAdminMail()
    {
        return self::find()->select(['mail'])->where(['status_id' => 1])->asArray()->all();
    }

    public static function getUsers() {
        return self::find()->with(['position', 'status', 'company'])->orderBy('full_name')->where(['delete' => false])->all();
    }

    public function getPosition() {
        return $this->hasOne(UsersPosition::classname(), ['id' => 'position_id']);
    }
    public function getStatus() {
        return $this->hasOne(UsersStatus::classname(), ['id' => 'status_id']);
    }

    public function getCompany() {
        return $this->hasOne(Company::classname(), ['id' => 'company_id']);
    }

    public function getAccountInfo() {
        return $this->hasOne(AccountInfo::className(), ['user_id' => 'id']);
    }

}

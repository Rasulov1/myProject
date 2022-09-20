<?php


namespace app\models;

use app\models\Users;
use Yii;
use yii\base\Model;

class LoginForm extends Model
{
    public $phone;
    public $password;

    public function rules()
    {
        return [
            ['phone', 'trim'],
            ['phone', 'required', 'message' => 'Необходимо заполнить поле «Логин».'],
            ['password', 'required', 'message' => 'Необходимо заполнить поле «Пароль».'],
            ['password', 'validatePassword'],
        ];
    }

    public function login()
    {
        $this->phone = preg_replace("/[^0-9]/", '', $this->phone);
        if ($this->validate()) {
            $user = Users::findByPhone($this->phone);
            if ($user->active === Users::STATUS_ACTIVE) {
                return Yii::$app->user->login($user);
            }
            if($user->active === Users::STATUS_WAIT){
                Yii::$app->getSession()->setFlash('warning', 'Проверьте свою электронную почту, чтобы подтвердить регистрацию.');
                return Yii::$app->user->login($user);
            }
        }
        return false;
    }

    public function validatePassword($attribute, $params)
    {
        $user = Users::findByPhone($this->phone);

        if (!$user || !$user->validatePassword($this->password)) {
            $this->addError($attribute, 'Неверный пароль!');
        }

    }
}
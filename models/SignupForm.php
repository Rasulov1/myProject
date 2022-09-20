<?php


namespace app\models;

use Yii;
use yii\base\Model;
use app\models\Users;
use yii\db\Exception;

class SignupForm extends Model
{
    public $first_name;
    public $second_name;
    public $patronymic;
    public $phone;
    public $mail;
    public $company_alias;
    public $password;

    public function rules()
    {
        return [
            ['first_name', 'trim'],
            ['first_name', 'required', 'message' => 'Необходимо заполнить поле «Имя».'],
            
            ['second_name', 'trim'],
            ['second_name', 'required', 'message' => 'Необходимо заполнить поле «Фамилия».'],
            
            ['patronymic', 'trim'],
            ['patronymic', 'required', 'message' => 'Необходимо заполнить поле «Отчество».'],

            ['phone', 'trim'],
            ['phone', 'required', 'message' => 'Необходимо заполнить поле «Телефон».'],
            ['phone', 'string', 'length' => 17, 'message' => 'Номер должен содержать минимум 10 символа.'],
            [['phone'], 'unique', 'targetClass' => Users::className(), 'message' => 'Пользователь с данныи телефоном уже существует.'],

            ['mail', 'trim'],
            ['mail', 'email'],
            ['mail', 'required', 'message' => 'Необходимо заполнить поле «Почта».'],
            ['mail', 'string', 'max' => 255],

            ['company_alias', 'trim'],
            ['company_alias', 'required', 'message' => 'Необходимо заполнить поле «Компания».'],
            ['company_alias', 'string', 'max' => 255],

            ['password', 'required', 'message' => 'Необходимо заполнить поле «Пароль».'],
            ['password', 'string', 'min' => 5, 'message' => 'Длинна пароля не меньше 5 символов.'],
        ];
    }

    public function save(SignupForm $form)
    {
        $phone = preg_replace('/[^0-9]/', '', $form->phone);
        if ($form->validate()) {
            $user = Users::find()->where(['mail' => $form->mail])->orWhere(['phone' => $phone])->andWhere(['delete' => 0])->one();

            if ($user) {
                throw new \RuntimeException('Данный пользователь уже существует.');
                return 'Данный пользователь уже существует';
            }

            $user = new Users();
            $user->mail = $form->mail;
            $user->first_name = $form->first_name;
            $user->second_name = $form->second_name;
            $user->patronymic = $form->patronymic;
            $user->full_name = $form->second_name . ' ' . $form->first_name . ' ' . $form->patronymic;
            $user->phone = $phone;
            $user->company_alias = $form->company_alias;
            $user->create_at = date('Y-m-d H:i:s');
            $user->auth_key = Yii::$app->security->generateRandomString();
            $user->password_hash = Yii::$app->security->generatePasswordHash($form->password);
            $user->email_confirm_token = Yii::$app->security->generateRandomString();
            $user->active = Users::STATUS_WAIT;
            $user->status_id = 2;

            if(!$user->save()){
                throw new \RuntimeException('Saving error.');
            }

            return $user;
        }
    }

    public function sentEmailConfirm( Users $user, $new_pass = '')
    {
        $email = $user->mail;

        try {
            $sent = Yii::$app->mailer
                ->compose(
                    [ 'html' => 'user-signup-comfirm-html', 'text' => 'user-signup-comfirm-text' ],
                    [
                        'user' => $user,
                        'pass' => $new_pass
                    ] )
                ->setTo( $email )
                ->setFrom( Yii::$app->params['adminEmail'] )
                ->setSubject( 'Confirmation of registration' )
                ->send();
        } catch (Exception $e) {
            Helper::sendTelegramChat($e->getMessage(), 768715019);
            return false;
        }

        return true;
    }

    public function sentEmailResetPass( Users $user, $new_pass ) {
        $sent = Yii::$app->mailer
            ->compose(
                ['html' => 'user-reset-pass-html', 'text' => 'user-reset-pass-text'],
                [
                    'user' => $user,
                    'pass' => $new_pass
                ])
            ->setTo($user->mail)
            ->setFrom(Yii::$app->params['adminEmail'])
            ->setSubject('Reset pass')
            ->send();

        if (!$sent) {
            throw new \RuntimeException('Sending error.');
        }
    }

    public function confirmation($token)
    {
        if (empty($token)) {
            throw new \DomainException('Empty confirm token.');
        }

        $user = Users::findOne(['email_confirm_token' => $token]);
        if (!$user) {
            throw new \DomainException('User is not found.');
        }

        $user->email_confirm_token = null;
        $user->active = Users::STATUS_ACTIVE;
        if (!$user->save()) {
            throw new \RuntimeException('Saving error.');
        }

        if (!Yii::$app->getUser()->login($user)){
            throw new \RuntimeException('Error authentication.');
        }
    }
}

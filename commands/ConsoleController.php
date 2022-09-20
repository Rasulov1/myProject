<?php

namespace app\commands;

use app\models\Helper;
use app\models\Users;
use DateTime;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

class ConsoleController extends Controller
{

    public function actionIndex()
    {

        var_dump(Users::getAdminMail());

        return ExitCode::OK;
    }

    public function actionChangePass($email, $new_pass)
    {

        $user = Users::findByMail($email);
        $user->password_hash = Yii::$app->security->generatePasswordHash($new_pass);
        $user->save();

        return ExitCode::OK;
    }

    public function actionResetPass($email)
    {

        $password = Helper::generatePass();

        $user = Users::findByMail($email);
        $user->password_hash = Yii::$app->security->generatePasswordHash($password);
        $user->save();

        print_r('Ваш новый пароль: ' . $password);

        return ExitCode::OK;
    }

    public function actionCreateUser($fio, $phone, $email, $company) {

        $password = Helper::generatePass();

        $user = new Users();
        $user->fio = $fio;
        $user->phone = $phone;
        $user->email = $email;
        $user->company_alias = $company;
        $user->create_at = date('Y-m-d H:i:s');
        $user->auth_key = Yii::$app->security->generateRandomString();
        $user->password_hash = Yii::$app->security->generatePasswordHash($password);
        $user->save();

        print_r("Создан новый пользователь. Данные для входа:\n");
        print_r("Логин: " . $phone . "\n");
        print_r("Пароль: " . $password);

        return ExitCode::OK;
    }

}

<?php

namespace app\controllers;

use app\models\Helper;
use app\models\LoginForm;
use app\models\SignupForm;
use app\models\Users;
use Yii;
use yii\helpers\Html;

class LoginController extends \yii\web\Controller
{
    public function actionLogin()
    {
        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            if (Yii::$app->user->identity->active == 1) {
                Yii::$app->getSession()->setFlash('warning', 'Проверьте свою электронную почту, чтобы подтвердить регистрацию.');
            } else {
                if (Yii::$app->user->identity->isAdmin()) {
                    return $this->redirect(['admin/complaints']);
                }
                return $this->redirect(['site/index']);
            }
        }
        return $this->render('login', [
            'model' => $model
        ]);
    }

    public function actionSignup()
    {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $signupFrom = new SignupForm();

            try{
                $user = $signupFrom->save($model);
                Yii::$app->getSession()->setFlash('warning', 'Проверьте свою электронную почту, чтобы подтвердить регистрацию.');
//                Yii::$app->session->setFlash('warning', 'Проверьте свою электронную почту, чтобы подтвердить регистрацию.');
                $signupFrom->sentEmailConfirm($user);
                return $this->redirect(['login/login']);
            } catch (\RuntimeException $e){
                Yii::$app->errorHandler->logException($e);
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    public function actionLogout() {
        Yii::$app->user->logout();
        return $this->redirect(['login/login']);
    }

    public function actionSignupConfirm($token)
    {
        $signupFrom = new SignupForm();

        try{
            $signupFrom->confirmation($token);
            Yii::$app->session->setFlash('success', 'Вы успешно подтвердили свою регистрацию.');
        } catch (\Exception $e){
            Yii::$app->errorHandler->logException($e);
            Yii::$app->session->setFlash('error', $e->getMessage());
        }

        return $this->goHome();
    }

    public function actionResetPass($token)
    {
        if (empty($token)) {
            throw new \DomainException('Empty confirm token.');
        }

        $user = Users::findOne(['password_reset_token' => $token]);
        if (!$user) {
            throw new \DomainException('User is not found.');
        }

        return $this->render('reset_pass', compact('user'));
    }

}

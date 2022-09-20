<?php

namespace app\controllers;

use app\models\Company;
use app\models\Complaints;
use app\models\ComplaintsStatus;
use app\models\Ecp;
use app\models\Helper;
use app\models\Users;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class SiteController extends BaseController
{

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        Helper::checlGuest();
        $session = Yii::$app->session;
        $session->set('language', 'ru-RU');

        $model = new Complaints();

        if ($model->load(Yii::$app->request->post())) {
            $model->user_id = Yii::$app->user->id;
            $model->create_at = date('Y-m-d H:i:s');
            $model->save();

            $text_telegram = "\xF0\x9F\x99\x8D: " . Yii::$app->user->identity->full_name . "\n" .
                "\xE2\x9D\x97: " . $model->title . "\n";

            $text_mail = "<b>Пользователь</b>: " . Yii::$app->user->identity->full_name . " ( " . Yii::$app->user->id . " )" . " - оставил заявку<br><br>" .
                "<b>Тема</b>: " . $model->title . "<br><br>" .
                "<b>Задача</b>: " . $model->message . "<br><br>";

            foreach ( Users::getAdminMail() as $mail) {
                Helper::sendMail($model->title, $text_mail, $mail['mail']);
            }
            Helper::sendTelegram($text_telegram);

            $this->refresh();
        }

        $complaints = Complaints::findByUserId(Yii::$app->user->id);
        $status = ComplaintsStatus::find()->asArray()->indexBy('id')->all();
        $i = 1;
        $render_complaints = '';

        foreach ($complaints as $item) {
            $time = strtotime($item['create_at']);
            $class = $i < 3 ? "top" : "";
            $render_complaints .= '<tr complaints_id="' . $item["id"] . '">
                <td class="table-indecation ' . $class . '" data-tooltip="' . $status[$item['status']]['alias'] . '" data-status="' . $status[$item['status']]['id'] . '"><span class="status-' . $status[$item['status']]['status'] . '"></span></td>
                <th class="table-counter" scope="row">' . $item["id"] . '</th>
                <td class="table-title">' . $item["title"] . '</td>
                <td class="table-date">' . Helper::rdate("d M - H:i", $time) . '</td>
            </tr>';
            $i++;
        }

        return $this->render('index', compact(
            'model',
            'complaints',
            'render_complaints'
        ));
    }

    public function actionError() {
        return $this->render('error');
    }

//    public function actionImport() {
//        $t = fopen('../web/ww.csv', 'r');
//        while ( !feof($t) ) {
//            $r[] = explode(',', fgets($t));
//        }
//
//        foreach ($r as $item) {
//            $ecp = new Ecp();
//            $ecp->seria = $item[1];
//            $company = Company::find()->where(['name' => $item[2]])->one();
//            if ($company->id) {
//                $ecp->company_id = $company->id;
//            }
//            $ecp->date_start = date('Y-m-d', strtotime($item[4]));
//            $ecp->date_finish = date('Y-m-d', strtotime($item[5]));
//            $user = User::find()->where(['second_name' => $item[6]])->one();
//            if ($user->id) {
//                $ecp->user_id = $user->id;
//            }
//            $ecp->verification_сenter = $item[7];
//            $ecp->type = $item[8];
//            $ecp->status = $item[9];
//            $ecp->where = $item[10];
//            $ecp->save();
//        }
//
//
//
//
//
//        foreach ($r as $item) {
//            if ($item[7] == 'работает') {
//                $user = new User();
//                $acc = new AccountInfo();
//                if ($item[6]) {
//                    $position = UserPosition::find()->where(['name' => $item[6]])->one();
//                    if (!$position) {
//                        $position = new UserPosition();
//                        $position->name = $item[6];
//                        $position->save();
//                    }
//                }
//
//                $user->second_name = $item[1];
//                $user->first_name = $item[2];
//                $user->patronymic = $item[3];
//                $user->mail = $item[9];
//                $user->position_id = $position->id;
//                $user->create_at = date('Y-m-d H:i:s');
//                var_dump($user->save());
//
//                $acc->user_id = $user->id;
//                $acc->comp_name = $item[4];
//                $acc->domen = $item[8];
//                $acc->lk_mail = $item[9];
//                $acc->lk_login = $item[10];
//                $acc->lk_pass = $item[11];
//                $acc->vpn_login = $item[12];
//                $acc->vpn_pass = $item[13];
//                $acc->vpn_ip = $item[14];
//                $acc->vpn_second_pass = $item[15];
//                var_dump($acc->save());
//            }
//        }
//    }

}
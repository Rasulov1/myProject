<?php


namespace app\commands;


use app\models\Ecp;
use app\models\Helper;
use app\models\Users;
use yii\console\Controller;

class CronController extends Controller
{

    public function actionTest() {

        $ecp = Ecp::find()->with(['company'])->where(['between', 'date_finish', date('Y-m-d'), date('Y-m-d', strtotime('+14 day'))])->andWhere(['delete' => false, 'notify' => 1, 'status' => 'Действует'])->orderBy('date_finish')->all();

        if (!empty($ecp)) {

            $text_telegram = "\xE2\x9D\x97 " . " *** " . "\xE2\x9D\x97 \n\n";

            foreach ($ecp as $item) {
                // отключение уведомление, статус "новое ецп"
                $text_telegram .= Helper::rdate('d M', strtotime($item->date_finish)) . " - " . $item->company->name . "\n";
            }
            Helper::sendTelegram($text_telegram);
        } else {
            $text_telegram = "\xE2\x9D\x97 " . " *** " . "\xE2\x9D\x97 \n\n ***\n ***\n *** ";
            Helper::sendTelegram($text_telegram);
        }

    }



}
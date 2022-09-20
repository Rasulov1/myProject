<?php


namespace app\models;


use DateTime;
use Yii;

class Helper
{

    /**
     * @param $theme
     * @param $message
     * @param string $admin
     */
    public static function sendMail($theme, $message, $admin = '***') {
        Yii::$app->mailer->compose()
            ->setFrom('***')
            ->setTo($admin)
            ->setSubject($theme)
            ->setHtmlBody($message)
            ->send();
    }

    /**
     * @param $message
     */
    public static function sendTelegram($message) {
        $telegram_token = '***';
        $telegram_chatid = '***';

        $ch = curl_init();
        curl_setopt_array(
            $ch,
            array(
                CURLOPT_URL => 'https://api.telegram.org/bot' . $telegram_token . '/sendMessage',
                CURLOPT_POST => TRUE,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_POSTFIELDS => array(
                    'chat_id' => $telegram_chatid,
                    'text' => $message,
                    'parse_mode' => 'html'
                ),
            )
        );
        curl_exec($ch);
    }
    
    /**
     * @param $message
     */
    public static function sendTelegramChat($message, $chat_id) {
        $telegram_token = '***';
        $telegram_chatid = $chat_id;
        $ch = curl_init();
        curl_setopt_array(
            $ch,
            array(
                CURLOPT_URL => 'https://api.telegram.org/bot' . $telegram_token . '/sendMessage',
                CURLOPT_POST => TRUE,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_POSTFIELDS => array(
                    'chat_id' => $telegram_chatid,
                    'text' => $message,
                    'parse_mode' => 'html'
                ),
            )
        );
        curl_exec($ch);
    }

    public static function rdate($param, $time = 0) {
        if (intval($time) == 0) {
            $time = time();
        }
        $MonthNames = array(
            "Января",
            "Февраля",
            "Марта",
            "Апреля",
            "Мая",
            "Июня",
            "Июля",
            "Августа",
            "Сентября",
            "Октября",
            "Ноября",
            "Декабря"
        );
        if (strpos($param,'M') === false) {
            return date($param, $time);
        } else {
            return date(str_replace('M', $MonthNames[date('n', $time) - 1], $param), $time);
        }
    }

    /**
     * @param $complaints
     */
    public static function getTableRender($data, $titles, $values, $cartridge = false) {

        $table['table_title'] = '
            <table class="table table-hover table-title-name">
                <thead>
                    <tr>';

        foreach ($titles as $item) {
            $table['table_title'] .= '<th scope="col">' . $item . '</th>';
        }

        $table['table_title'] .= '
                    </tr>
                </thead>
            </table>
        ';

        $table['table_value'] = '<div class="container complaints-block complaint-tbody" id="style-3">
            <table class="table table-value">
                <tbody>';

        $i = 1;
        foreach ($data as $item) {
            $id = isset($item['id']) ? $item['id'] : $item['cartridge_id'];
            $table['table_value'] .= '<tr tr_id="' . $id . '">';
            foreach ($values as $k => $v) {
                $p[$k] = $v;
                switch ($v) {
                    case '':
                        $class = $i < 3 ? "top" : "";
                        $table['table_value'] .= '<td class="table-indecation ' . $class . '" data-tooltip="' . $item->complaintsStatus->alias . '" data-status="' . $item->complaintsStatus->id . '"><span class="status-' . $item->complaintsStatus->status . '"></span></td>';
                        break;

                    case 'create_at':
                        $time = strtotime($item[$v]);
                        $table['table_value'] .= '<td>' . Helper::rdate("d M - H:i", $time) . '</td>';
                        break;

                    case 'date_finish':
                        $time = strtotime($item[$v]);
                        $table['table_value'] .= '<td>' . Helper::rdate("d M Y", $time) . '</td>';
                        break;

                    case 'increment':
                        $table['table_value'] .= '<td>' . $item['id'] . '</td>';
                        $i++;
                        break;

                    case 'date_action':
                        $time = new DateTime($item[$v]);
                        $now = new DateTime();
                        $interval = self::getPeriod($time, $now);
                        $table['table_value'] .= '<td>' . (!empty($interval) ? $interval : 'Сегодня') . '</td>';
                        break;

                    case 'location_id':
                        $text = !empty($item[$v]) ? $item['location'] . ' ( ' . $item['company'] . ' )' : 'Не указано';
                        $table['table_value'] .= '<td>' . $text . '</td>';
                        break;

                    case 'cartridge_status':
                        $class = $i < 3 ? "top" : "";
                        $alias = '';
                        $status = '';
                        switch ($item['status']) {
                            case 1:
                                $alias = 'Нужно заправить';
                                $status = 'created';
                                break;
                            case 2:
                                $alias = 'Используется';
                                $status = 'progress';
                                break;
                            case 4:
                                $alias = 'Готовый';
                                $status = 'done';
                                break;
                        }
                        $table['table_value'] .= '<td class="table-indecation ' . $class . '" data-tooltip="' . $alias . '" data-status="' . $item['status'] . '"><span class="status-' . $status . '"></span></td>';
                        break;

                    default:
                        if (!$cartridge) {
                            $val = !is_numeric($k) ? (isset($item->{$k}->{$v}) ? $item->{$k}->{$v} : '') : (isset($item->{$v}) ? $item->{$v} : '');
                        }  else {
                            $val = $item[$v];
                        }
                        $table['table_value'] .= '<td>' . $val . '</td>';
                        break;
                }
            }
            $table['table_value'] .= '</tr>';
        }

        $table['table_value'] .= '</tbody>
            </table>
        </div>';

        return $table;
    }

    /**
     * @return string|null
     */
    public static function generatePass() {
        $chars = "qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP";
        $max = 5;
        $size = StrLen($chars) - 1;
        $password = null;

        while ($max--) {
            $password .= $chars[rand(0, $size)];
        }

        return $password;
    }

    public static function checlGuest() {
        if (Yii::$app->user->isGuest) {
            Yii::$app->response->redirect(['login/login']);
        }
    }

    public static function generateRandomPass( $length = 6, $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789' ) {
        return substr( str_shuffle( $chars ), 0, $length );
    }

    public static function getPeriod($date1,$date2){
        $interval = date_diff($date1, $date2);
        $y='';$m='';$d='';

        if ($interval->y>0) {
            if ($interval->y>4)
                $y .=$interval->y . ' лет';
            else if ($interval->y == 1)
                $y .=$interval->y . ' год';
            else
                $y .=$interval->y . ' года';
            $y .= ', ';
        }

        if ($interval->m>0) {
            if ($interval->m>4)
                $m .= $interval->m . ' месяцев';
            else if ($interval->m>1)
                $m .= $interval->m . ' месяца';
            else
                $m .= $interval->m . ' месяц';
            $m .= ', ';
        }

        if ($interval->d>0) {
            if ($interval->d>4)
                $d .= $interval->d . ' дней';
            else if ($interval->d>1)
                $d .= $interval->d . ' дня';
            else
                $d .= $interval->d . ' день';
        }

        return $y . $m . $d;
    }

}
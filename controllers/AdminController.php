<?php

namespace app\controllers;

use app\models\AccountInfo;
use app\models\Cartridge;
use app\models\CartridgeHistory;
use app\models\Company;
use app\models\Complaints;
use app\models\Ecp;
use app\models\Helper;
use app\models\Mfu;
use app\models\NetworkDevice;
use app\models\Phone;
use app\models\Provider;
use app\models\ServerDevice;
use app\models\UploadFileForm;
use app\models\UploadForm;
use app\models\Users;
use app\models\UsersPosition;
use app\models\UsersStatus;
use PDO;
use Yii;
use yii\web\UploadedFile;

class AdminController extends BaseController
{

    public function beforeAction($action) {
        Helper::checlGuest();
        return parent::beforeAction($action);
    }

    public function actionComplaints() {

        $arr = [];

        $sqlll = 'select model, count(*) as count, status from ( SELECT n.*, n.status as cartridge_status, c.model, c.toner FROM cartridge_history n
                                INNER JOIN (
                                SELECT cartridge_id, MAX(date_action) AS date_action
                                FROM cartridge_history GROUP BY cartridge_id
                            ) AS max USING (cartridge_id, date_action)
                            left join cartridge c on c.id = n.cartridge_id ) ss
                            group by model, status';
        $commandll = Yii::$app->db->createCommand($sqlll);
        $result = $commandll->queryAll(PDO::FETCH_ASSOC);

        foreach ($result as $item) {
            if (!isset($arr[$item['model']])) {
                $arr[$item['model']]['empty'] = 0;
                $arr[$item['model']]['use'] = 0;
                $arr[$item['model']]['ready'] = 0;
            }
            switch ($item['status']) {
                case '1':
                    $arr[$item['model']]['empty'] = $item['count'];
                    break;
                case '2':
                    $arr[$item['model']]['use'] = $item['count'];
                    break;
                case '4':
                    $arr[$item['model']]['ready'] = $item['count'];
                    break;
            }
        }

        $send = false;
        $text = 'ВНИМАНИЕ! ВНИМАНИЕ! Замечена нехватка катриджа: ' . PHP_EOL;
        foreach ( $arr as $k => $v ) {
            if ($v['ready'] < 2) {
                $text .= $k . ' => ' . $v['ready'] . PHP_EOL;
                $send = true;
            }
        }

//        if ($send) {
//            Helper::sendTelegram($text);
//        }

//        array_unshift($arr, ['aaa' => ['sss' => 'sadasdasd']]);
//
//        echo "<pre>";
//        print_r($arr);
//        echo "</pre>";

        $complaints = Complaints::getComplaints();

        foreach ($complaints as $item) {
            $time = strtotime($item['create_at']);
            $times = Helper::rdate("d M - H:i", $time);
            $data[$item->id] = [
                'id'                => $item->id,
                'create_at'         => $times,
                'user_complaint'    => Yii::$app->user->id == $item->user->id,
                'title'             => $item->title,
                'message'           => $item->message,
                'status'            => $item->status,
                'status_alias'      => $item->complaintsStatus->alias,
                'user'              => $item->user->full_name,
                'company_alias'     => !empty($item->user->company_alias) ? $item->user->company_alias : '',
            ];
        }
        $page_params = Complaints::PARAMS_PAGE;
        $page_params['table'] = Helper::getTableRender($complaints, $page_params['title_list'], $page_params['value_list']);

        return $this->render('table_maket', compact(
            'page_params',
            'data'
        ));
    }

    public function actionUsers() {

        $users = Users::getUsers();

        $page_params = Users::PARAMS_PAGE;
        $page_params['company'] = Company::find()->select(['id', 'name'])->orderBy('name')->all();
        $page_params['position'] = UsersPosition::find()->select(['id', 'name'])->orderBy('name')->all();
        $page_params['status'] = UsersStatus::find()->select(['id', 'alias'])->orderBy('alias')->all();

        $page_params['table'] = Helper::getTableRender($users, $page_params['title_list'], $page_params['value_list']);

        return $this->render('table_maket', compact(
            'page_params'
        ));
    }

    public function actionInventory() {

//        return $this->render('nventory');
        return $this->redirect('site/error');
    }

    public function actionMfu() {

        $mfu = Mfu::find()->with(['user'])->orderBy('model')->where(['delete' => false])->all();

        $page_params = Mfu::PARAMS_PAGE;
        $page_params['users'] = Users::find()->select(['id', 'full_name'])->orderBy('full_name')->all();
        $page_params['table'] = Helper::getTableRender($mfu, $page_params['title_list'], $page_params['value_list']);

        return $this->render('table_maket', compact(
            'page_params'
        ));
    }

    public function actionCompany() {

        $company = Company::find()->orderBy('name')->where(['delete' => false])->all();

        $page_params = Company::PARAMS_PAGE;

        $page_params['table'] = Helper::getTableRender($company, $page_params['title_list'], $page_params['value_list']);

        return $this->render('table_maket', compact(
            'page_params'
        ));
    }

    public function actionNetwork() {

        $network = NetworkDevice::find()->orderBy('seria')->where(['delete' => false])->all();

        $page_params = NetworkDevice::PARAMS_PAGE;

        $page_params['table'] = Helper::getTableRender($network, $page_params['title_list'], $page_params['value_list']);

        return $this->render('table_maket', compact(
            'page_params'
        ));
    }

    public function actionServer() {

        $network = ServerDevice::find()->orderBy('host_name')->where(['delete' => false])->all();

        $page_params = ServerDevice::PARAMS_PAGE;

        $page_params['table'] = Helper::getTableRender($network, $page_params['title_list'], $page_params['value_list']);

        return $this->render('table_maket', compact(
            'page_params'
        ));
    }

    public function actionAccount() {

        $account = AccountInfo::find()->with('user')->orderBy('comp_name')->where(['delete' => false])->all();

        $page_params = AccountInfo::PARAMS_PAGE;
        $page_params['users'] = Users::find()->leftJoin('account_info', 'account_info.user_id = users.id')->where(['account_info.user_id' => null])->orWhere(['account_info.delete' => '1'])->select(['users.id', 'users.full_name'])->orderBy('users.full_name')->all();

        $page_params['table'] = Helper::getTableRender($account, $page_params['title_list'], $page_params['value_list']);

        return $this->render('table_maket', compact(
            'page_params',
        ));

        return $this->render('maket_table');
    }

    public function actionEcp() {

        $ecp = Ecp::find()->with(['location', 'user', 'company'])->where(['>=', 'date_finish', date('Y-m-d H:i:s')])->andWhere(['delete' => false])->orderBy('date_finish')->all();

        $page_params = Ecp::PARAMS_PAGE;
        $page_params['users'] = Users::find()->select(['id', 'full_name'])->where(['delete' => false])->orderBy('full_name')->all();
        $page_params['company'] = Company::find()->select(['id', 'name'])->where(['delete' => false])->orderBy('name')->all();

        $page_params['table'] = Helper::getTableRender($ecp, $page_params['title_list'], $page_params['value_list']);

        return $this->render('table_maket', compact(
            'page_params',
        ));
    }

    public function actionProvider() {

        $provider = Provider::find()->with(['company'])->orderBy('name')->where(['delete' => false])->all();

        $page_params = Provider::PARAMS_PAGE;
        $page_params['company'] = Company::find()->select(['id', 'name'])->where(['delete' => false])->orderBy('name')->all();

        $page_params['table'] = Helper::getTableRender($provider, $page_params['title_list'], $page_params['value_list']);

        return $this->render('table_maket', compact(
            'page_params',
        ));
    }

    public function actionPhone() {

        $phone = Phone::find()->where(['delete' => false])->all();

        $page_params = Phone::PARAMS_PAGE;

        $page_params['table'] = Helper::getTableRender($phone, $page_params['title_list'], $page_params['value_list']);

        return $this->render('table_maket', compact(
            'page_params',
        ));
    }

    public function actionSetting() {

//        return $this->render('setting');
        return $this->redirect('site/error');
    }

    public function actionFiles() {

        $model = new UploadForm();

        if (Yii::$app->request->isPost) {
            $model->file = UploadedFile::getInstance($model, 'file');
            if ($model->upload()) {
                Yii::$app->session->setFlash('success', 'Изображение загружено');
                return $this->refresh();
            }
        }

//        return $this->render('files', compact('model'));
        return $this->redirect('site/error');
    }

    public function actionCartridge() {

        $sql = 'SELECT n.*, n.status as cartridge_status, c.*, cl.* FROM cartridge_history n
            INNER JOIN (
            SELECT cartridge_id, MAX(date_action) AS date_action
                    FROM cartridge_history GROUP BY cartridge_id
                ) AS max USING (cartridge_id, date_action)
            left join cartridge c on c.id = n.cartridge_id
            left join cartridge_location cl on cl.id = n.location_id';
        $command = Yii::$app->db->createCommand($sql);
        $cartridge = $command->queryAll(PDO::FETCH_ASSOC);


        $page_params = CartridgeHistory::PARAMS_PAGE;
        $page_params['table'] = Helper::getTableRender($cartridge, $page_params['title_list'], $page_params['value_list'], true);

        return $this->render('table_maket', compact(
            'page_params'
        ));
    }

    public function actionApi() {

        $file = 'text.txt';

        $text = file_get_contents($file);

        return $this->render('text', compact('text'));
    }

}

<?php

namespace app\controllers;

use app\models\AccountInfo;
use app\models\Cartridge;
use app\models\CartridgeHistory;
use app\models\Comments;
use app\models\Company;
use app\models\Complaints;
use app\models\Ecp;
use app\models\Helper;
use app\models\Mfu;
use app\models\NetworkDevice;
use app\models\Phone;
use app\models\Provider;
use app\models\ServerDevice;
use app\models\SignupForm;
use app\models\Users;
use MongoDB\Driver\Server;
use Symfony\Component\Console\Helper\ProgressBar;
use Yii;
use yii\db\StaleObjectException;
use yii\web\Controller;
use yii\web\User;

class AjaxController extends Controller
{

    public function actionChangeStatus() {
        $request = Yii::$app->request;
        if ($request->post()) {
            $complaints = Complaints::findOne($request->post('complaints_id'));
            $complaints->status = $request->post('new_status');
            try {
                $complaints->update();
            } catch (StaleObjectException $e) {
            } catch (\Throwable $e) {
            }
        }
    }

    public function actionWorker() {
        $request = Yii::$app->request;
        if ($request->post()) {
            $complaints = Complaints::findOne($request->post('complaints_id'));
            $complaints->worker_id = Yii::$app->user->id;
            try {
                $complaints->update();
                $fio = Yii::$app->user->identity->second_name . " " . Yii::$app->user->identity->first_name . " " . Yii::$app->user->identity->patronymic;
                return $fio;
            } catch (StaleObjectException $e) {
            } catch (\Throwable $e) {
            }
        }
    }

    public function actionComment() {
        $request = Yii::$app->request;
        if ($request->post()) {
            $date = date('Y-m-d H:i:s');
            $comment = new Comments();
            $comment->comment = $request->post('comment');
            $comment->user_id = Yii::$app->user->id;
            $comment->create_at = $date;
            $comment->insert();
            $time = strtotime($date);

            $data = [
                'fio'       => Yii::$app->user->identity->second_name . " " . Yii::$app->user->identity->first_name . " " . Yii::$app->user->identity->patronymic,
                'create_at' => Helper::rdate("d M - H:i", $time)
            ];
            return json_encode($data);
        }
    }

    public function actionGetNewComplaints() {

        $request = Yii::$app->request;
        if ($request->post()) {
            $new_complaints = Complaints::getComplaints();
            foreach ($new_complaints as $item) {
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
                    'status_name'       => $item->complaintsStatus->status,
                    'user'              => $item->user->full_name,
                    'company_alias'     => $item->user->company_alias,
                    'worker'            => ''
                ];
                if (!empty($item->worker)) {
                    $data[$item->id]['worker'] = $item->worker->full_name;
                }
            }
            $new = array_diff_key ($data, $request->post('old_complaints'));
            return json_encode($new);
        }
        return 0;

    }

    public function actionAddUser() {

        $request = Yii::$app->request;

        if ($request->post()) {
            $send_email = new SignupForm();
            $new_pass = Helper::generateRandomPass();
            $user = new Users($request->post());
            $user->full_name = $request->post('second_name') . ' ' . $request->post('first_name') . ' ' . $request->post('patronymic');
            $user->create_at = date('Y-m-d H:i:s');
            $user->phone = preg_replace('/[^0-9]/', '', $request->post('phone'));
            $user->active = Users::STATUS_WAIT;
            $user->auth_key = Yii::$app->security->generateRandomString();
            $user->password_hash = Yii::$app->security->generatePasswordHash($new_pass);
            $user->email_confirm_token = Yii::$app->security->generateRandomString();
            $user->save();
            $send_email->sentEmailConfirm($user, $new_pass);
            Yii::$app->session->setFlash('success', 'Пользователь успешно создан. Необходимо зайти на почту ( ' . $user->mail . ' ) и подтвердить регистрацию.');
            return $this->redirect(['admin/users']);
        }

        return 0;

    }

    public function actionDeleteUser() {

        $request = Yii::$app->request;

        if ($request->post()) {
            $user = Users::find()->where(['id' => $request->post('id')])->one();
            $user->delete = true;
            $user->update();

	          $account_user = AccountInfo::find()->where(['user_id' => $request->post('id')])->one();
	          if ($account_user) {
		          $account_user->delete = true;
		          $account_user->update();
	          }
            Yii::$app->session->setFlash('success', 'Пользователь успешно удален');
            return $this->redirect(['admin/users']);
        }

        return 0;
    }

    public function actionWatchUser() {
        $request = Yii::$app->request;

        if ($request->post()) {
            $user_info = Users::find()->where(['id' => $request->post('id')])->with(['company', 'position', 'status', 'accountInfo'])->asArray()->one();
            return json_encode($user_info);
        }

        return 0;
    }

    public function actionEditUser() {

        $request = Yii::$app->request;

        $arr = [
            'second_name',
            'first_name',
            'patronymic',
            'phone',
            'company_id',
            'position_id',
            'status_id',
            'mail',
        ];

        if ($request->post()) {

            $user = Users::find()->where(['id' => $request->post('id')])->one();

            foreach ($arr as $attr) {
                if ($user->{$attr} != $request->post($attr)) {
                    $user->{$attr} = $request->post($attr);
                }
            }
            $user->full_name = $user->second_name . ' ' . $user->first_name . ' ' . $user->patronymic;

            $user->save();
            $user_info = Users::find()->where(['id' => $request->post('id')])->with(['company', 'position', 'status', 'accountInfo'])->asArray()->one();
            return json_encode($user_info);
        }
        return 0;
    }


    public function actionAddCompany() {

        $request = Yii::$app->request;

        if ($request->post()) {
            $company = new Company($request->post());
            $company->create_at = date('Y-m-d H:i:s');
            $company->save();
            Yii::$app->session->setFlash('success', 'Организация успешно добавлена');
            return $this->redirect(['admin/company']);
        }

        return 0;
    }

    public function actionDeleteCompany() {

        $request = Yii::$app->request;

        if ($request->post()) {
            $company = Company::find()->where(['id' => $request->post('id')])->one();
            $company->delete = true;
            $company->save();
            Yii::$app->session->setFlash('success', 'Организация успешно удалена');
            return $this->redirect(['admin/company']);
        }

        return 0;
    }

    public function actionEditCompany() {

        $request = Yii::$app->request;

        $arr = [
            'name',
            'address',
            'directory',
            'INN',
        ];

        if ($request->post()) {

            $company = Company::find()->where(['id' => $request->post('id')])->one();

            foreach ($arr as $attr) {
                if ($company->{$attr} != $request->post($attr)) {
                    $company->{$attr} = $request->post($attr);
                }
            }

            $company->save();
            $company_info = Company::find()->where(['id' => $request->post('id')])->asArray()->one();
            return json_encode($company_info);
        }
        return 0;
    }

    public function actionWatchCompany() {

        $request = Yii::$app->request;

        if ($request->post()) {
            $company_info = Company::find()->where(['id' => $request->post('id')])->asArray()->one();
            return json_encode($company_info);
        }

        return 0;
    }


    public function actionAddEcp() {

        $request = Yii::$app->request;
        if ($request->post()) {
            $ecp = new Ecp($request->post());
            $ecp->save();
            Yii::$app->session->setFlash('success', 'ЭЦП успешно добавлена');
            return $this->redirect(['admin/ecp']);
        }

        return 0;
    }

    public function actionDeleteEcp() {

        $request = Yii::$app->request;

        if ($request->post()) {
            $ecp = Ecp::find()->where(['id' => $request->post('id')])->one();
            $ecp->delete = true;
            $ecp->notify = false;
            $ecp->save();
            Yii::$app->session->setFlash('success', 'ЭЦП успешно удалена');
            return $this->redirect(['admin/ecp']);
        }

        return 0;
    }

    public function actionEditEcp() {

        $request = Yii::$app->request;

        $arr = [
            'seria',
            'user_id',
            'date_start',
            'date_finish',
            'verification_center',
            'type',
            'location_id',
            'where',
            'company_id',
            'status',
        ];

        if ($request->post()) {

            $ecp = Ecp::find()->where(['id' => $request->post('id')])->one();

            foreach ($arr as $attr) {
                if ($ecp->{$attr} != $request->post($attr)) {
                    $ecp->{$attr} = $request->post($attr);
                }
            }

            $ecp->save();
            $ecp_info = Ecp::find()->where(['id' => $request->post('id')])->with(['location', 'user', 'company'])->asArray()->one();
            $time = strtotime($ecp_info['date_start']);
            $ecp_info['date_start_alias'] = Helper::rdate("d M Y", $time);
            $ecp_info['date_start'] = date('Y-m-d', $time);
            $time = strtotime($ecp_info['date_finish']);
            $ecp_info['date_finish_alias'] = Helper::rdate("d M Y", $time);
            $ecp_info['date_finish'] = date('Y-m-d', $time);
            return json_encode($ecp_info);
        }
        return 0;
    }

    public function actionWatchEcp() {

        $request = Yii::$app->request;

        if ($request->post()) {
            $ecp_info = Ecp::find()->where(['id' => $request->post('id')])->with(['location', 'user', 'company'])->asArray()->one();

            $time = strtotime($ecp_info['date_start']);
            $ecp_info['date_start_alias'] = Helper::rdate("d M Y", $time);
            $ecp_info['date_start'] = date('Y-m-d', $time);
            $time = strtotime($ecp_info['date_finish']);
            $ecp_info['date_finish_alias'] = Helper::rdate("d M Y", $time);
            $ecp_info['date_finish'] = date('Y-m-d', $time);
            return json_encode($ecp_info);
        }

        return 0;
    }

    public function actionAddMfu() {

        $request = Yii::$app->request;

        if ($request->post()) {
            $mfu = new Mfu($request->post());
            $mfu->save();
            Yii::$app->session->setFlash('success', 'МФУ успешно добавлена');
            return $this->redirect(['admin/mfu']);
        }

        return 0;
    }

    public function actionDeleteMfu() {

        $request = Yii::$app->request;

        if ($request->post()) {
            $mfu = Mfu::find()->where(['id' => $request->post('id')])->one();
            $mfu->delete = true;
            $mfu->save();
            Yii::$app->session->setFlash('success', 'МФУ успешно удалена');
            return $this->redirect(['admin/mfu']);
        }

        return 0;
    }

    public function actionWatchMfu() {
        $request = Yii::$app->request;

        if ($request->post()) {
            $mfu_info = Mfu::find()->where(['id' => $request->post('id')])->with('user')->asArray()->one();
            return json_encode($mfu_info);
        }

        return 0;
    }

    public function actionEditMfu() {

        $request = Yii::$app->request;

        $arr = [
            'location',
            'model',
            'host_name',
            'IP_address',
            'user_id',
        ];

        if ($request->post()) {
            $mfu = Mfu::find()->where(['id' => $request->post('id')])->one();
            foreach ($arr as $attr) {
                if ($mfu->{$attr} != $request->post($attr)) {
                    $mfu->{$attr} = $request->post($attr);
                }
            }
            $mfu->save();
            $mfu_info = Mfu::find()->where(['id' => $request->post('id')])->with('user')->asArray()->one();
            return json_encode($mfu_info);
        }

        return 0;
    }

    public function actionAddNetwork() {

        $request = Yii::$app->request;

        if ($request->post()) {
            $network = new NetworkDevice($request->post());
            $network->save();
            Yii::$app->session->setFlash('success', 'Сетевое оборудование успешно добавлено');
            return $this->redirect(['admin/network']);
        }

        return 0;
    }

    public function actionDeleteNetwork() {

        $request = Yii::$app->request;

        if ($request->post()) {
            $network = NetworkDevice::find()->where(['id' => $request->post('id')])->one();
            $network->delete = true;
            $network->save();
            Yii::$app->session->setFlash('success', 'Сетевое оборудование успешно удалено');
            return $this->redirect(['admin/network']);
        }

        return 0;
    }

    public function actionWatchNetwork() {
        $request = Yii::$app->request;

        if ($request->post()) {
            $network_info = NetworkDevice::find()->where(['id' => $request->post('id')])->asArray()->one();
            return json_encode($network_info);
        }

        return 0;
    }

    public function actionEditNetwork() {

        $request = Yii::$app->request;

        $arr = [
            'model',
            'IP_address',
            'seria',
            'location',
        ];

        if ($request->post()) {
            $network = NetworkDevice::find()->where(['id' => $request->post('id')])->one();
            foreach ($arr as $attr) {
                if ($network->{$attr} != $request->post($attr)) {
                    $network->{$attr} = $request->post($attr);
                }
            }
            $network->save();
            $network_info = NetworkDevice::find()->where(['id' => $request->post('id')])->asArray()->one();
            return json_encode($network_info);
        }

        return 0;
    }

    public function actionAddServer() {

        $request = Yii::$app->request;

        if ($request->post()) {
            $server = new ServerDevice($request->post());
            $server->save();
            Yii::$app->session->setFlash('success', 'Серверное оборудование успешно добавлено');
            return $this->redirect(['admin/server']);
        }

        return 0;
    }

    public function actionDeleteServer() {

        $request = Yii::$app->request;

        if ($request->post()) {
            $server = ServerDevice::find()->where(['id' => $request->post('id')])->one();
            $server->delete = true;
            $server->save();
            Yii::$app->session->setFlash('success', 'Серверное оборудование успешно удалено');
            return $this->redirect(['admin/server']);
        }

        return 0;
    }

    public function actionWatchServer() {
        $request = Yii::$app->request;

        if ($request->post()) {
            $server_info = ServerDevice::find()->where(['id' => $request->post('id')])->asArray()->one();
            return json_encode($server_info);
        }

        return 0;
    }

    public function actionEditServer() {

        $request = Yii::$app->request;

        $arr = [
            'model',
            'IP_address',
            'host_name',
            'description',
        ];

        if ($request->post()) {
            $server = ServerDevice::find()->where(['id' => $request->post('id')])->one();
            foreach ($arr as $attr) {
                if ($server->{$attr} != $request->post($attr)) {
                    $server->{$attr} = $request->post($attr);
                }
            }
            $server->save();
            $server_info = ServerDevice::find()->where(['id' => $request->post('id')])->asArray()->one();
            return json_encode($server_info);
        }

        return 0;
    }

    public function actionAddAccount() {

        $request = Yii::$app->request;

        if ($request->post()) {
            $account = new AccountInfo($request->post());
            $account->save();
            Yii::$app->session->setFlash('success', 'Информация об учетной записи успешно добавлена');
            return $this->redirect(['admin/account']);
        }

        return 0;
    }

    public function actionDeleteAccount() {

        $request = Yii::$app->request;

        if ($request->post()) {
            $account = AccountInfo::find()->where(['id' => $request->post('id')])->one();
            $account->delete = true;
            $account->save();
            Yii::$app->session->setFlash('success', 'Информация об учетной записи успешно удалена');
            return $this->redirect(['admin/account']);
        }

        return 0;
    }

    public function actionWatchAccount() {
        $request = Yii::$app->request;

        if ($request->post()) {
            $account_info = AccountInfo::find()->where(['id' => $request->post('id')])->with('user')->asArray()->one();
            return json_encode($account_info);
        }

        return 0;
    }

    public function actionEditAccount() {

        $request = Yii::$app->request;

        $arr = [
            'user_id',
            'comp_name',
            'domen',
            'lk_mail',
            'lk_login',
            'lk_pass',
            'vpn_login',
            'vpn_pass',
            'vpn_ip',
            'vpn_second_pass',
        ];

        if ($request->post()) {
            $account = AccountInfo::find()->where(['id' => $request->post('id')])->one();
            foreach ($arr as $attr) {
                if ($account->{$attr} != $request->post($attr)) {
                    $account->{$attr} = $request->post($attr);
                }
            }
            $account->save();
            $account_info = AccountInfo::find()->where(['id' => $request->post('id')])->with('user')->asArray()->one();
            return json_encode($account_info);
        }

        return 0;
    }

    public function actionAddProvider() {

        $request = Yii::$app->request;

        if ($request->post()) {
            $provider = new Provider($request->post());
            $provider->save();
            Yii::$app->session->setFlash('success', 'Провайдер успешно добавлен');
            return $this->redirect(['admin/provider']);
        }

        return 0;
    }

    public function actionDeleteProvider() {

        $request = Yii::$app->request;

        if ($request->post()) {
            $provider = Provider::find()->where(['id' => $request->post('id')])->one();
            $provider->delete = true;
            $provider->save();
            Yii::$app->session->setFlash('success', 'Провайдер успешно удален');
            return $this->redirect(['admin/provider']);
        }

        return 0;
    }

    public function actionWatchProvider() {
    $request = Yii::$app->request;

    if ($request->post()) {
        $provider_info = Provider::find()->where(['id' => $request->post('id')])->with('company')->asArray()->one();
        return json_encode($provider_info);
    }

    return 0;
}

    public function actionEditProvider() {

        $request = Yii::$app->request;

        $arr = [
            'name',
            'contract_num',
            'company_id',
            'login',
            'password',
            'cost',
            'manager_phone',
        ];

        if ($request->post()) {
            $provider = Provider::find()->where(['id' => $request->post('id')])->one();
            foreach ($arr as $attr) {
                if ($provider->{$attr} != $request->post($attr)) {
                    $provider->{$attr} = $request->post($attr);
                }
            }
            $provider->save();
            $provider_info = Provider::find()->where(['id' => $request->post('id')])->with('company')->asArray()->one();
            return json_encode($provider_info);
        }

        return 0;
    }

    public function actionAddPhone() {

        $request = Yii::$app->request;

        if ($request->post()) {
            $phone = new Phone($request->post());
            $phone->save();
            Yii::$app->session->setFlash('success', 'Телефонная станция успешно добавлена');
            return $this->redirect(['admin/phone']);
        }

        return 0;
    }

    public function actionDeletePhone() {

        $request = Yii::$app->request;

        if ($request->post()) {
            $phone = Phone::find()->where(['id' => $request->post('id')])->one();
            $phone->delete = true;
            $phone->save();
            Yii::$app->session->setFlash('success', 'Телефонная станция успешно удалена');
            return $this->redirect(['admin/phone']);
        }

        return 0;
    }

    public function actionWatchPhone() {
        $request = Yii::$app->request;

        if ($request->post()) {
            $phone_info = Phone::find()->where(['id' => $request->post('id')])->asArray()->one();
            return json_encode($phone_info);
        }

        return 0;
    }
    public function actionEditPhone() {

        $request = Yii::$app->request;

        $arr = [
            'interior_num',
            'external_num',
            'IP_address',
            'location',
        ];

        if ($request->post()) {
            $phone = Phone::find()->where(['id' => $request->post('id')])->one();
            foreach ($arr as $attr) {
                if ($phone->{$attr} != $request->post($attr)) {
                    $phone->{$attr} = $request->post($attr);
                }
            }
            $phone->save();
            $phone_info = Phone::find()->where(['id' => $request->post('id')])->asArray()->one();
            return json_encode($phone_info);
        }

        return 0;
    }

    public function actionAddCartridge() {

        $request = Yii::$app->request;
        if ($request->post('id') && $request->post('model') && $request->post('toner')) {
            $new_cartridge = new Cartridge($request->post());
            $new_cartridge->save();

            $cartridge_history = new CartridgeHistory();
            $cartridge_history->cartridge_id = $new_cartridge->id;
            $cartridge_history->status = 4;
            $cartridge_history->date_action = date('Y-m-d H:i:s');
            $cartridge_history->save();

            Yii::$app->session->setFlash('success', 'Картридж успешно добавлен');
            return $this->redirect(['admin/cartridge']);
        }

        return 0;

    }

    public function actionResetPass() {
        $request = Yii::$app->request;

        if ($request->post()) {
            $send_email = new SignupForm();
            $user = Users::findByMail($request->post('mail'));
            $new_pass = Helper::generateRandomPass();
            $user->password_reset_token = Yii::$app->security->generateRandomString();
            $user->save();
            $send_email->sentEmailResetPass($user, $new_pass);
            Yii::$app->session->setFlash('success', 'Перейдите на почту ( ' . $user->mail . ' ), чтобы сбросить ваш пароль.');
            return $this->redirect(['login/login']);
        }
    }

    public function actionResPas() {
        $request = Yii::$app->request;

        if ($request->post()) {
            $user = Users::findOne(['id' => $request->post('user_id')]);
            $user->password_hash = Yii::$app->security->generatePasswordHash($request->post('new_pass'));
            $user->password_reset_token = null;
            $user->save();
            Yii::$app->session->setFlash('success', 'Ваш пароль успешно обновлен.');
            return $this->redirect(['login/login']);
        }
    }

    public function actionLoadFile() {
        if( isset( $_POST['my_file_upload'] ) ){
            // ВАЖНО! тут должны быть все проверки безопасности передавемых файлов и вывести ошибки если нужно

            $uploaddir = '/web/images/uploads/'; // . - текущая папка где находится submit.php

            // cоздадим папку если её нет
            if( ! is_dir( $uploaddir ) ) mkdir( $uploaddir, 0777 );

            $files      = $_FILES; // полученные файлы
            $done_files = array();

            // переместим файлы из временной директории в указанную
            foreach( $files as $file ){
                $file_name = $file['name'];

                if( move_uploaded_file( $file['tmp_name'], "$uploaddir/$file_name" ) ){
                    $done_files[] = realpath( "$uploaddir/$file_name" );
                }
            }

            $data = $done_files ? array('files' => $done_files ) : array('error' => 'Ошибка загрузки файлов.');

            die( json_encode( $data ) );
        }

        return json_encode($_FILES);
    }

}
<?php

namespace leegoway\uic\filters;

use Yii;
use yii\base\ActionFilter;

class UicAuthFilter extends ActionFilter
{
	/* 
	 * @var string 登录地址
	 */
	protected $homeUrl = 'http://home.corp.elong.com';

	/*
	 * @var string 用户中心地址
	 */
	protected $uicUrl = 'http://uic.corp.elong.com';

     /**
     * @var int 用户未登录的AJAX错误码
     */
    private $noLoginCode = 401;

    /**
     * @var string 用户中心验证 ticket 地址
     */
    private $decodeTicket;

    /**
     * @var string 用户中心根据 token 获取 ticket 地址
     */
    private $takeTicket;

    /**
     * @var string http客户端，用户请求用户中心 
     */
    private $httpClient;

	public function __construct($config=[]) {
		parent::__construct($config);

		if (isset(Yii::$app->params['uicUrl'])) {
            $this->uicUrl = Yii::$app->params['uicUrl'];
        }
        if (isset(Yii::$app->params['homeUrl'])) {
            $this->homeUrl = Yii::$app->params['homeUrl'];
        }
        if (isset(Yii::$app->params['notLoginErrorCode'])) {
            $this->noLoginCode = Yii::$app->params['noLoginCode'];
        }
        $this->httpClient = new Client();
        $this->homeLogin  = $this->homeUrl . '/home/Site/Index?subsystem=' . Yii::$app->name;
        $this->takeTicket = $this->uicUrl . '/ucenter/api/User/Ticket';
        $this->decodeTicket = $this->uicUrl . '/ucenter/api/User/decodeTicket';
        Yii::info('Uic Auth Filter Constructed: ' . $this->uicUrl, 'AUTH');
	}

	 public function beforeAction($action) {
        if (Yii::$app->uicAuther->user() === null) {
            if (!$this->loginViaUic()) {
                Yii::info('Login Via Uic Failed', 'AUTH');
                if (Yii::$app->request->isAjax) {
                    echo json_encode(AjaxHelper::ajaxErrorJSON($this->noLoginCode));
                    return false;
                }
                Yii::$app->response->redirect($this->homeLogin)->send();
                return false;
            } else {
                Yii::info('Login Via Uic Success', 'AUTH');
            }
        }
        return parent::beforeAction($action);
    }


    public function loginViaUic() {
        $ticket = Yii::$app->request->get('ticket');
        if ($ticket && $this->loginViaTicket($ticket)) {
            return true;
        }
        $utoken = isset($_COOKIE['utoken']) ? $_COOKIE['utoken'] : null;
        if ($utoken && $this->loginViaToken($utoken)) {
            return true;
        }
        return false;
    }


    /**
     * 通过票据登录系统
     * @param $ticket
     * @return bool
     * @throws \yii\base\InvalidConfigException
     */
    public function loginViaTicket($ticket) {
        Yii::info('Logging in Via Ticket: ' . $ticket, "AUTH");
        $data = [
            "ticket" => $ticket,
            "subsystem" => Yii::$app->name
        ];
        $response = $this->httpClient->request('POST', $this->decodeTicket, ['form_params' => $data]);
        if ($response->getStatusCode() == 200) {
            if (($result = json_decode($response->getBody())) && $result->code == 200) {
                return $this->login($result->data);
            } else {
                Yii::info('Decode Ticket Error: ' . $response->getBody(), "AUTH");
            }
        }
        return false;
    }

    /**
     * 通过 Token 登录系统
     * @param $token
     * @return bool
     * @throws \yii\base\InvalidConfigException
     */
    public function loginViaToken($token) {
        Yii::info('Logging in Via Token: ' . $token, "AUTH");
        $data = [
            "token" => $token,
            "subsystem" => Yii::$app->name
        ];
        $response = $this->httpClient->request('POST', $this->takeTicket, ['form_params'=>$data]);
        if ($response->getStatusCode() == 200) {
            if (($result = json_decode($response->getBody())) && $result->code == 200) {
                return $this->loginViaTicket($result->data->ticket);
            } else {
                Yii::info('Take Ticket with token Error: ' . $response->getBody(), 'AUTH');
            }
        }
        return false;
    }

    public function login($userData){
        $loginDuration = isset(Yii::$app->params['loginDuration']) ? Yii::$app->params['loginDuration'] : 7 * 24 * 60 * 60;
        return Yii::$app->uicAuther->login($userData->username);
    }

    //todo 调用uic退出
    public function logout() {
        if (Yii::$app->uicAuther->user() === null) return true;
        setcookie('utoken', null, 0, '/', 'elong.com');
        return Yii::$app->uicAuther->logout();
    }
}

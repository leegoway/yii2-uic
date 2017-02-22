<?php

namespace leegoway\uic;

use Yii;
use yii\base\Component;
use yii\web\Cookie;
use leegoway\rest\RestException;
use GuzzleHttp\Client;

class Auther extends Component 
{
	public $domain = 'autops.corp.elong.com';
	public $path = '/';
	public $expire = 3600;

	/** 
	 * 登录，注册cookie
	 */
	public function login($user) {
		$username = PassCookie::passC($user->username);
		$cookies = Yii::$app->response->cookies;
		$res = $cookies->add(new Cookie([
			'name' => Yii::$app->name,
			'value' => $username,
			'domain' => $this->domain,
			'path' => $this->path,
			'expire' => time() + $this->expire,
			'httpOnly' => false
			]));
		$id = PassCookie::passC($user->id);
		$cookies->add(new Cookie([
			'name' => Yii::$app->name . '_i',
			'value' => $id,
			'domain' => $this->domain,
			'path' => $this->path,
			'expire' => time() + $this->expire,
			'httpOnly' => false
			]));
		return true;
	}

	/** 
	 * 退出登录，注销cookie
	 */
	public function logout() {
		$cookies = Yii::$app->request->cookies;
		$cookies->remove(Yii::$app->name);
		$cookies->remove(Yii::$app->name . '_i');
		return true;
	}

	/** 
	 * 获取当前用户
	 */
	public function user() {
		$username = null;
		$cookies = Yii::$app->request->cookies;
		if($cookies->has(Yii::$app->name)) {
			$value = $cookies[Yii::$app->name]->value;
			$username = PassCookie::passC($value, 'DECODE');
		}
		return $username;
	}

	/** 
	 * 获取当前用户
	 */
	public function userId() {
		$userid = null;
		$cookies = Yii::$app->request->cookies;
		if($cookies->has(Yii::$app->name . '_i')) {
			$value = $cookies[Yii::$app->name . '_i']->value;
			$userid = PassCookie::passC($value, 'DECODE');
		}
		return $userid;
	}

	/**
	 * 校验权限
	 */
	public function checkPermission($permissionId, $organizationId, $username = null) {
		$result = false;
		if(empty($username)){
			$username = $this->user();
		}
		if(empty($username)){
			throw new RestException('获取用户信息失败，请先登录', 401);
		}

		$uicUrl = 'http://uic.corp.elong.com';
		if (isset(Yii::$app->params['uicUrl'])) {
            $uicUrl = Yii::$app->params['uicUrl'];
        }
        $url = $uicUrl . '/ucenter/api/auth?organizationId=' . $organizationId . '&permissionId=' . $permissionId . '&username=' . $username;
        $httpClient = new Client();
        $response = $httpClient->request('GET', $url);
        if ($response->getStatusCode() == 200) {
            if (($res = json_decode($response->getBody())) && $res->code == 200) {
                Yii::info('GET UIC_CheckPerm  OK: time=' . date('Y-m-d H:i:s') , "UIC");
                $result = $res->data;
            } else {
                Yii::info('GET UIC_CheckPerm Error: time=' . date('Y-m-d H:i:s') . ',msg='. $response->getBody(), "UIC");
            }
        }else{
            Yii::info('GET UIC_CheckPerm Error: httpCode=' . $response->getStatusCode(), 'UIC');
        }
        return $result;	
	}


}

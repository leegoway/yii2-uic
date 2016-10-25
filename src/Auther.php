<?php

namespace leegoway\uic;

use Yii;
use yii\helpers\Component;

class Auther extends Component 
{
	protected $domain = 'autops.corp.elong.com';
	protected $path = '/';
	protected $expire = 3600;

	/** 
	 * 登录，注册cookie
	 */
	public function login($username) {
		$value = PassCookie::passC($username);
		$cookies = Yii::$app->response->cookies;
		$cookies->add(new yii\web\Cookie([
			'name' => Yii::$app->name,
			'value' => $value,
			'domain' => $this->domain,
			'path' => $this->path,
			'expire' => $this->expire
			]));
	}

	/** 
	 * 退出登录，注销cookie
	 */
	public function logout() {
		$cookies = Yii::$app->response->cookies;
		$cookies->remove(Yii::$app->name);
	}

	/** 
	 * 获取当前用户
	 */
	public function user() {
		$username = null;
		$cookies = Yii::$app->response->cookies;
		if($cookies->has(Yii::$app->name)) {
			$value = $cookies[Yii::$app->name]->value;
			$username = PassCookie::passC($value, 'DECODE');
		}
		return $username;
	}

}
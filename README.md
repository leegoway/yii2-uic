# yii2-uic

User center extension for Yii2 framework

包含功能：
* [登录] 接入用户中心的单点登录
* [权限校验] 接入用户中心的权限校验

## Installation
The preferred way to install this extension is through [composer](http://getcomposer.org/download/). Check the [composer.json](https://github.com/daixianceng/yii2-uic/blob/master/composer.json) for this extension's requirements and dependencies.

To install, either run

```
$ php composer.phar require leegoway/yii2-uic "*"
```

or add

```
"leegoway/yii2-uic": "*"
```

to the ```require``` section of your `composer.json` file.

## Usage

```php
return [
    'components' => [
        'uicAuther' => [
            'class' => 'leegoway\uic\Auther',
            'domain' => 'autops.corp.elong.com',//cookie的domain属性
            'path' => '/',//cookie的路径
            'expire' => 7200 //超时时间
        ]
    ],
];
```

then in your controller which need auth, then add the following code:

```php
use leegoway\uic\UicAuthFacade;

...
trait UicAuthFacade;

```

Secondly, you can check permission like the following code:

```php
Yii::$app->uicAuther->checkPermission($permissionId, $organizationId, [$username]); //$username default current login username
```


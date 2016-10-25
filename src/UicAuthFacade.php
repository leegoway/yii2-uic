<?php

namespace leegoway\uic;

use yii;
use leegoway\uic\filters\UicAuthFilter;

trait UicAuthFacade
{
    protected $onlyAuth;
    protected $exceptAuth = [];
    
    public function behaviors() {
        $behaviors = parent::behaviors();
        $behaviors['uicAuth'] = [
            'class'  => UicAuthFilter::className(),
            'except' => $this->exceptAuth,
            'only'   => $this->onlyAuth
        ];
        return $behaviors;
    }
}

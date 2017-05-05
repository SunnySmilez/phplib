<?php

class Controller_Oauthdemo extends \Modules\Wechat\Controllers\Abstraction {

    protected $openid;

    protected function params() {
        return array();
    }

    protected function beforeAction() {
        parent::beforeAction();

        // 注: 用户授权模式获取到的结果为用户完整信息数组
        // $this->openid = (new \Modules\Wechat\Model\Service\Oauth(WECHAT_NAME_DEMO))->auth(\Wechat\Oauth::SCOPE_USERINFO)['openid'];

        // 静默授权获取用户openid
        $this->openid = (new \Modules\Wechat\Model\Service\Oauth(WECHAT_NAME_DEMO))->auth();
    }

    protected function action() {
        $this->response['openid'] = $this->openid;
    }

}
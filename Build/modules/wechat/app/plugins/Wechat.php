<?php

class Plugin_Wechat extends \Base\Plugin\Base {

    public function preDispatch(\Yaf\Request_Abstract $request, \Yaf\Response_Abstract $response) {
        parent::preDispatch($request, $response);

        \Wechat\Model\Service\Message\Register::register();
    }

}
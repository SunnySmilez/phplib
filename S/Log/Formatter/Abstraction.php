<?php
namespace S\Log\Formatter;

abstract class Abstraction {

    protected function getCommon(){
        $request = \Yaf\Application::app()->getDispatcher()->getRequest();
        $common = array(
            'date' => date("Y-m-d H:i:s"),
            'x-rid' => \S\Request::server('x-rid')?:null,
            'server_ip' => \S\Util\Ip::getServerIp(),
            'client_ip' => \S\Util\Ip::getClientIp(),
            'uri'   => \Core\Env::getControllerName(true),
            'params' => json_encode($request->getParams() ?: $_REQUEST),
        );

        $common = array_merge($common, \S\Log\Context::getCommonInfo());
        return $common;
    }

    abstract function format(array $message);

}
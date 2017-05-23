<?php
namespace S\Log\Formatter;

abstract class Abstraction {

    protected function getCommon() {
        if (\Core\Env::isCli()) {
            $uri    = \S\Request::server('argv', array())[1];
            $params = array_slice(\S\Request::server('argv', array()), 2);
        } else {
            $uri    = \Core\Env::getControllerName(true);
            $params = $_REQUEST;
        }

        $common = array(
            'date'      => date("Y-m-d H:i:s"),
            'x-rid'     => \S\Request::server('x-rid') ?: null,
            'server_ip' => \S\Util\Ip::getServerIp(),
            'client_ip' => \S\Util\Ip::getClientIp(),
            'uri'       => $uri,
            'params'    => $params,
        );
        $common = array_merge($common, \S\Log\Context::getCommonInfo());

        return $common;
    }

    abstract function format(array $message);

}
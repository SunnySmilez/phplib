<?php
namespace S\Log\Formatter;

abstract class Abstraction {

    protected function getCommon() {
        if (\Core\Env::isCli()) {
            $module     = \S\Request::server('argv', array())[1];
            $controller = \S\Request::server('argv', array())[2];
            $action     = \S\Request::server('argv', array())[3];

            $uri    = ('Index' == $module ? '' : $module . '_') . $controller . '::' . $action;
            $params = array_slice(\S\Request::server('argv', array()), 4) ?: array();
        } else {
            $uri    = \S\Request::server('PATH_INFO');
            $params = $_REQUEST ?: array();
        }

        $common = array(
            'date'      => date("Y-m-d H:i:s"),
            'x_rid'     => \S\Request::server('x-rid', null),
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
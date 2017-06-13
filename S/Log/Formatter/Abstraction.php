<?php
namespace S\Log\Formatter;

abstract class Abstraction {

    protected function getCommon() {
        if (\Core\Env::isCli()) {
            $uri    = str_replace('\\', '_', trim(\Core\Env::getCliClass(), '\\')) ?: \S\Request::server('argv', array())[1];
            $params = array_slice(\S\Request::server('argv', array()), 2) ?: array();
        } else {
            $uri    = \Core\Env::getControllerName(true);
            $params = $_REQUEST ?: array();
        }

        $common = array(
            'app'       => APP_NAME,
            'date'      => date("Y-m-d H:i:s"),
            'x_rid'     => \S\Request::server('x-rid') ?: null,
            'server_ip' => \S\Util\Ip::getServerIp(),
            'client_ip' => \S\Util\Ip::getClientIp(),
            'uri'       => $uri,
            'params'    => json_encode($params),
            'level'     => strtolower(end(explode("\\", get_class($this)))),
        );
        $common = array_merge($common, \S\Log\Context::getCommonInfo());

        return $common;
    }

    abstract function format(array $message);

}
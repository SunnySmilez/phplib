<?php
namespace S\Log\Formatter;

abstract class Abstraction {

    protected function getCommon() {
        if (\Core\Env::isCli()) {
            $optind   = null;
            $opts     = getopt('c:m:a:', [], $optind);
            $pos_args = array_slice(\S\Request::server('argv', array()), $optind);

            $module     = $opts['m'] ?: 'Index';
            $controller = $opts['c'];
            $action     = $opts['a'] ?: 'Index';

            $params = array();
            foreach ($pos_args as $param_str) {
                list($key, $val) = explode('=', $param_str);
                $params[$key] = $val;
            }

            $uri    = ('Index' == $module ? '' : $module . '_') . $controller . '::' . $action;
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
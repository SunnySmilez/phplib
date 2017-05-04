<?php
namespace S\Log\Handler;


class Rsyslog extends Abstraction {

    public function write($key, $message) {
        $ident = "APP:" . APP_NAME;
        openlog($ident, LOG_ODELAY, LOG_LOCAL7);
        $ret = syslog(LOG_DEBUG, "[:" . $key . ":] | " . $message);
        closelog();

        return $ret;
    }

}
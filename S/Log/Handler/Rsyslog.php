<?php
namespace S\Log\Handler;


class Rsyslog extends Abstraction {

    public function write($level, $message, $to_path) {
        $log_path = $this->getPath($level, $to_path);
        $ident = "LOG_PATH:{$log_path}";
        $message = json_encode($message, JSON_UNESCAPED_UNICODE);

        openlog($ident, LOG_ODELAY, LOG_LOCAL7);
        $ret = syslog(LOG_DEBUG, $message);
        closelog();

        return $ret;
    }

}
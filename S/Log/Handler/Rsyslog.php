<?php
namespace S\Log\Handler;


class Rsyslog extends Abstraction {

    public function write($level, $message, $to_path) {
        $log_path = $this->getPath($level, $to_path);
        $message_array = json_decode($message, true);
        $message_array['log_path'] = $log_path;
        $message = json_encode($message_array);

        openlog("", LOG_ODELAY, LOG_LOCAL7);
        $ret = syslog(LOG_DEBUG, $message);
        closelog();

        return $ret;
    }

}
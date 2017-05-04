<?php
namespace Base\Exception;

class Abstraction extends \Exception {

    protected $type = "";

    public function __construct($message = "", $code = 0, \Exception $previous = null) {
        $user_msg = null;

        if (strpos($message, "error.") === 0) {
            $key = substr($message, 6);
            if (!\S\Config::confError($key)) {
                $msg = $key;
            } else {
                $user_msg = \S\Config::confError($key . '.user_msg');
                $msg      = \S\Config::confError($key . '.msg');
                $code     = \S\Config::confError($key . '.retcode');
            }
        } else {
            $msg = $message;
        }

        parent::__construct($user_msg ?: $msg, $code, $previous);
        $this->setLog($message);
    }

    protected function setLog($msg) {
        $message['exception'] = $this;
        if (strpos($msg, "error.") === 0) {
            $message['exception_self_message'] = \S\Config::confError(substr($msg, 6));
        } else {
            $message['exception_self_message'] = $msg;
        }
        \S\Log\Logger::getInstance()->warning($message);
    }

}
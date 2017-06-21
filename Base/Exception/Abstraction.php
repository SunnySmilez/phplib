<?php
namespace Base\Exception;

class Abstraction extends \Exception {

    protected $type = "";

    public function __construct($exception_msg = "", $code = 0, \Exception $previous = null) {
        //为了支持更好的记录异常信息，扩展message字段为数组,0为模板，1位输入
        if (is_array($exception_msg)) {
            $message_flag = $exception_msg[0];
            $message_args = $exception_msg[1];
        }else{
            $message_flag = $exception_msg;
            $message_args = "";
        }

        $message_data = $this->getMsgData($message_flag);

        $message = sprintf($message_data['msg'], $message_args);
        $code = $message_data['retcode']?:$code;

        parent::__construct($message, $code, $previous);
        $this->setLog();
    }

    protected function setLog() {
        $data['exception'] = $this;
        \S\Log\Logger::getInstance()->warning($data);
    }

    protected function getMsgData($error_flag){
        $msg_data = array();
        if (strpos($error_flag, "error.") === 0) {
            $key = substr($error_flag, 6);
            if (!\S\Config::confError($key)) {
                $msg_data['msg'] = $error_flag;
            } else {
                $msg_data = \S\Config::confError($key);
            }
        } else {
            $msg_data['msg'] = $error_flag;
        }

        return $msg_data;
    }

}
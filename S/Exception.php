<?php
namespace S;

class Exception extends \Exception {

    public function __construct($message = "", $code = 0, \Exception $previous = null){
        parent::__construct($message, $code, $previous);
        //记录日志 作为S\Exception
        $this->setLog();
    }

    protected function setLog(){
        $message['exception'] = $this;
        \S\Log\Logger::getInstance()->error($message);
    }
}
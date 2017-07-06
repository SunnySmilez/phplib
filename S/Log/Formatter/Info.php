<?php
namespace S\Log\Formatter;

class Info extends Abstraction {

    public function format(array $message) {
        /**
         * @var \Yaf\Request_Abstract $request
         */
        $request = \Yaf\Application::app()->getDispatcher()->getRequest();

        $message['exectime'] = round((microtime(1) - APP_START_TIME) * 1000);
        $message['retcode']  = $request->getException() ? $request->getException()->getCode() : \S\Config::confError('common.succ.retcode');
        $message['retmsg']   = $request->getException() ? $request->getException()->getMessage() : \S\Config::confError('common.succ.msg');

        $message         = array_merge($this->getCommon(), $message, \S\Log\Context::getInfo());
        $message['succ'] = (($message['retcode'] == \S\Config::confError('common.succ.retcode')) ? 'succ' : 'fail');

        return json_encode($message, JSON_UNESCAPED_UNICODE);
    }

}
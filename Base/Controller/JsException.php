<?php
namespace Base\Controller;

class JsException extends Common {
    protected $responseFormatter = \S\Response::FORMAT_JSON;

    public function params(){
        return array();
    }

    public function action(){
        $this->write();
    }

    /**
     * js异常写入日志
     *
     * @return string
     */
    private function write(){
        $request_url = strip_tags(\S\Request::server('SERVER_NAME') .':'. \S\Request::server('SERVER_PORT') . \S\Request::server('REQUEST_URI'));//请求地址
        $header_user_agent = strip_tags(\S\Request::header('User-Agent'));//访问信息、机型、系统、浏览器
        $exception_msg = strip_tags(\S\Request::request('msg'));//错误信息，错误信息建议用post方式传输

        \S\Log\Logger::getInstance()->warning(
            array(
            'request_url:' . $request_url,
            'header_user_agent:' . $header_user_agent,
            'exception_msg:' . $exception_msg
        ), 'js.exception');
        return true;
    }
}
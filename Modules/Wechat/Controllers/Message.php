<?php
namespace Modules\Wechat\Controllers;

use Base\Exception\Controller as Exception;

/**
 * Class Message
 *
 * @package     Modules\Wechat\Controllers
 * @description 微信推送消息请求入口
 *              将微信请求分发到各个注册事件上, 没有注册事件返回空值
 */
abstract class Message extends Abstraction {

    protected $response_formatter = \S\Response::FORMAT_PLAIN;
    /**
     * @var \Wechat\Message\Push
     */
    protected $request;

    public function params() {
        return array();
    }

    public function action() {
        $wechat_name = \S\Request::get('wechat_name');
        $config      = \S\Config::confServer("wechat.{$wechat_name}");

        //校验请求串
        $token = $config['token'];
        if (!\Wechat\Message\Push::checkSign($token)) {
            throw new Exception('签名验证失败');
        }

        //微信调用的第一次请求验证
        if ($echostr = \S\Request::get('echostr')) {
            $this->response = $echostr;

            return;
        }

        $this->request = new \Wechat\Message\Push($config);
        \S\Log\Logger::getInstance()->debug(array('request_xml' => $this->request->getRequestXml(), 'request_data' => $this->request->getRequestData()));

        $this->response = \Modules\Wechat\Model\Service\Message\Dispatcher::run($this->request);
        \S\Log\Logger::getInstance()->debug(array('response_msg' => $this->response));
    }

}
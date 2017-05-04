<?php
namespace Modules\Wechat\Model\Service\Message\Handler;

/**
 * Class Base
 *
 * @package     Modules\Wechat\Model\Service\Message\Handler
 * @description 微信请求处理基类
 */
abstract class Base {

    /**
     * @var string 微信公众号名称, 在modules\Wechat\Bootstrap中定义
     */
    protected $wechat_name;
    /**
     * @var \Wechat\Message\Push 微信推送过来的请求消息
     */
    protected $request;
    /**
     * @var string 向微信回复的响应消息
     */
    protected $response;

    public function run(\Wechat\Message\Push $message) {
        $this->wechat_name = \S\Request::get('wechat_name');
        $this->request     = $message;

        $this->beforeAction();
        $this->response = $this->action();
        $this->afterAction();
        register_shutdown_function(array($this, "log"));

        return $this->response;
    }

    protected function beforeAction() {
    }

    protected function afterAction() {
    }

    /**
     * 记录微信消息日志
     *
     * @return bool
     */
    public function log() {
        return (new \Modules\Wechat\Model\Data\Log())->add(
            $this->wechat_name,
            $this->request->FromUserName,
            $this->request->MsgType,
            date("Y-m-d H:i:s", $this->request->CreateTime),
            $this->request->getRequestData()
        );
    }

    /**
     * 消息处理
     * 返回响应消息
     *
     * @return mixed
     */
    abstract public function action();

}
<?php
namespace Base\Plugin;

/**
 * Class Base
 *
 * 触发顺序    名称    触发时机    说明
 * 1    routerStartup    在路由之前触发    这个是7个事件中, 最早的一个. 但是一些全局自定的工作, 还是应该放在Bootstrap中去完成
 * 2    routerShutdown    路由结束之后触发    此时路由一定正确完成, 否则这个事件不会触发
 * 3    dispatchLoopStartup    分发循环开始之前被触发
 * 4    preDispatch    分发之前触发    如果在一个请求处理过程中, 发生了forward, 则这个事件会被触发多次
 * 5    postDispatch    分发结束之后触发    此时动作已经执行结束, 视图也已经渲染完成. 和preDispatch类似, 此事件也可能触发多次
 * 6    dispatchLoopShutdown    分发循环结束之后触发    此时表示所有的业务逻辑都已经运行完成, 但是响应还没有发送
 *
 * @desc 流程插件
 */
class Base extends \Yaf\Plugin_Abstract {

    /**
     * 记录运行日志
     *
     * @param \Yaf\Request_Abstract  $request
     * @param \Yaf\Response_Abstract $response
     *
     * @return bool
     */
    public function dispatchLoopShutdown(\Yaf\Request_Abstract $request, \Yaf\Response_Abstract $response) {
        //记录日志
        \S\Log\Logger::getInstance()->info();
        return true;
    }

}
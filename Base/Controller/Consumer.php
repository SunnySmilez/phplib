<?php
/**
 * 消费者 控制器基类
 * 1.生产者 向队列里发送消息
 * 2.任务队列 redis/mq
 * 3.消费者 消费任务队列里的任务
 *
 * 由外部bin/consumer从任务队列里取数据，可以是监听，获得数据后以fastcgi模式发送给消费者controller消费
 */
abstract class Consumer extends \Yaf\Controller_Abstract {

    /**
     * 限制调用
     * @throws \Yaf\Exception\LoadFailed\Controller
     */
    public function init(){
        //限制请求为本地发送的fastcgi

    }

    public function getParams($key){

    }
}
<?php

namespace S\Mq\Handler;

/**
 * Class MqInterface
 *
 * @package S\Mq
 */
interface MqInterface {

    /**
     * 消息发布
     *
     * @param string $topic  订阅主题
     * @param string $msg    消息
     * @param array  $option 可选参数
     *
     * @return mixed
     */
    public function pub($topic, $msg, array $option = array());

    /**
     * 消息订阅
     *
     * @param string $topic  订阅主题
     * @param array  $option 可选参数
     *
     * @return mixed
     */
    public function sub($topic, array $option = array());

}
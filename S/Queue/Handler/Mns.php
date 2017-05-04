<?php
namespace S\Queue\Handler;

include_once (PHPLIB."/Ext/GuzzleHttp/Promise/functions_include.php");
include_once (PHPLIB."/Ext/GuzzleHttp/functions_include.php");
include_once (PHPLIB."/Ext/GuzzleHttp/Psr7/functions_include.php");

use \S\Strace\Timelog;

class Mns extends Abstraction{
    /**
     * @var \MNS\Client
     */
    protected $mns = null;

    /**
     * push
     * option['delay_seconds'] 延迟消费秒数
     * option['priority'] 队列优先级 1为最好 16为最低 默认为8
     * @param $queue_name
     * @param $message
     * @param array $option
     * @return bool
     */
    public function push($queue_name, $message, $option = array()) {
        Timelog::instance()->resetTime();

        $queue = $this->getInstance()->getQueueRef($queue_name);
        $ret = $queue->sendMessage(new \MNS\Requests\SendMessageRequest($message, $option['delay_seconds'], $option['priority']));

        $this->setStrace(__FUNCTION__, $queue_name);
        return $ret ? $ret->isSucceed() : false;
    }

    public function pop($queue_name, $option = array()) {
        Timelog::instance()->resetTime();

        try{
            $queue = $this->getInstance()->getQueueRef($queue_name);
            $res = $queue->receiveMessage(10);
        } catch (\Exception $e) {
            $res = false;
        }

        $this->setStrace(__FUNCTION__, $queue_name);
        return $res;
    }

    public function delete($queue_name, \MNS\Responses\ReceiveMessageResponse $res){
        Timelog::instance()->resetTime();

        $queue = $this->getInstance()->getQueueRef($queue_name);
        $ret = $queue->deleteMessage($res->getReceiptHandle());

        $this->setStrace(__FUNCTION__, $queue_name);
        return $ret ? $ret->isSucceed() : false;
    }

    /**
     * 重新设置状态为可见
     * @param $queue_name
     * @param \MNS\Responses\ReceiveMessageResponse $res
     * @param $timeout
     * @return mixed
     */
    public function toActive($queue_name, \MNS\Responses\ReceiveMessageResponse $res, $timeout){
        Timelog::instance()->resetTime();

        $queue = $this->getInstance()->getQueueRef($queue_name);
        $ret = $queue->changeMessageVisibility($res->getReceiptHandle(), $timeout);

        $this->setStrace(__FUNCTION__, $queue_name);
        return $ret ? $ret->isSucceed() : false;
    }

    public function close() {
        $this->mns = null;
        return true;
    }

    public function __call($name, $args=array()) {
        Timelog::instance()->resetTime();
        $ret = call_user_func_array(array($this->getInstance(), $name), $args);
        $this->setStrace($name, $args);
        return $ret;
    }

    public function __destruct() {
        $this->close();
    }

    /**
     * @return \MNS\Client
     */
    public function getInstance() {
        if (!$this->mns) {
            $this->mns = new \MNS\Client($this->config['EndPoint'], $this->config['AccessKeyID'], $this->config['AccessKeySecret']);
        }
        return $this->mns;
    }
}
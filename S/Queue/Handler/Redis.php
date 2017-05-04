<?php
namespace S\Queue\Handler;

use \S\Strace\Timelog;
use S\Exception as Exception;

class Redis extends Abstraction{

    const DEFAULT_CONNECT_TIMEOUT = 3;

    /**
     * @var \Redis
     */
    protected $redis = null;
    /**
     * 用于判断是否使用持久连接，如果使用，析构函数中不做close操作
     * @var bool
     */
    private $_persistent = false;

    public function push($queue_name, $message, $option = array()) {
        Timelog::instance()->resetTime();
        $ret = $this->getInstance()->rPush($queue_name, $message);
        $this->setStrace(__FUNCTION__, $queue_name);
        return $ret;
    }

    public function pop($queue_name, $option = array()) {
        Timelog::instance()->resetTime();
        $ret = $this->getInstance()->lPop($queue_name);
        $this->setStrace(__FUNCTION__, $queue_name);
        return $ret;
    }

    public function len($queue_name){
        Timelog::instance()->resetTime();
        $ret = $this->getInstance()->lLen($queue_name);
        $this->setStrace(__FUNCTION__, $queue_name);
        return $ret;
    }

    public function close() {
        $this->redis->close();
        return true;
    }

    /**
     * 将其他方法调用均转向到封装的Redis实例
     *
     * @param string $name
     * @param array $args
     * @return mixed
     */
    public function __call($name, $args=array()) {
        Timelog::instance()->resetTime();
        $ret = call_user_func_array(array($this->getInstance(), $name), $args);
        $this->setStrace($name, $args);
        return $ret;
    }

    public function __destruct() {
        if (!$this->_persistent) {
            $this->close();
        }
    }
    public function getInstance() {
        if (!$this->redis) {
            $this->redis = new \Redis();
            $this->connect();
            $this->setOptions();
        }
        return $this->redis;
    }

    protected function connect() {
        if(isset($this->config['persistent']) && $this->config['persistent']){
            $this->_persistent = true;
            $conn = $this->redis->pconnect($this->config['host'], $this->config['port'], $this->config['timeout'] ?: self::DEFAULT_CONNECT_TIMEOUT);
            //重连一次
            if($conn === false){
                $conn = $this->redis->pconnect($this->config['host'], $this->config['port'], $this->config['timeout'] ?: self::DEFAULT_CONNECT_TIMEOUT);
            }
        }else{
            $conn = $this->redis->connect($this->config['host'], $this->config['port'], $this->config['timeout'] ?: self::DEFAULT_CONNECT_TIMEOUT);
            //重连一次
            if($conn === false){
                $conn = $this->redis->connect($this->config['host'], $this->config['port'], $this->config['timeout'] ?: self::DEFAULT_CONNECT_TIMEOUT);
            }
        }

        if($conn === false){
            throw new Exception("redis connect ".$this->config['host']." fail");
        }
        return true;
    }

    protected function setOptions(){
        if(isset($this->config['user']) && $this->config['user'] && $this->config['auth']){
            if ($this->redis->auth($this->config['user'].":".$this->config['auth']) == false) {
                throw new Exception("redis auth ".$this->config['host']." fail");
            }
        }
        if (!isset($this->config['user']) && isset($this->config['auth']) && $this->config['auth']) {
            $this->redis->auth($this->config['auth']);
        }
        if (isset($this->config['db']) && $this->config['db']) {
            $this->redis->select($this->config['db']);
        }
    }

}
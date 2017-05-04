<?php
namespace S\Mq\Handler;

use \S\Strace\Timelog;

/**
 * Class Abstraction
 *
 * @package     S\Mq\Handler
 * @description 消息队列基类
 */
abstract class Abstraction implements MqInterface {

    /**
     * 配置信息
     *
     * @var array
     */
    protected $config = array();
    /**
     * 传入的表示信息
     *
     * @var string
     */
    protected $name = "";
    /**
     * 调用的子类
     *
     * @var string
     */
    protected $type = "";

    public function __construct() {
        $this->init();
    }

    protected function init() {
    }

    /**
     * 定义消息队列的configure接口，用来实现消息队列的初始化及配置工作
     *
     * @param string $type 消息队列类型
     * @param mixed  $name 配置数据
     *
     * @throws \S\Exception
     */
    public function configure($type, $name) {
        $this->type   = $type;
        $this->name   = $name;
        $this->config = \S\Config::confServer('mq.' . $type . '.' . $name);

        if (!$this->config) {
            throw new \S\Exception(get_class($this) . ' need be configured. Config : ' . $name);
        }
    }

    /**
     * 远程资源调用跟踪
     *
     * @param string $function 调用方法
     * @param mixed  $params   请求参数
     */
    protected function setStrace($function, $params) {
        Timelog::instance()->log($this->type, array(
            'class'    => get_class($this),
            'method'   => $function,
            'resource' => $this->name,
            'params'   => $params,
        ));
    }

    /**
     * 关闭连接
     *
     * 对于某些需要连接的实现，比如Redis等，手动关闭连接可以提供更好的性能优化。
     */
    abstract public function close();

    /**
     * 获取静态实例
     */
    abstract protected function getInstance();
}
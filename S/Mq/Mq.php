<?php

namespace S\Mq;

use S\Exception;

/**
 * Class Mq
 *
 * @package S\Mq
 */
class Mq {

    const TYPE_REDIS = 'redis';
    const TYPE_ONS = 'ons';
    const TYPE_DEFAULT = self::TYPE_ONS;

    const NAME_COMMON = 'common';

    /**
     * 魔术方法, 未定义的静态方法将在此处理
     *
     * @param string $name 方法名
     * @param mixed $args 参数
     *
     * @return mixed
     */
    public static function __callStatic($name, $args) {
        $queue = self::pool(self::TYPE_DEFAULT, self::NAME_COMMON);

        return call_user_func_array(array($queue, $name), $args);
    }

    /**
     * @var \S\Mq\Handler\Abstraction[]
     */
    private static $pools = array();

    /**
     * 根据名字获取一个消息队列实例
     *
     * @param string $name 实例名
     * @param string $type 缓存类型
     *
     * @return \S\Mq\Handler\Abstraction 缓存实例
     * @throws Exception
     */
    public static function pool($type, $name = '') {
        $key = self::_getKey($type, $name);

        if (!isset(self::$pools[$key])) {
            $handler_ns = __NAMESPACE__ . '\\Handler\\';
            $class = $handler_ns . ucfirst($type);
            /* @var \S\Mq\Handler\MqInterface $instance */
            $instance = new $class();
            if (!is_subclass_of($instance, $handler_ns . "MqInterface")) {
                throw new Exception($class . ' is not a subclass of \\S\\Mq\\MqInterface');
            }
            if (is_subclass_of($instance, $handler_ns . "Abstraction")) {
                /* @var \S\Mq\Handler\Abstraction $instance */
                $instance->configure($type, $name);
            }
            self::$pools[$key] = $instance;
        }

        return self::$pools[$key];
    }

    /**
     * 移除实例
     *
     * @param string $type 实例类型
     * @param string $name 实例名称
     *
     * @return bool true-移除成功 false-移除失败
     */
    public static function remove($type, $name = "") {
        $key = self::_getKey($type, $name);

        if (isset(self::$pools[$key])) {
            self::$pools[$key]->close();
            unset(self::$pools[$key]);
        }

        return true;
    }

    /**
     * 获取实例标识
     *
     * @param string $type 实例类型
     * @param string $name 实例名称
     *
     * @return string 实例标识
     */
    private static function _getKey($type, $name = '') {
        return ($type . '-' . $name);
    }

}
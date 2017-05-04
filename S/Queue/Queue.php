<?php

namespace S\Queue;

use S\Exception;
/**
 * 封装队列及简易调用
 *
 * 配置文件的解析放在相应的文件中，初始化时只提供配置名
 * 配置文件为conf/server/../queue.php
 *
 *
 * <code>
 * $ret = \S\Queue\Queue::push($key, $value); //默认使用\S\Queue\Queue::pool('redis', 'common')配置
 * $ret = \S\Queue\Queue::pop($key);
 *
 * //init
 * $redis  = \S\Queue\Queue::pool('redis', 'common');
 *
 * //push
 * $redis->push('key1', '1234567890');
 *
 * //pop
 * $redis->pop('key1');
 *
 * </code>
 */
/**
 * @var /S/Queue/QueueInterface
 */
class Queue {
    const TYPE_REDIS        = 'redis';
    const TYPE_MNS          = 'mns';
    const TYPE_DEFAULT      = self::TYPE_REDIS;

    const NAME_COMMON       = 'common';

    public static function __callStatic($name, $args){
        $queue = self::pool(self::TYPE_DEFAULT, self::NAME_COMMON);
        return call_user_func_array(array($queue, $name), $args);
    }

    /**
     * @var \S\Queue\Handler\Abstraction[]
     */
    private static $pools = array();

    /**
     * 根据名字获取一个队列实例
     *
     * @param string $name  实例名
     * @param string $type  队列类型
     *
     * @return \S\Queue\Handler\Abstraction 队列实例
     * @throws \S\Exception
     */
    public static function pool($type, $name='') {
        $key = self::getKey($type, $name);

        if (!isset(self::$pools[$key])) {
            $handler_ns = __NAMESPACE__.'\\Handler\\';

            $class = $handler_ns.ucfirst($type);
            /* @var \S\Queue\Handler\QueueInterface $instance */
            $instance = new $class();
            if (!is_subclass_of($instance, $handler_ns."QueueInterface")) {
                throw new Exception($class . ' is not a subclass of \\S\\Queue\\QueueInterface');
            }
            if(is_subclass_of($instance, $handler_ns."Abstraction")){
                /* @var \S\Queue\Handler\Abstraction $instance */
                $instance->configure($type, $name);
            }
            self::$pools[$key] = $instance;
        }
        return self::$pools[$key];
    }

    public static function remove($type, $name="") {
        $key = self::getKey($type, $name);
        if (isset(self::$pools[$key])) {
            self::$pools[$key]->close();
            unset(self::$pools[$key]);
        }
        return true;
    }

    private static function getKey($type, $name='') {
        return ($type.'-'.$name);
    }
}
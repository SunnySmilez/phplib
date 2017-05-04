<?php

namespace Base\Dao;

use Base\Exception\Dao as Exception;

/**
 * Class Queue
 *
 * @package Base\Dao
 * @description 队列服务基类
 *              使用示范
 *
 *              namespace Dao\Queue
 *              /**
 *               * Class Foo
 *               * @ description Foo类展示了Dao\Queue下的队列服务的标准样例
 *               *               建议添加 @ method 显式声明对外提供的方法以便ide正确的识别
 *               *
 *               * @ method popTask($task)
 *               * @ method pushTask()
 *               * @ method lenTask()
 *               * /
 *              class Foo extends \Base\Dao\Queue{
 *
 *                  public function __construct(){
 *                      $this->setConfig('Task', 'QUEUE_FOO_TASK');
 *                  }
 *
 *              }
 *
 *              //操作样例
 *              $task = array(
 *                  'param1' => 'val1',
 *                  'param2' => 1,
 *              );
 *              $queue = new \Dao\Queue\Foo();
 *              $queue->pushTask($task);
 *              $task = $queue->popTask();
 *              $len = $queue->lenTask();
 */
class Queue {

    protected static $function = array('pop', 'push', 'len');

    protected $pool_type = \S\Queue\Queue::TYPE_DEFAULT;  //队列类型, 参考\S\Queue\Queue队列类型常量定义
    protected $pool_name = \S\Queue\Queue::NAME_COMMON;  //配置名称, 参考\S\Queue\Queue常量定义

    /**
     * 魔术方法, 所有队列操作将转到此方法中进行处理
     *
     * @param string $name 队列操作
     *                     以: pop push len 等标准队列操作开头定义的方法, 操作以外的部分将被看作队列对象标识
     *                     e.g.
     *                     以UserInfo作为队列标识定义的方法列表:
     *                     popUserInfo pushUserInfo lenUserInfo
     * @param array  $arguments 操作参数
     *                          pop|len: 不需要参数
     *                          push   : 第0个参数代表键
     *
     * @return mixed 操作结果, 实际意义视具体操作而定
     * @throws Exception
     */
    public function __call($name, $arguments) {
        $function = null;
        foreach (self::$function as $need_function) {
            if (stripos($name, $need_function) === 0) {
                $function = $need_function;
            }
        }
        if (!$function) {
            throw new Exception("unsupported function: {$function}");
        }

        $queue_id = strtolower(substr($name, strlen($function)));
        if (empty($this->$queue_id)) {
            throw new Exception("$queue_id not configured");
        }
        if (!$this->pool_name || !$this->pool_type) {
            throw new Exception("class need set pool_name and pool_type");
        }

        $queue = \S\Queue\Queue::pool($this->pool_type, $this->pool_name);

        $config     = $this->$queue_id;
        $queue_name = $config['queue'];

        if ('pop' == $function) {
            $ret = $queue->pop($queue_name);
            $ret = ($ret ? json_decode($ret, true) : $ret);
        } else if ('push' == $function) {
            $ret = ($queue->push($queue_name, json_encode($arguments[0])) ? true : false);
        } else if ('len' == $function) {
            $ret = $queue->len($queue_name);
        } else {
            $ret = false;
        }

        return $ret;
    }

    /**
     * 设置队列配置
     *
     * @param string $queue_id   队列标识
     * @param string $queue_name 队列名
     */
    protected function setConfig($queue_id, $queue_name) {
        $queue_id        = strtolower($queue_id);
        $this->$queue_id = array(
            'queue' => $queue_name,
        );
    }

}
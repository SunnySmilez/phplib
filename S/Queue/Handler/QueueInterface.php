<?php
namespace S\Queue\Handler;

interface QueueInterface {
    /**
     * 队列的入队操作
     * @param $key
     * @param $value
     * @param array $option
     * @return mixed
     */
    public function push($key, $value, $option=array());

    /**
     * 队列的出队操作
     * @param $key
     * @param array $option
     * @return mixed
     */
    public function pop($key, $option=array());
}
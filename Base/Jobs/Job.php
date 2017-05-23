<?php
namespace Base\Jobs;

abstract class Job {
    abstract public function action($argv=array());

    //上送任务log信息
    protected function log($log){
        (new \Api\Rdbs\Jobs())->up($log);
        return true;
    }
}
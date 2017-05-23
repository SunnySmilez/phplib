<?php
namespace Base\Jobs\MapReduce;

abstract class Worker extends \Base\Jobs\Job {

    protected $work_argv = array();
    protected $work_count;
    protected $work_id;

    private $export_file_handle;
    private $export_file_path;

    public final function action($argv=array()){
        $this->work_id    = array_shift($argv);
        $this->work_count = array_shift($argv);
        $this->work_argv  = $argv;
        //打开输出文件
        $run_id = isset($_SERVER['x-rid']) ? $_SERVER['x-rid'] : getmypid();
        $this->export_file_path = "/tmp/Map_Data_{$run_id}";
        $this->export_file_handle = fopen($this->export_file_path, "w");
        register_shutdown_function(array($this, 'finish'));
        //执行处理任务
        $this->process();
        //上传数据文件
        $this->upload();
    }

    /**
     * 任务处理函数
     * @return mixed
     */
    abstract protected function process();

    /**
     * 数据输出函数
     * @param $data
     * @return bool //输出成功/失败
     */
    protected function export($data){
        $ret = fwrite($this->export_file_handle, $data);
        return $ret ? true : false;
    }

    //上传数据文件 重试一次
    private function upload(){
        $file_name = basename($this->export_file_path);

        try{
            \S\FileSystem\FileSystem::getInstance()->put(\Base\Jobs\MapReduce\Master::BUCKET, "MapReduce/{$file_name}", $this->export_file_path);
        }catch (\Exception $e){
            \S\FileSystem\FileSystem::getInstance()->put(\Base\Jobs\MapReduce\Master::BUCKET, "MapReduce/{$file_name}", $this->export_file_path);
        }

        return true;
    }

    public final function finish(){
        fclose($this->export_file_handle);
        //清除临时输出文件
        unlink($this->export_file_path);
        return true;
    }

}
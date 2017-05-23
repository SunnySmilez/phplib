<?php
namespace Base\Jobs\MapReduce;

/**
 * 针对大数据量需要拆分进程分布式处理
 */
abstract class Master extends \Base\Jobs\Job {

    const BUCKET = 'file-map-reduce';
    const CHECK_WORKER_SLEEP = 10; //检查worker任务状态间隔时间

    private $work_run_id = array();

    protected $input_argv = array();   //外部输入参数
    protected $work_class = null;      //worker类 形如Jobs_Job_Test
    protected $work_argv  = array();   //worker任务的执行参数
    protected $work_count = 0;         //worker进程数
    protected $work_data_files = array();  //worker数据文件集

    public final function action($argv=array()){
        $this->input_argv = $argv;
        $this->beforeMap();
        if(!$this->work_class || !$this->work_count){
            throw new \S\Exception('class work_class or work_count not set');
        }

        if(\Core\Env::isProductEnv()){
            $this->remoteMap();
        }else{
            $this->localMap();
        }
        $this->download();
        register_shutdown_function(array($this,'finish'));
        $this->reduce();
    }

    //在分布式环境中派生子任务
    private function remoteMap(){
        $api_rds_jobs = new \Api\Rdbs\Jobs();

        $job_ids = array();
        for($i=0;$i<$this->work_count;$i++){
            $argv = array(
                'work_id'       => $i,
                'work_count'    => $this->work_count
            );
            $work_argv = array_merge($argv, $this->work_argv);
            $job_ids[] = $api_rds_jobs->submit($this->work_class, $work_argv, "{$_SERVER['x-rid']}map任务", true);
        }
        $this->log("分发了".$this->work_count."个进程处理");

        //等待map任务执行完成
        while(count($job_ids) > 0){
            sleep(self::CHECK_WORKER_SLEEP);
            foreach ($job_ids as $work_id=>$job_id){
                try{
                    $ret = $api_rds_jobs->query($job_id);
                }catch (\S\Exception $e){
                    $ret = array();
                }
                if($ret['status'] == \Api\Rdbs\Jobs::STATUS_SUCC){
                    unset($job_ids[$work_id]);
                    $this->work_run_id[$work_id] = $ret['run_id'];
                }elseif($ret['status'] == \Api\Rdbs\Jobs::STATUS_FAIL){
                    throw new \S\Exception("map任务执行失败");
                }
            }
        }
        ksort($this->work_run_id);
        $this->log($this->work_count."个进程已经全部处理完成");

        return true;
    }

    //在本地派生子任务
    private function localMap(){
        for($i=0;$i<$this->work_count;$i++){
            $argv = array(
                'work_id'       => $i,
                'work_count'    => $this->work_count
            );
            $work_argv = array_merge($argv, $this->work_argv);
            $params = implode(" ", $work_argv);
            $command = "php /data1/htdocs/".APP_NAME."/jobs/job.php {$this->work_class} {$params}";

            $process = proc_open($command, array(), $pipes, "/", array());
            $this->work_run_id[$i] = proc_get_status($process)['pid'];
            $ret = proc_close($process);
            if($ret == 255){
                throw new \S\Exception("map任务执行失败");
            }
        }
        $this->log($this->work_count."个进程已经全部处理完成");

        return true;
    }

    //下载
    private function download(){
        foreach ($this->work_run_id as $work_id=>$run_id){
            $map_file_name = "Map_Data_{$run_id}";
            try{
                $file_content = \S\FileSystem\FileSystem::getInstance()->get(self::BUCKET, "MapReduce/{$map_file_name}");
            }catch (\Exception $e){
                $file_content = \S\FileSystem\FileSystem::getInstance()->get(self::BUCKET, "MapReduce/{$map_file_name}");
            }
            file_put_contents("/tmp/{$map_file_name}", $file_content);
            unset($file_content);
            $this->work_data_files[] = "/tmp/{$map_file_name}";
        }
        $this->log("数据文件下载完成");

        return true;
    }

    public final function finish(){
        //清除临时数据文件
        foreach ($this->work_data_files as $file_path){
            unlink($file_path);
        }
        return true;
    }

    abstract protected function beforeMap();
    abstract protected function reduce();

}
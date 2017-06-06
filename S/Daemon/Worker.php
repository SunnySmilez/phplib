<?php

namespace S\Daemon;

/**
 * 子进程基类
 *
 * 子进程类需要继承此类并实现process方法
 * 子进程的业务流程需要实现在process()方法里
 * process()方法每隔一定时间会执行一次, 由$whileSleep控制 默认1秒
 *
 * 子进程受主进程控制
 * 子进程预设了进程的生存时间和执行次数上限, 在超过生存时间或执行次数超过上限时，进程会被回收, 然后主进程会启动一个新的进程
 *
 * worker进程停止场景：
 *     接收到SIGTERM信号
 *     关联文件发生改变
 *     处理任务数量达到上限
 *     达到生命周期
 *
 * process方法中的异常不会影响当前进程的中断退出
 */
abstract class Worker {

    const DEFAULT_SLEEP_INTERVAL = 10000;  //默认未处理任务时进程睡眠间隔时间: 10毫秒

    protected $is_running = false;
    protected $included_files_md5 = null;
    protected $run_num = 0;  //已处理任务数量
    protected $run_start_time = 0;  //进程开始运行时间
    protected $sleep_interval = 1;  //未处理任务时进程睡眠时间

    protected $config;

    /**
     * Worker constructor.
     *
     * @param array $config
     */
    public function __construct(array $config) {
        $this->config = $config;
    }

    /**
     * 子进程开始处理任务
     *
     * @access public
     * @return void
     */
    public function doTask() {
        $this->run_start_time = time();
        $this->is_running     = true;

        if ($this->sleep_interval) {
            $this->sleep_interval *= 1000000;
        } else {
            $this->sleep_interval = self::DEFAULT_SLEEP_INTERVAL;
        }

        $this->registerSigHandler();

        $process_name = cli_get_process_title();
        Utils::echoInfo($process_name . " start");

        while ($this->is_running) {
            try {
                $this->process();
            } catch (\Throwable $e) {
                Utils::echoInfo($process_name . " throw " . $e->getMessage());
                $this->is_running = false;
            }

            $this->run_num++;

            if (!$this->checkIncludedFiles()) {
                Utils::echoInfo($process_name . " included file md5 change");
                $this->is_running = false;
            }
            if (!$this->checkRunNum()) {
                Utils::echoInfo($process_name . " run num over");
                $this->is_running = false;
            }
            if (!$this->checkRunTtl()) {
                Utils::echoInfo($process_name . " run ttl over");
                $this->is_running = false;
            }

            usleep($this->sleep_interval);
            pcntl_signal_dispatch();
        }
    }

    /**
     * 注册子进程信息号
     *
     * @access protected
     * @return void
     */
    protected function registerSigHandler() {
        pcntl_signal(SIGINT, SIG_IGN);
        pcntl_signal(SIGHUP, SIG_IGN);
        pcntl_signal(SIGQUIT, SIG_IGN);
        pcntl_signal(SIGTERM, array($this, 'sigHandler'));
    }

    /**
     * 子进程信号处理
     *
     * @param int $sig 信号量
     *
     * @access protected
     * @return void
     */
    protected function sigHandler($sig) {
        switch (intval($sig)) {
            case SIGTERM:
                $this->stop();
                break;
            default:
                break;
        }
    }

    /**
     *  接受到SIGTERM信号时，结束子进程
     *
     * @access public
     * @return void
     */
    protected function stop() {
        $this->is_running = false;
        Utils::echoInfo(cli_get_process_title() . " receive stop sign");
        exit();
    }

    /**
     * 检查文件变更
     */
    protected function checkIncludedFiles() {
        $latest_included_files_md5 = Utils::getIncludedFilesMd5();

        if (!$this->included_files_md5) {
            $this->included_files_md5 = $latest_included_files_md5;

            return true;
        }

        if (array_diff($this->included_files_md5, $latest_included_files_md5)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 检查循环次数
     */
    protected function checkRunNum() {
        if ($this->run_num >= $this->config["deal_num"]) {
            return false;
        }

        return true;
    }

    /**
     * 检查生存时长
     */
    protected function checkRunTtl() {
        if ((time() - $this->run_start_time) >= $this->config["ttl"]) {
            return false;
        }

        return true;
    }

    /**
     * 任务处理函数
     *
     * @abstract
     * @access public
     * @return void
     */
    abstract public function process();

}
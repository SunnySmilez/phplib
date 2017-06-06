<?php

namespace S\Daemon;

/**
 * Class Master
 *
 * @package     S\Daemon
 * @description 进程管理主进程类
 *
 * 此类用于进行进程管理的入口，使用示例：
 *
 * $config = new \S\Daemon\Config();
 *
 * $config->setWorkerConfig("\\Jobs\\Daemon\\ClassI", 1);
 * $config->setWorkerConfig("\\Jobs\\Daemon\\ClassII", 2);
 *
 * $master = new \S\Daemon\Master($config);
 * $master->main();
 *
 * 一个守护进程里只有一个主进程
 * 主进程负责启动、管理工作进程
 * 当进程数不足时, 会启动新的进程
 */
class Master {

    const MASTER_PID_FILE_PATH = "/var/run/php_daemon_master_pid." . APP_NAME;

    /**
     * @var \S\Daemon\Config
     */
    protected $config;
    protected $is_running = false;
    /**
     * @var \Swoole\Process[]
     */
    protected $workers;

    public function __construct(\S\Daemon\Config $config) {
        if (!\Core\Env::isCli()) {
            throw new \S\Exception("当前环境非cli模式");
        }
        $this->config = $config;
    }

    /**
     * 启动并且实时监控工作进程
     */
    public function main() {
        \Swoole\Process::daemon(false, false);

        $last_pid = file_get_contents(self::MASTER_PID_FILE_PATH);
        if ($last_pid && \Swoole\Process::kill($last_pid, 0)) {
            \Swoole\Process::kill($last_pid, SIGTERM);
        }

        file_put_contents(self::MASTER_PID_FILE_PATH, getmypid());

        Utils::echoInfo("master start");
        $this->is_running = true;

        $this->registerSigHandler();

        $this->forkWorkers();
    }

    /**
     * 管理进程
     */
    protected function forkWorkers() {
        $worker_configs = $this->config->getWorkerConfig();

        foreach ($worker_configs as $class_name => $config) {
            for ($i = 0; $i < $config["worker_num"]; $i++) {
                $worker = new \Swoole\Process(function () use ($class_name, $config) {
                    swoole_set_process_name("PHP_" . strtoupper(APP_NAME) . "_DAEMON_WORKER_" . $class_name);

                    \Core\Env::setCliClass($class_name);
                    /** @var \S\Daemon\Worker $worker_class */
                    $worker_class = new $class_name($config);
                    $worker_class->doTask();
                });

                $pid                 = $worker->start();
                $this->workers[$pid] = $worker;
            }
        }
    }

    /**
     * 信号注册
     *
     * @access protected
     * @return void
     */
    protected function registerSigHandler() {
        \Swoole\Process::signal(SIGTERM, function ($signo) {
            $this->sigHandler($signo);
        });
        \Swoole\Process::signal(SIGINT, function ($signo) {
            $this->sigHandler($signo);
        });
        \Swoole\Process::signal(SIGCHLD, function ($signo) {
            $this->sigHandler($signo);
        });
    }

    /**
     * 信号处理
     *
     * @param int $sig
     *
     * @access private
     * @return void
     */
    protected function sigHandler($sig) {
        Utils::echoInfo("master receive $sig");

        switch (intval($sig)) {
            case SIGCHLD:
                //当子进程停止或退出时通知父进程
                $this->waitChild();
                break;
            case SIGINT:
                //中断进程
                $this->cleanup();
                break;
            case SIGTERM:
                //终止进程
                $this->cleanup();
                break;
            case SIGQUIT:
                //终止进程，并且生成core文件
                break;
            case SIGHUP:
                //终端线路挂断
                break;
            default:
                break;
        }
    }

    /**
     * 处理退出的子进程
     *
     * @access protected
     * @return void
     */
    protected function waitChild() {
        while (true) {
            $ret = \Swoole\Process::wait(false);
            if ($ret) {
                $pid = $ret['pid'];
                Utils::echoInfo("master waitpid $pid");

                if ($this->is_running) {
                    $worker                  = $this->workers[$pid];
                    $new_pid                 = $worker->start();
                    $this->workers[$new_pid] = $worker;
                }

                unset($this->workers[$pid]);
            } else {
                break;
            }
        }
    }

    /**
     * 进程退出前的清理任务
     *
     * 父进程接受到退出信号时，给子进程发送SIGTERM信号
     */
    protected function cleanup() {
        Utils::echoInfo("clean up");

        $this->is_running = false;

        if (count($this->workers)) {
            foreach ($this->workers as $pid => $worker) {
                Utils::echoInfo("master posix kill $pid");
                \Swoole\Process::kill($pid, SIGTERM);
            }
        }

        Utils::echoInfo("master stop");
        exit();
    }

}
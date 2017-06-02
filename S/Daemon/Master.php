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
 * $master = new \S\Daemon\Master();
 * $master->main($config);
 *
 * 一个守护进程里只有一个主进程
 * 主进程负责启动、管理工作进程
 * 每10s主进程会检查工作进程的生存情况
 * 当进程数不足时, 会启动新的进程
 * 当进程数超过时, 按启动时间依次停止工作进程直到进程数符合要求
 */
class Master {

    const MASTER_PID_FILE_PATH = "/var/run/php_daemon_master_pid." . APP_NAME;
    const MASTER_SLEEP = 10;

    /**
     * @var \S\Daemon\Config
     */
    protected $config;
    protected $is_running = false;
    protected $worker_pids = array();

    public function __construct(\S\Daemon\Config $config) {
        if (!\Core\Env::isCli()) {
            throw new \S\Exception("当前环境非cli模式");
        }
        $this->config = $config;

        \Swoole\Process::daemon(false, false);

        $last_pid = file_get_contents(self::MASTER_PID_FILE_PATH);
        if ($last_pid && \Swoole\Process::kill($last_pid, 0)) {
            \Swoole\Process::kill($last_pid, SIGTERM);

            //等待上一个进程退出
            while (file_get_contents(self::MASTER_PID_FILE_PATH)) {
                sleep(3);
            }
        }

        //进程pid落地
        file_put_contents(self::MASTER_PID_FILE_PATH, getmypid());
    }

    /**
     * 启动并且实时监控工作进程
     */
    public function main() {
        Utils::echoInfo("master start");
        $this->registerSigHandler();

        $this->is_running = true;

        while ($this->is_running) {
            $this->manageWorkers();

            pcntl_signal_dispatch();
            sleep(self::MASTER_SLEEP);
        }

        //等待子进程退出
        while (count($this->worker_pids) > 0) {
            pcntl_signal_dispatch();
            sleep(3);
        }

        file_put_contents(self::MASTER_PID_FILE_PATH, "");
    }

    /**
     * 信号处理
     *
     * @param int $sig
     *
     * @access private
     * @return void
     */
    public function sigHandler($sig) {
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
     * 信号注册
     *
     * @access protected
     * @return void
     */
    protected function registerSigHandler() {
        pcntl_signal(SIGTERM, array($this, 'sigHandler'));
        pcntl_signal(SIGHUP, array($this, 'sigHandler'));
        pcntl_signal(SIGCHLD, array($this, 'sigHandler'));
        pcntl_signal(SIGINT, array($this, 'sigHandler'));
        pcntl_signal(SIGQUIT, array($this, 'sigHandler'));
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
                unset($this->worker_pids[$pid]);
            } else {
                break;
            }
        }
    }

    /**
     * 父进程接受到退出信号时，给子进程发送SIGTERM信号
     *
     * @param null $pid
     */
    protected function cleanup($pid = null) {
        Utils::echoInfo("clean up $pid");

        if ($pid) {
            Utils::echoInfo("master posix kill $pid");
            \Swoole\Process::kill($pid, SIGTERM);
        } else {
            if (count($this->worker_pids)) {
                foreach ($this->worker_pids as $pid => $class_name) {
                    Utils::echoInfo("master posix kill $pid");
                    \Swoole\Process::kill($pid, SIGTERM);
                }
            }

            Utils::echoInfo("master stop");
            $this->is_running = false;
        }
    }

    /**
     * 管理进程
     */
    protected function manageWorkers() {
        $worker_configs = $this->config->getWorkerConfig();
        $worker_pids    = Utils::getWorkerProcessInfo($this->worker_pids);

        //查看进程情况，按配置启动和减少进程
        foreach ($worker_configs as $classname => $item) {
            $num = count($worker_pids[$classname]) - $this->config->getWorkerNum($classname);

            if ($num >= 0) {
                for ($i = 0; $i < $num; $i++) {
                    $this->cleanup($worker_pids[$classname][$i]);
                }
            } else {
                $this->fork($classname);
            }
        }
    }

    /**
     * fork子进程
     *
     * @param string $class_name 子进程类名
     *
     * @return bool
     * @throws \Exception
     */
    protected function fork($class_name) {
        $worker = new \Swoole\Process(function () use ($class_name) {
            swoole_set_process_name("THREAD_PHP_" . strtoupper(APP_NAME) . "_" . $class_name);

            \Core\Env::setCliClass($class_name);
            /** @var \S\Daemon\Worker $worker_class */
            $worker_class = new $class_name($this->config);
            $worker_class->doTask();
        });

        $pid                     = $worker->start();
        $this->worker_pids[$pid] = array(
            'classname' => $class_name,
        );

        return true;
    }

}
<?php

namespace S\Daemon;
/**
 * Class Config
 *
 * @package     S\Daemon
 * @description 进程配置类
 *
 * 子进程配置类，包括：
 *     子进程数量
 *     子进程生命周期
 *     子进程处理任务数量上限
 *
 * 配置一旦生效将保存入配置文件中，该文件默认存放路径为：/tmp/daemon_config_file.conf
 *
 * 此类用于进行配置子进程，使用示例：
 *
 * $config = new \S\Daemon\Config();
 *
 * $config->setWorkerConfig("\\Jobs\\Daemon\\ClassI", 1);
 * $config->setWorkerConfig("\\Jobs\\Daemon\\ClassII", 2);
 *
 * $master = new \S\Daemon\Master();
 * $master->main();
 */
class Config {

    const DEFAULT_WORKER_TTL = 8640;  //子进程1小时，回收
    const DEFAULT_WORKER_DEAL_NUM = 100000;  //子进程循环处理100000个，回收

    protected $_config = array();

    /**
     * 设置工作进程的配置
     *
     * @param string $classname 类名(包括命名空间)
     * @param int    $work_num  工作进程数
     * @param int    $ttl       进程工作多少时间会被回收     默认一天
     * @param int    $deal_num  进程循环处理多少次会被回收   默认1000000次
     *
     * @return bool
     *
     */
    public function setWorkerConfig($classname, $work_num, $ttl = self::DEFAULT_WORKER_TTL, $deal_num = self::DEFAULT_WORKER_DEAL_NUM) {
        if (0 == $ttl) {
            $ttl = self::DEFAULT_WORKER_TTL;
        }
        if (0 == $deal_num) {
            $deal_num = self::DEFAULT_WORKER_DEAL_NUM;
        }
        $this->_config[$classname] = array(
            'work_num' => $work_num,
            'ttl'      => $ttl,
            'deal_num' => $deal_num,
        );

        return true;
    }

    /**
     * 获取进程配置
     *
     * @return array
     */
    public function getWorkerConfig() {
        return $this->_config;
    }

    /**
     * 获取子进程生命周期
     *
     * @param string $classname 子进程类名
     *
     * @return int
     */
    public function getWorkerTtl($classname) {
        return $this->_config[$classname]['ttl'];
    }

    /**
     * 获取子进程数量上限
     *
     * @param string $classname 子进程类名
     *
     * @return int
     */
    public function getWorkerNum($classname) {
        return $this->_config[$classname]['work_num'];
    }

    /**
     * 获取子进程处理任务数量上限
     *
     * @param string $classname 子进程类名
     *
     * @return int
     */
    public function getWorkerDealNum($classname) {
        return $this->_config[$classname]['deal_num'];
    }

}
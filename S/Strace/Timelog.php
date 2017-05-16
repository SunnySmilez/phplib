<?php
namespace S\Strace;

/**
 * 记录代码执行时间,此日志作为分析代码执行时间的参考，不用于信息系统部的统计
 *
 * 注意：
 *  程序执行时间的计算不是精确的
 * 计时原理：
 *  因为考虑到大部分耗时的操作（如db、cache、api接口调用...）都会通过框架执行，而框架部分可能耗时的操作处都会调用STimelog::log()，
 *  一般两次调用STimelog::log()之间php的执行消耗时间可以忽略，所以我们简单的认为两次调用STimelog::log()方法之间的时间间隔即为本次耗时操作的时间
 *
 * 使用
 * <code>
 *  STimelog::instance()->log('memcacheq', array(
 *        'class'     => __CLASS__,
 *        'method'    => __METHOD__,
 *        'resource'  => $config_name,
 *        'extension' => array('queuename'=>$queuename)
 *    ));
 * </code>
 */
class Timelog {

    const TIMELOG_NAME = 'timelog';

    public static $all_log = true;

    /**
     * 上次运行时间，microtime(true)
     */
    private static $_last_run_time = '';

    private static $_instance = null;

    /**
     * 日志字段名
     * class: string 类名
     * method: string 方法名
     * type:s tring 资源类型(例:mc,mq,mysql)
     * resource: string 资源(例如：配置文件中的资源名称、ip或者url)
     * params: string 参数(例如:mysql中的sql语句或mc中的key)
     * execute_time: 注意此参数用于定义log顺序，不需要在程序中设置
     * extension: array 额外的参数
     */
    private $_log_item = array('res_type', 'idc', 'class', 'method', 'resource', 'params', 'exectime', 'extension');

    /**
     * 阈值，需要根据不同的业务实现定义，单位ms
     */
    private $_threshold = array(
        'memcached' => 500,
        'memcache'  => 500,
        'mysql'     => 1000,   // 基本记录下所有的db操作，用户分析数据库操作是否正常
        'http'      => 1000,
        'redis'     => 1000,
        'socket'    => 1000,
        'soap'      => 1500,
        'ots'       => 1500,
        'mns'       => 1500,
    );

    private function __construct() {
        if (!defined('APP_START_TIME')) {
            throw new \S\Exception('Please define APP_START_TIME for STimelog.');
        }
    }

    /**
     * @return Timelog
     */
    public static function instance() {
        if (self::$_instance === null) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

    /**
     * 获取需要将执行时间写入调试日志中的时间阀值
     */
    public function getTimeThreshold($res_type) {
        if (!isset($this->_threshold[$res_type])) {
            return false;
        }

        return $this->_threshold[$res_type];
    }

    /**
     * 设置需要将执行时间写入调试日志中的时间阀值
     */
    public function setTimeThreshold($res_type, $value) {
        if (!is_numeric($value)) {
            return false;
        }
        if (!isset($this->_threshold[$res_type])) {
            return false;
        } else {
            $this->_threshold[$res_type] = $value;

            return true;
        }
    }

    /**
     * 设置时间为当前时间，用于减少其他流程调用产生的误差
     *
     * @return mixed
     */
    public function resetTime() {
        return self::$_last_run_time = microtime(true);
    }

    /**
     * 记录timelog日志
     *
     * @param string $res_type 资源类型 例：mc,mq,mysql
     * @param array  $log      日志内容.
     *                         例如：
     *                         $log = array(
     *                         'rid'       => '123'
     *                         'class'     => 'user',
     *                         'method'    => 'get_uid',
     *                         'resource'  => '10.10.10.10:12345',
     *                         'params'    => array('uid'=>'1111111', 'entry'=>'sso', 'ip'=>'1.1.1.1'),
     *                         'extension' => array('call_server_ip'=>'2.2.2.2', 'reg_from'=>'weibo')
     *                         );
     *
     * @return bool
     * @throws \S\Exception
     */
    public function log($res_type, array $log) {
        if (!isset($this->_threshold[$res_type])) {
            return false;
        }

        $current_time         = microtime(true);
        $log['exectime']      = ($current_time - (self::$_last_run_time ? self::$_last_run_time : APP_START_TIME)) * 1000;
        self::$_last_run_time = $current_time;

        if (($log['exectime'] < $this->_threshold[$res_type]) && !self::$all_log) {
            return true;
        }

        $log['exectime'] = round($log['exectime']);
        $log['res_type'] = $res_type;
        $log['idc']      = \Core\Env::getIdc();

        $log_filter = array();
        foreach ($this->_log_item as $value) {
            $log_filter[$value] = ($log[$value] ?? '');
        }

        return \S\Log\Logger::getInstance()->debug($log_filter, self::TIMELOG_NAME);
    }

}
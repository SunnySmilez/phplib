<?php
namespace Modules\Admin\Model\Dao\Db\Sysconfig;

/**
 * Class VisitLog
 *
 * @package Modules\Admin\Model\Dao\Db\Sysconfig
 * @description 访问日志表
 */
class VisitLog extends \Modules\Admin\Model\Dao\Db\Db {

    private $table;

    public function __construct() {
        $this->table = self::table('sysconfig_visitlog');
    }

    /**
     * 添加记录
     */
    public function add() {
        $request_info = $_REQUEST;
        unset($request_info['username'], $request_info['password']);
        $request_info = \Dao\Db::encrypt(json_encode($request_info));

        $data = array(
            'uri'          => \Yaf\Dispatcher::getInstance()->getRequest()->getRequestUri(),
            'uname'        => $_SESSION['uname'] ?: "",
            'session_info' => json_encode($_SESSION),
            'server_info'  => json_encode($_SERVER),
            'request_info' => $request_info,
        );

        return self::db()->insert($this->table, $data);
    }

    /**
     * 查询获取列表数据源
     *
     * @param $uname
     * @param $controller
     * @param $time_start
     * @param $time_end
     *
     * @return array
     */
    public function getDataTable($uname, $controller, $time_start, $time_end) {
        $where = array();
        $sql   = "select * from " . $this->table;

        if ($uname) {
            $where[] = "`uname` = '{$uname}'";
        }
        if ($controller) {
            $where[] = "`uri` = '{$controller}'";
        }
        if ($time_start) {
            $where[] = "`ctime` >= '{$time_start}'";
        }
        if ($time_end) {
            $where[] = "`ctime` <= '{$time_end}'";
        }

        if ($where) {
            $sql .= " where " . implode(' and ', $where);
        }

        $data = self::dataTableSql($sql);

        return $data ? $data : array();
    }

}
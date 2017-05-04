<?php
namespace Modules\Admin\Model\Dao\Db\Sysconfig;

/**
 * Class Ip
 *
 * @package Modules\Admin\Model\Dao\Db\Sysconfig
 * @description IP白名单表
 */
class Ip extends \Modules\Admin\Model\Dao\Db\Db {

    private $table;

    public function __construct() {
        $this->table = self::table('sysconfig_ip');
    }

    public function add($ip, $description) {
        $data = array(
            'ip'          => $ip,
            'description' => $description,
        );

        return self::db()->insert($this->table, $data);
    }

    public function update($prev_ip, $ip, $description) {
        $data      = array(
            'ip'          => $ip,
            'description' => $description,
        );
        $condition = array(
            'ip' => $prev_ip,
        );

        return self::db()->update($this->table, $data, $condition);
    }

    public function updateStatus($ip, $status) {
        $data      = array(
            'status' => $status,
        );
        $condition = array(
            'ip' => $ip,
        );

        return self::db()->update($this->table, $data, $condition);
    }

    /**
     * @param $ip
     *
     * @return array
     */
    public function queryByIp($ip) {
        $params = array(
            'ip' => $ip,
        );

        return self::db()->queryone($this->table, $params);
    }

    public function del($ip) {
        $condition = array(
            'ip' => $ip,
        );

        return self::db()->delete($this->table, $condition);
    }

    /**
     * @param $ip
     *
     * @return array
     */
    public function queryDataTable($ip) {
        $sql = "select * from " . $this->table;

        $where = array();
        if ($ip) {
            $where[] = "ip = '{$ip}'";
        }

        if ($where) {
            $sql .= " where " . implode(' and ', $where);
        }

        $data = self::dataTableSql($sql);

        return $data ? $data : array();
    }

}
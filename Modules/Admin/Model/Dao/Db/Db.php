<?php

namespace Modules\Admin\Model\Dao\Db;

use Modules\Admin\Model\Util\DataTable as UtilDataTable;

/**
 * Class Db
 *
 * @package     Modules\Admin\Model\Dao\Db
 * @description 管理后台db基类
 *              启用读写分离
 *              查询使用备库
 */
class Db extends \Base\Dao\BackupDb {

    const TABLE_PREFIX = 'admin_';  //admin模块表名前缀

    /**
     * 选择库实例
     *
     * @param string $name 库名, 默认与index主模块使用相同库
     *
     * @return \S\Db\Mysql
     */
    public static function db($name = \Dao\Db::DB_NAME) {
        return self::getInstance($name);
    }

    /**
     * 选择表
     * 自动添加表前缀, 获取表必须使用此方法
     *
     * @param string $table 表名
     *
     * @return string 带有admin前缀的表名, 格式: admin_table
     */
    public static function table($table) {
        return self::TABLE_PREFIX . $table;
    }

    /**
     * 根据条件分页查询(使用dataTable组件)
     *
     * @param string $table     表名
     * @param array  $params    查询条件, k-v结构array
     *                          e.g.
     *                          array(
     *                              'col_i' => 'value',
     *                              'col_ii' => 1,
     *                          )
     * @param array  $cols      default array() 查询条目
     *                          e.g.
     *                          array(
     *                              'col_i',
     *                              'col_ii',
     *                          )
     * @param array  $order     default array() 排序参数, k-v结构array
     *                          e.g.
     *                          array(
     *                              'col_i' => 'asc',
     *                          )
     *
     * @return array 分页查询结果
     */
    public static function dataTable($table, array $params, array $cols = array(), array $order = array()) {
        $table_params = UtilDataTable::getTableParams();
        if ($table_params) {
            $params = array_merge($params, $table_params['search']);
            $order  = array_merge($order, $table_params['order']);
            $count  = self::db()->count($table, $params);

            $ret['data']                 = self::db()->query($table, $params, $cols, $order, $table_params['limit']);
            $ret['draw']                 = $table_params['draw'];
            $ret['iTotalRecords']        = $count;
            $ret['iTotalDisplayRecords'] = $count;
        } else {
            $ret['data'] = self::db()->query($table, $params, $cols, $order);
        }

        return $ret;
    }

    /**
     * 根据sql语句分页查询(使用dataTable组件)
     *
     * @param string $sql sql语句
     * @param array $params 查询条件, k-v结构array
     *                      e.g.
     *                      array(
     *                          'col_i' => 'value',
     *                          'col_ii' => 1,
     *                      )
     * @param int   $style default 2 结果读取形式, 默认键值为表字段
     *
     * @return array 分页查询结果
     */
    public static function dataTableSql($sql, $params = array(), $style = \PDO::FETCH_ASSOC) {
        $sql          = trim($sql, ';');
        $table_params = UtilDataTable::getTableParams();
        if ($table_params) {
            $params = array_merge($params, $table_params['search']);
            $sql    = preg_replace('/^\s*SELECT\s+/i', 'SELECT SQL_CALC_FOUND_ROWS ', $sql);

            // 直接将 limit 加到sql的最后，理论上这里是读操作，不会出问题
            $limit = $table_params['limit'];
            if ($limit) {
                $sql .= ' LIMIT ' . intval(array_shift($limit)) . ", " . intval(array_shift($limit));
            }

            $ret['data']                 = self::db()->qsql($sql, $params, $style);
            $ret['draw']                 = $table_params['draw'];
            $ret['iTotalRecords']        = self::db()->execute('SELECT FOUND_ROWS()')->fetchColumn();
            $ret['iTotalDisplayRecords'] = $ret['iTotalRecords'];
        } else {
            $ret['data'] = self::db()->qsql($sql, $params, $style);
        }

        return $ret;
    }

}
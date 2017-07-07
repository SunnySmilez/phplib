<?php
namespace Modules\Admin\Model\Util;

use \S\Request;

class DataTable {

    /**
     * 获取dataTable查询参数
     *
     * @return array
     * @throws \S\Exception
     */
    public static function getTableParams() {
        if (0 !== strpos(\S\Request::server('PATH_INFO'), '/admin')) {
            throw new \S\Exception("只有后台模块才能使用dataTable组件");
        }

        $draw = intval(Request::request('draw'));
        if ($draw) {
            $ret['draw']   = $draw;
            $ret['search'] = self::getSearchParams();
            $ret['order']  = self::getOrderParams();
            $ret['limit']  = self::getPagerParams();
        } else {
            $ret = array();
        }

        return $ret;
    }

    /**
     * 获取查询条件
     *
     * @return array
     */
    protected static function getSearchParams() {
        $params = array();

        $columns = Request::request("columns") ?: array();
        foreach ($columns as $column) {
            $column_name = strip_tags($column['data']);
            $able        = strip_tags($column['searchable']);
            $search      = strip_tags($column['search']['value']);

            if (isset($search) && $search !== "" && $able) {
                $params[$column_name] = $search;
            }
        }

        return $params;
    }

    /**
     * 获取分页参数
     *
     * @return array
     */
    protected static function getPagerParams() {
        $start  = intval(Request::request('start'));
        $length = intval(Request::request('length'));

        return array($start, $length);
    }

    /**
     * 获取排序条件
     *
     * @return array
     */
    protected static function getOrderParams() {
        $orders        = array();
        $order_columns = Request::request("order") ?: array();
        $columns       = Request::request("columns") ?: array();

        foreach ($order_columns as $order_column) {
            $column_number = strip_tags($order_column['column']);
            $column_name   = strip_tags($columns[$column_number]['data']);
            $able          = strip_tags($columns[$column_number]['orderable']);
            $order         = strip_tags($order_column['dir']);

            if ($able && $order) {
                $orders[$column_name] = $order;
            }
        }

        return $orders;
    }

}
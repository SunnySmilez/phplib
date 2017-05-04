<?php
namespace Modules\Admin\Model\Data\Sysconfig;

use Modules\Admin\Model\Dao\Db\Sysconfig\VisitLog as DbVisitLog;

class Visitlog {

    /**
     * 添加一条纪录
     *
     * @return bool|int
     */
    public function add() {
        $ret = (new DbVisitLog())->add();

        return $ret;
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
        $ret = (new DbVisitLog())->getDataTable($uname, $controller, $time_start, $time_end);

        return $ret;
    }

}

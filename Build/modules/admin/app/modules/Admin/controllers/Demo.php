<?php

/**
 * @name admin控制器示范
 */
class Controller_Demo extends \Modules\Admin\Controllers\Base {

    /**
     * @name dataTable示范页面
     */
    public function dataTableIndexAction() {
    }

    /**
     * @name 访问记录数据源
     */
    public function listAction() {
        $this->response = (new \Admin\Model\Data\Demo())->queryDataTable();
    }

    /**
     * @name 图表示范
     */
    public function chartIndexAction() {
    }

}

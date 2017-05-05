<?php
namespace Admin\Model\Data;

use Admin\Model\Dao\Db\Demo as AdminDbDemo;

/**
 * Class Demo
 *
 * @package Admin\Model\Data
 * @description admin模块Data层示例
 */
class Demo{

    /**
     * 分页查询示例
     *
     * @return array
     */
    public function queryDataTable(){
        return (new AdminDbDemo())->queryDataTable();
    }

}
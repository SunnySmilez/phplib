<?php
namespace Admin\Model\Dao\Db;

use Modules\Admin\Model\Dao\Db\Db as AdminDb;

/**
 * Class Demo
 *
 * @package Admin\Model\Dao\Db
 * @description admin模块Db层示例
 */
class Demo extends AdminDb {

    private $table;

    public function __construct() {
        $this->table = \Dao\Db\Demo::table('demo');
    }

    /**
     * 分页查询示例
     *
     * ******************************
     * dataTable方法仅能在admin模块调用
     * ******************************
     *
     * @return array
     */
    public function queryDataTable(){
        $params = array();

        return self::dataTable($this->table, $params);
    }

}
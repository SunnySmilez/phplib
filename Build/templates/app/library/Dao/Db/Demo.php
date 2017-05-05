<?php
namespace Dao\Db;
/**
 * Class Demo
 *
 * @package Dao\Db
 * @description 数据库数据访问层示范
 *
 *              备注:
 *              1. 在Dao层中应该只关心数据的基本操作, 如: 插入、更新、 查询等;
 *              2. 持久化数据操作应尽量避免数据的物理删除, 以状态值的更新来替代, 如: 1代表生效 0代表失效或已删除;
 *              3. 禁止跨层调用, Dao层仅包括数据访问的最基本操作, 因此禁止调用其他层, 包括上层以及其他Dao层的服务;
 */
class Demo extends \Dao\Db {

    private $table;

    public function __construct(){
        $this->table = self::table('demo');
    }

    /**
     * 查询
     *
     * @param int $id
     *
     * @return array
     */
    public function get($id) {
        return self::db()->queryone($this->table, array('id'=>$id));
    }

    /**
     * 保存
     *
     * @param array $data
     *
     * @return bool
     */
    public function add(array $data) {
        return self::db()->insert($this->table, $data);
    }

    /**
     * 更新
     *
     * @param string $id
     * @param array $data
     *
     * @return bool
     */
    public function update($id, array $data) {
        $condition = array('id' => $id);

        return self::db()->update($this->table, $condition, $data);
    }

}
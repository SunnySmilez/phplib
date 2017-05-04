<?php
namespace Modules\Admin\Model\Dao\Db\Acl;

/**
 * Class All
 *
 * @package     Modules\Admin\Model\Dao\Db\Acl
 * @description 全局权限表
 */
class All extends \Modules\Admin\Model\Dao\Db\Db {

    private $table;

    public function __construct() {
        $this->table = self::table('acl_all');
    }

    /**
     * 获取全局权限权限
     *
     * @return array
     */
    public function getList() {
        return self::db()->query($this->table, array(), array());
    }

    /**
     * 新增全局权限
     *
     * @param array $data 权限数据
     *
     * @return boolean
     */
    public function add($data) {
        foreach ($data as $acl) {
            unset($acl['option'], $acl['id']);
            self::db()->insert($this->table, $acl);
        }

        return true;
    }

    /**
     * 删除全局权限
     *
     * @param array $data 权限数据
     *
     * @return boolean
     */
    public function del($data) {
        foreach ($data as $acl) {
            unset($acl['option'], $acl['id']);
            self::db()->delete($this->table, $acl);
        }

        return true;
    }

}
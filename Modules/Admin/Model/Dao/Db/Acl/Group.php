<?php
namespace Modules\Admin\Model\Dao\Db\Acl;

/**
 * Class Group
 *
 * @package Modules\Admin\Model\Dao\Db\Acl
 * @description 组权限表
 */
class Group extends \Modules\Admin\Model\Dao\Db\Db {

    private $table;

    public function __construct() {
        $this->table = self::table('acl_group');
    }

    /**
     * 获取组所有权限信息
     * @param integer|array $gid 组id
     * @return array
     */
    public function getList($gid) {
        return self::db()->query($this->table, array('gid' => $gid));
    }

    /**
     * 新增用户组权限
     * @param array $data 权限数据
     * @return boolean
     */
    public function add($data) {
        foreach ($data as $acl) {
            self::db()->insert($this->table, $acl);
        }
        return true;
    }

    /**
     * 删除用户组权限
     * @param array $data 权限数据
     * @return boolean
     */
    public function del($data) {
        foreach ($data as $acl) {
            unset($acl['option']);
            self::db()->delete($this->table, $acl);
        }
        return true;
    }
}
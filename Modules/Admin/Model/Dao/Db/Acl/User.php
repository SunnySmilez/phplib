<?php
namespace Modules\Admin\Model\Dao\Db\Acl;

/**
 * Class User
 *
 * @package Modules\Admin\Model\Dao\Db\Acl
 * @description 用户权限表
 */
class User extends \Modules\Admin\Model\Dao\Db\Db {

    private $table;

    public function __construct() {
        $this->table = self::table('acl_user');
    }

    /**
     * 获取用户所有权限信息
     * @param integer $uid 用户id
     * @return array
     */
    public function getList($uid) {
        return self::db()->query($this->table, array('uid' => $uid), array());
    }

    /**
     * 新增用户权限
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
     * 删除用户权限
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
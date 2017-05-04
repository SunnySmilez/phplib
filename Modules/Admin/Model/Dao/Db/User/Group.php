<?php
namespace Modules\Admin\Model\Dao\Db\User;

/**
 * Class Group
 *
 * @package Modules\Admin\Model\Dao\Db\User
 * @description 用户组成员表
 */
class Group extends \Modules\Admin\Model\Dao\Db\Db {

    private $table;

    public function __construct() {
        $this->table = self::table('user_group');
    }

    /**
     * 通过组id获取组成员
     *
     * @param integer $gid
     *
     * @return array
     */
    public function getUidByGid($gid) {
        $user = self::db()->query($this->table, array('gid' => $gid), array('uid'));
        $uid  = array_column($user, 'uid') ?: array();

        return $uid;
    }

    /**
     * 通过用户id获取组id
     *
     * @param integer $uid
     *
     * @return array
     */
    public function getGidByUid($uid) {
        $group = self::db()->query($this->table, array('uid' => $uid), array('gid'));
        $gid   = array_column($group, 'gid') ?: array();

        return $gid;
    }


    /**
     * 新增用户组关系
     *
     * @param integer $gid 组id
     * @param integer $uid 用户id
     *
     * @return bool
     */
    public function add($gid, $uid) {
        return self::db()->insert($this->table, array('gid' => $gid, 'uid' => $uid));
    }

    /**
     * 通过用户gid删除所有组关系
     *
     * @param $gid
     *
     * @return bool|int
     * @throws \S\Exception
     */
    public function delByGid($gid) {
        return self::db()->delete($this->table, array('gid' => $gid));
    }

    /**
     * 删除用户组关系
     *
     * @param integer $gid 组id
     * @param integer $uid 用户id
     *
     * @return bool
     */
    public function delByGidUid($gid, $uid) {
        return self::db()->delete($this->table, array('uid' => $uid, 'gid' => $gid));
    }

    /**
     * 通过用户id删除所有组关系
     *
     * @param integer $id
     *
     * @return boolean
     */
    public function delById($id) {
        return self::db()->delete($this->table, array('uid' => $id));
    }

}
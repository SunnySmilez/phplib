<?php
namespace Modules\Admin\Model\Dao\Db\User;

/**
 * Class Groups
 *
 * @package Modules\Admin\Model\Dao\Db\User
 * @description 用户组表
 */
class Groups extends \Modules\Admin\Model\Dao\Db\Db {
    private $table;

    public function __construct() {
        $this->table = self::table('user_groups');
    }

    /**
     * 通过id获取一条组信息
     * @param integer $gid 组gid
     * @return array
     */
    public function getInfoById($gid) {
        return self::db()->queryone($this->table, array('gid' => $gid));
    }

    /**
     * 获取所有组信息
     *
     * @param null $gname
     *
     * @return array
     */
    public function getList($gname = null) {
        $params = array();
        if ($gname) {
            $params['gname'] = $gname;
        }

        return self::db()->query($this->table, $params, array(), array('ctime' => 'ASC'));
    }

    /**
     * 通过组id删除组及相关信息
     * @param integer $gid
     * @return boolean
     */
    public function delByGid($gid) {
        return self::db()->delete($this->table, array('gid' => $gid));
    }

    /**
     * 保存组信息
     * @param null $id 组id
     * @param array   $info 组信息
     * @return boolean
     */
    public function save($info, $id = null) {
        if ($id) {
            return self::db()->update($this->table, $info, array('gid' => $id));
        } else {
            return self::db()->insert($this->table, $info);
        }
    }

    /**
     * 校验组名是否存在
     * @param string $name 组名
     * @return bool
     */
    public function isExistsByName($name) {
        return (bool)self::db()->queryone($this->table, array('gname' => $name));
    }

}
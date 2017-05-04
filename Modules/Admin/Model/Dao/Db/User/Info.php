<?php
namespace Modules\Admin\Model\Dao\Db\User;

/**
 * Class Info
 *
 * @package Modules\Admin\Model\Dao\Db\User
 * @description 用户信息表
 */
class Info extends \Modules\Admin\Model\Dao\Db\Db {

    const PASSWORD_LDAP = 'ldap';

    protected $table;

    public function __construct() {
        $this->table = self::table('user_info');
    }

    /**
     * 通过id获取一条用户信息
     *
     * @param integer|array $uid 用户uid
     *
     * @return array
     */
    public function getInfoByUid($uid) {
        return self::db()->queryone($this->table, array('uid' => $uid));
    }

    /**
     * 通过id获取用户信息
     *
     * @param integer|array $uid 用户uid
     *
     * @return array
     */
    public function getListByUid($uid) {
        return self::db()->query($this->table, array('uid' => $uid));
    }

    /**
     * 获取所有用户信息
     *
     * @param null $uname
     *
     * @return array
     */
    public function getList($uname = null) {
        $params = array();
        if ($uname) {
            $params['uname'] = $uname;
        }

        return self::db()->query($this->table, $params, array(), array('ctime' => 'ASC'));
    }

    /**
     * 通过用户名获取用户信息
     *
     * @param string $name 用户名
     *
     * @return array
     */
    public function getInfoByName($name) {
        return self::db()->queryone($this->table, array('uname' => $name));
    }

    /**
     * 校验用户名是否存在
     *
     * @param string $name 用户名
     *
     * @return bool
     */
    public function isExistsByName($name) {
        return (bool)self::db()->queryone($this->table, array('uname' => $name));
    }

    /**
     * 保存用户信息
     *
     * @param array        $info 用户信息
     * @param integer|null $uid  用户uid
     *
     * @return boolean
     */
    public function save($info, $uid = null) {
        if ($uid) {
            return self::db()->update($this->table, $info, array('uid' => $uid));
        } else {
            return self::db()->insert($this->table, $info);
        }
    }

}
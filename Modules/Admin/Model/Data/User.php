<?php
namespace Modules\Admin\Model\Data;

use Base\Exception\Data as Exception;
use Modules\Admin\Model\Dao\Db\User\Groups as DbGroups;
use Modules\Admin\Model\Dao\Db\User\Info as DbUserInfo;
use Modules\Admin\Model\Dao\Db\User\Group as DbUserGroup;

/**
 * Class User
 *
 * @package Modules\Admin\Model\Data
 * @description 用户管理
 */
class User {

    /**
     * 获取所有用户信息
     *
     * @param null $uname
     *
     * @return array
     */
    public function getUserList($uname = null) {
        $user_list = (new DbUserInfo())->getList($uname);

        return $user_list;
    }

    /**
     * 根据用户名取用户信息
     *
     * @param $name
     *
     * @return array
     */
    public function getUserInfoByName($name) {
        $info = (new DbUserInfo())->getInfoByName($name);

        return $info;
    }

    /**
     * 通过id获取一条用户信息
     *
     * @param integer $uid 用户uid
     *
     * @return array
     */
    public function getUserInfoById($uid) {
        $info = (new DbUserInfo())->getInfoByUid($uid);

        return $info;
    }

    /**
     * 通过组id获取组成员
     *
     * @param integer $gid 组gid
     *
     * @return array
     */
    public function getUserByGid($gid) {
        $uid = (new DbUserGroup())->getUidByGid($gid);
        if ($uid) {
            return (new DbUserInfo())->getListByUid($uid);
        }

        return array();
    }

    /**
     * 获取所有组信息
     *
     * @param $gname
     *
     * @return array
     */
    public function getGroupList($gname = null) {
        $group_list = (new DbGroups())->getList($gname);

        return $group_list;
    }

    /**
     * 通过id获取一条组信息
     *
     * @param integer $gid 组gid
     *
     * @return array
     */
    public function getGroupOneById($gid) {
        $group_info = (new DbGroups())->getInfoById($gid);

        return $group_info;
    }

    /**
     * 根据$uid取用户所属组
     *
     * @param $uid
     *
     * @return array
     */
    public function getGroupByUid($uid) {
        $group_list = (new DbUserGroup())->getGidByUid($uid);

        return $group_list;
    }

    public function auth($email, $password_with_otp) {
        $result = (new \Modules\Admin\Model\Dao\Api\Auth\Auth())->auth($email, $password_with_otp);
        if ($result) {
            return true;
        } else {
            throw new Exception("error.admin.login_error");
        }
    }

    /**
     * 新增用户组关系
     *
     * @param int|array $gid 组id
     * @param int       $uid 用户id
     *
     * @return bool
     */
    public function addUserGroupRel($gid, $uid) {
        if (is_array($gid)) {
            $curr_gids = $this->getGroupByUid($uid);

            $add_gids = array_diff($gid, $curr_gids);
            if ($add_gids) {
                foreach ($add_gids as $add_gid) {
                    $this->addUserGroupRel($add_gid, $uid);
                }
            }

            $del_gids = array_diff($curr_gids, $gid);
            if ($del_gids) {
                foreach ($del_gids as $del_gid) {
                    $this->delUserGroupRel($del_gid, $uid);
                }
            }

            $ret = true;
        } else {
            $ret = (new DbUserGroup())->add($gid, $uid);
        }

        return $ret;
    }

    /**
     * 通过组id删除组及相关信息
     *
     * @param integer $gid
     *
     * @return boolean
     */
    public function delGroupOneById($gid) {
        $ret1 = (new DbGroups())->delByGid($gid);
        $ret2 = (new DbUserGroup())->delByGid($gid);

        return $ret1 && $ret2;
    }

    /**
     * 删除用户组关系
     *
     * @param integer $gid 组id
     * @param integer $uid 用户id
     *
     * @return bool
     */
    public function delUserGroupRel($gid, $uid) {
        return (new DbUserGroup())->delByGidUid($gid, $uid);
    }

    /**
     * 通过用户id删除所有组关系
     *
     * @param integer $id
     *
     * @return boolean
     */
    public function delGroupRelById($id) {
        return (new DbUserGroup())->delById($id);
    }

    /**
     * 校验用户名是否存在
     *
     * @param string $name 用户名
     *
     * @return bool
     */
    public function isExistsByUserName($name) {
        $ret = (new DbUserInfo())->isExistsByName($name);

        return $ret;
    }

    /**
     * 校验用户组名是否存在
     *
     * @param string $name 用户名
     *
     * @return bool
     */
    public function isExistsByGroupName($name) {
        $ret = (new DbGroups())->isExistsByName($name);

        return $ret;
    }

    /**
     * 保存组信息
     *
     * @param null  $id   组id
     * @param array $info 组信息
     *
     * @return boolean
     */
    public function saveGroupInfo($info, $id = null) {
        return (new DbGroups())->save($info, $id);
    }

    /**
     * 保存用户信息
     *
     * @param      $info
     * @param null $uid
     *
     * @return bool
     */
    public function saveUserInfo($info, $uid = null) {
        $ret = (new DbUserInfo())->save($info, $uid);

        return $ret;
    }

    /**
     * 更新用户状态
     *
     * @param $uid
     * @param $status
     *
     * @return bool
     */
    public function updateUserStatus($uid, $status) {
        $user_info = array(
            'status' => $status,
        );

        return (new DbUserInfo())->save($user_info, $uid);
    }

    /**
     * 根据$uid取用户信息
     *
     * @param $uid
     *
     * @return array
     */
    public function getUserInfo($uid) {
        if ($uid) {
            $info = $this->getUserInfoById($uid);
            if ($info['otp_secret']) {
                $info['verify'] = 1;
            } else {
                $info['verify'] = 0;
            }
        } else {
            $info = array();
        }

        return $info;
    }

}
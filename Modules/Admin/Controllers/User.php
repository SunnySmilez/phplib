<?php
namespace Modules\Admin\Controllers;

use S\Request;
use Base\Exception\Controller as Exception;
use Modules\Admin\Model\Data\User as DataUser;
use Modules\Admin\Model\Service\User as ServiceUser;

/**
 * @用户管理
 */
class User extends Base {

    protected $sys_view = true;

    /**
     * @name 用户列表
     */
    public function indexAction() {
    }

    public function queryUserListAction() {
        $uname = $this->getParams('uname');

        $this->response['data'] = (new DataUser())->getUserList($uname);
    }

    /**
     * @name 编辑用户信息
     */
    public function editUserInfoAction() {
        $uid = intval($this->getParams('uid'));

        $data_user = new DataUser();
        $user_info = ($uid ? $data_user->getUserInfo($uid) : array());

        $this->response           = $user_info;
        $this->response['group']  = ($uid ? $data_user->getGroupByUid($uid) : array());
        $this->response['groups'] = $data_user->getGroupList();
    }

    /**
     * @name 校验用户名是否存在
     * @throws \Base\Exception\Controller
     */
    public function checkNameExistAction() {
        $name = $this->getParams('name');

        if ((new DataUser())->isExistsByUserName($name)) {
            $error_config = \S\Config::confError('admin.user_already_exists');
            $msg          = sprintf($error_config['msg'], $name);
            throw new Exception($msg, $error_config['retcode']);
        }
    }

    /**
     * @name 保存用户信息
     * @throws Exception
     * @throws \Base\Exception\Controller
     */
    public function saveUserInfoAction() {
        $uid         = (int)$this->getParams('uid');
        $status      = (int)$this->getParams('status') ?: 0;
        $isadmin     = (int)$this->getParams('isadmin');
        $uname       = $this->getParams('uname');
        $phone       = $this->getParams('phone');
        $email       = $this->getParams('email');
        $nick        = $this->getParams('nick');
        $password    = $this->getParams('password');
        $description = $this->getParams('description');
        $gids        = \S\Request::post('group');

        if (!preg_match('/^[.\w]{3,20}$/', $uname)) {
            throw new Exception('error.admin.name_of_the_correct_format');
        }

        $data_user = new DataUser();
        $user_info = $data_user->getUserInfoById($uid);

        if (($user_info && $user_info['uname'] != $uname || empty($user_info)) && $data_user->isExistsByUserName($uname)) {
            $error_config = \S\Config::confError('admin.user_already_exists');
            $msg          = sprintf($error_config['msg'], $uname);
            throw new Exception($msg, $error_config['retcode']);
        }
        if (strlen($nick) > 30 || empty($nick)) {
            throw new Exception('error.admin.name_of_the_correct_format');
        }
        if (!preg_match('/^1[345789]\d{9}$/', $phone)) {
            throw new Exception('error.admin.phone_of_the_correct_format');
        }
        if ($password && !preg_match('/^[a-zA-Z0-9.-_@`=;,.~!@#$%^*()+}{:?]{7,32}$/', $password)) {
            throw new Exception('error.admin.password_of_the_correct_format');
        }

        $user_info = array(
            'phone'       => $phone,
            'email'       => $email,
            'nick'        => $nick,
            'isadmin'     => $isadmin,
            'status'      => $status,
            'uname'       => $uname,
            'description' => $description,
        );
        if ($password) {
            $user_info['password'] = $password;
        }
        (new ServiceUser())->saveUserInfo($uid, $user_info);

        if ($gids) {
            $data_user->addUserGroupRel($gids, $uid);
        } else {
            $data_user->delGroupRelById($uid);
        }
    }

    /**
     * @name 禁用 /解禁用户
     * @throws Exception
     */
    public function setBanAction() {
        $isban = (int)$this->getParams('isban');
        $uid   = (int)$this->getParams('uid');
        if ($uid == Request::session('uid')) {
            throw new Exception('error.admin.can_not_disable_yourself');
        }

        (new DataUser())->updateUserStatus($uid, $isban);
        $this->response['msg'] = '设置成功';
    }

}

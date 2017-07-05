<?php
namespace Modules\Admin\Controllers;

use Modules\Admin\Model\Data\User as DataUser;
use Modules\Admin\Model\Service\User as ServiceUser;
use Modules\Admin\Model\Data\Acl as DataAcl;
use Base\Exception\Controller as Exception;

/**
 * @name 管理系统登录
 */
class Login extends Base {

    protected $sys_view = true;

    /**
     * @name 登录
     * @throws Exception
     */
    public function loginAction() {
        $username = $this->getParams("username");
        $password = $this->getParams("password");

        $user_info = (new ServiceUser())->loginVerify($username, $password);

        $_SESSION = array(
            'uid'        => $user_info['uid'],
            'uname'      => $user_info['uname'],
            'name'       => $user_info['nick'],
            'actionlist' => (new DataAcl())->getUserAclData($user_info['uid']),
            'groups'     => (new DataUser())->getGroupByUid($user_info['uid']),
            'isadmin'    => $user_info['isadmin'] ? true : false, // 超级管理员标识
            'blocked'    => $user_info['status'] ? false : true, // 禁用状态
        );

        $this->response['is_admin'] = $_SESSION['isadmin'];

        if ($user_info['isadmin']) {
            $this->response['is_admin_user_banned'] = (new DataUser())->getUserInfoByName('admin')['status'];
        }
    }

    /**
     * @name 登录页
     * @format html
     */
    public function indexAction() {
        if (\S\Request::session('uid')) {     //判断是否有session值,如果存在则跳转到后台欢迎页面
            header('Location:' . APP_ADMIN_PATH . '/welcome/index');

            return;
        }
        $refer = \S\Request::server('HTTP_REFERER');
        if (strpos($refer, APP_ADMIN_PATH)) {
            $this->response['refer'] = $refer;
        }
    }

    /**
     * @name 登出
     */
    public function logoutAction() {
        session_destroy();
        setcookie('menu', '', time() - 1, APP_ADMIN_PATH);
        header('Location: ' . APP_ADMIN_PATH . '/login/index');
    }

}

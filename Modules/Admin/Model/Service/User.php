<?php
namespace Modules\Admin\Model\Service;

use Modules\Admin\Model\Data\User as DataUser;
use Base\Exception\Service as Exception;

class User {

    /**
     * 保存用户信息
     *
     * @param $uid
     * @param $phone
     * @param $nick
     * @param $isadmin
     * @param $status
     * @param $uname
     * @param $description
     * @param $email
     *
     * @return bool
     * @throws Exception
     */
    public function saveUserInfo($uid, $phone, $nick, $isadmin, $status, $uname, $description, $email) {
        $data = array(
            'phone'       => $phone,
            'email'       => $email,
            'nick'        => $nick,
            'isadmin'     => $isadmin,
            'status'      => $status,
            'uname'       => $uname,
            'description' => $description,
        );

        return (new DataUser)->saveUserInfo($data, $uid);
    }

    /**
     * 验证账号密码
     *
     * @param string $username 用户名
     * @param string $password 密码
     *
     * @return array
     * @throws Exception
     * @throws \Exception
     */
    public function loginVerify($username, $password) {
        $user_info = (new DataUser())->getUserInfoByName($username);
        if (!$user_info) {
            throw new Exception('error.admin.login_error');
        }
        if ($user_info['status']) {
            throw new Exception('error.admin.banned_from');
        }

        //初始化账号直接校验密码
        if ('admin' == $username) {
            if ($user_info['password'] !== sha1(sha1($password) . $user_info['salt'])) {
                throw new Exception('error.admin.login_error');
            }
        } else {
            $email = $user_info['email'];

            (new \Modules\Admin\Model\Data\User())->auth($email, $password);
        }

        return $user_info;
    }

}
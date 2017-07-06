<?php
namespace Modules\Admin\Model\Service;

use Modules\Admin\Model\Data\User as DataUser;
use Base\Exception\Service as Exception;

class User {

    const AUTH_TYPE_LOCAL = 1;
    const AUTH_TYPE_REMOTE_AUTH = 2;

    /**
     * 保存用户信息
     *
     * @param       $uid
     * @param array $user_info
     * @param       $auth_type
     *
     * @return bool
     */
    public function saveUserInfo($uid, array $user_info, $auth_type) {
        $data_user = new DataUser();

        if (self::AUTH_TYPE_REMOTE_AUTH == $auth_type) {
            $email          = $user_info['email'];
            $auth_user_info = $data_user->getUserInfoFromAuth($email);
            $detail         = array(
                'uname' => $email,
                'nick'  => $auth_user_info['name'],
                'phone' => $auth_user_info['mobile'],
            );

            $user_info = array_merge($user_info, $detail);
        } else if ($user_info['password']) {
            $salt     = crc32($user_info['uname']);
            $password = self::_getEncryptPassword($user_info['password'], $salt);

            $user_info['salt']     = $salt;
            $user_info['password'] = $password;

            $otp_secret = (new DataUser())->getUserInfoByName($user_info['uname'])['otp_secret'];
            if (!$otp_secret) {
                $otp_secret = (new \Modules\Admin\Model\Util\Otp())->createSecret();

                $user_info['otp_secret'] = $otp_secret;

                self::_sendAuthMail($user_info['uname'], $user_info['email'], $otp_secret);
            }
        }

        return $data_user->saveUserInfo($user_info, $uid);
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
            if ($user_info['password'] !== self::_getEncryptPassword($password, $user_info['salt'])) {
                throw new Exception('error.admin.login_error');
            }
        } else {
            $auth_type = (false === strpos($username, '@') ? self::AUTH_TYPE_LOCAL : self::AUTH_TYPE_REMOTE_AUTH);

            if (self::AUTH_TYPE_LOCAL == $auth_type) {
                $password_with_otp = $password;

                $otp_code = substr($password_with_otp, -6);
                $password = substr($password_with_otp, 0, -6);

                $otp_result = (new \Modules\Admin\Model\Util\Otp())->verifyCode($user_info['otp_secret'], $otp_code, 2);
                if (!$otp_result) {
                    throw new Exception('error.admin.login_error');
                }
                if ($user_info['password'] !== self::_getEncryptPassword($password, $user_info['salt'])) {
                    throw new Exception('error.admin.login_error');
                }
            } else {
                $email = $user_info['email'];

                (new DataUser())->auth($email, $password);
            }
        }

        return $user_info;
    }

    private static function _getEncryptPassword($password, $salt) {
        return sha1(sha1($password) . $salt);
    }

    /**
     * 给用户发送密钥信息的邮件
     *
     * @param string $uid        用户uid
     * @param string $name       用户名
     * @param string $email      邮箱
     * @param string $otp_secret otp密钥
     *
     * @return bool
     */
    private static function _sendAuthMail($name, $email, $otp_secret) {
        $company                 = "jrmf360";
        $otp_url                 = sprintf("otpauth://totp/%s:%s?secret=%s&issuer=%s", $company, $name, $otp_secret, $company);
        $base64_otp              = self::_getBase64QRCode($otp_url);
        $base64_ios_download     = self::_getBase64QRCode('https://itunes.apple.com/cn/app/google-authenticator/id388497605');
        $base64_android_download = self::_getBase64QRCode(ADMIN_STATIC_PATH . '/build/app/google_authenticator_4.60.apk');

        $yaf_view = new \Yaf\View\Simple(PHPLIB . '/Modules/Admin/Views/', array());
        $view     = $yaf_view->render("user/otpinstall.phtml", array(
            'username'                => $name,
            'otp_secret'              => $otp_secret,
            'base64_otp'              => $base64_otp,
            'base64_ios_download'     => $base64_ios_download,
            'base64_android_download' => $base64_android_download,
        ));

        return (new \S\Msg\Mail('admin.otp'))->send($email, '开通统一认证账号【重要】', $view);
    }

    private static function _getBase64QRCode($url) {
        ob_start();
        \QRcode::png($url);
        $content = ob_get_clean();
        ob_end_clean();

        return 'data:image/png;base64,' . base64_encode($content);
    }

}
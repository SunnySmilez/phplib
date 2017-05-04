<?php
namespace Modules\Admin\Model\Dao\Api\Auth;

use Base\Exception\Dao as Exception;

/**
 * Class Auth
 *
 * @package Dao\Api\Auth
 */
class Auth {

    const PATH_AUTH = "api/auth";

    const ERR_CODE_SUCC = '2000000';

    public function auth($email, $password_with_otp) {
        $params = array(
            'email' => $email,
            'password' => $password_with_otp,
            'is_otp' => 1,
        );

        $resp_data = self::_request(self::PATH_AUTH, $params);

        return !empty($resp_data['user_info']);
    }

    private static function _request($path, array $params) {
        $resp_json = (new \S\Http(AUTH_BASE_URL))->request(\S\Http::METHOD_POST, $path, $params);
        $resp_data = json_decode($resp_json, true);

        if (empty($resp_data['retcode']) || self::ERR_CODE_SUCC != $resp_data['retcode']) {
            throw new Exception($resp_data['msg'], $resp_data['retcode']);
        }

        return $resp_data;
    }

}
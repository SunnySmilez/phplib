<?php
namespace Wechat;

/**
 * Class User
 *
 * @package     Wechat
 * @description 微信用户
 */
class User {

    const PATH_GET_USER_INFO = 'user/info';

    private $_access_token;

    public function __construct($access_token) {
        $this->_access_token = $access_token;
    }

    /**
     * 获取关注者详细信息
     *
     * @param string $openid
     *
     * @return array
     */
    public function getUserInfo($openid) {
        $data = array(
            'openid' => $openid,
            'lang' => 'zh_CN',
        );

        $user_info = Util::request(self::PATH_GET_USER_INFO, $data, $this->_access_token, \S\Http::METHOD_GET);
        if ($user_info) {
            $user_info['subscribe_time'] = date('Y-m-d H:i:s', $user_info['subscribe_time']);
        }

        return $user_info;
    }

}
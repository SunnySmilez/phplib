<?php
namespace Wechat;

/**
 * Class Oauth
 *
 * @package     Wechat
 * @description Oauth授权
 */
class Oauth {

    const URL_OAUTH_BASE = 'https://open.weixin.qq.com/connect/oauth2';
    const PATH_OAUTH_AUTHORIZE = 'authorize';

    const URL_OAUTH_TOKEN_BASE = 'https://api.weixin.qq.com/sns';
    const PATH_OAUTH_GET_ACCESS_TOKEN = 'oauth2/access_token';
    const PATH_OAUTH_GET_REFRESH_TOKEN = 'oauth2/refresh_token';
    const PATH_OAUTH_GET_USER_INFO = 'userinfo';
    const PATH_OAUTH_AUTH = 'auth';

    const SCOPE_BASE = 'snsapi_base';
    const SCOPE_USERINFO = 'snsapi_userinfo';

    /**
     * 获取oauth访问的地址
     *
     * @param string $appid
     * @param        $url
     * @param string $state
     * @param string $scope
     *
     * @return string
     */
    public static function getOauthUrl($appid, $url, $state = '', $scope = self::SCOPE_BASE) {
        $query = array(
            'appid'         => $appid,
            'redirect_uri'  => $url,
            'response_type' => 'code',
            'scope'         => $scope,
            'state'         => $state,
        );

        $url = self::URL_OAUTH_BASE . '/' . self::PATH_OAUTH_AUTHORIZE . '?' . http_build_query($query) . '#wechat_redirect';

        return $url;
    }

    /**
     * 通过code获取access_token
     *
     * @param $appid
     * @param $appsecret
     * @param $code
     *
     * @return array
     * @throws Exception
     */
    public function getAccessToken($appid, $appsecret, $code) {
        $url  = self::URL_OAUTH_TOKEN_BASE . '/' . self::PATH_OAUTH_GET_ACCESS_TOKEN;
        $data = array(
            'appid'      => $appid,
            'secret'     => $appsecret,
            'code'       => $code,
            'grant_type' => 'authorization_code',
        );

        return Util::request($url, $data, '', \S\Http::METHOD_GET);
    }

    /**
     * 刷新access token并续期
     *
     * @param string $refresh_token
     *
     * @return boolean|mixed
     */
    public function getRefreshToken($appid, $refresh_token) {
        $url  = self::URL_OAUTH_TOKEN_BASE . '/' . self::PATH_OAUTH_GET_REFRESH_TOKEN;
        $data = array(
            'appid'         => $appid,
            'grant_type'    => 'refresh_token',
            'refresh_token' => $refresh_token,
        );

        return Util::request($url, $data, '', \S\Http::METHOD_GET);
    }

    /**
     * 获取授权后的用户资料
     *
     * @param        $access_token
     * @param        $openid
     * @param string $lang
     *
     * @return bool|mixed
     * {openid,nickname,sex,province,city,country,headimgurl,privilege,[unionid]}
     * 注意：unionid字段 只有在用户将公众号绑定到微信开放平台账号后，才会出现。建议调用前用isset()检测一下
     */
    public function getUserInfo($access_token, $openid, $lang = 'zh_CN') {
        $url  = self::URL_OAUTH_TOKEN_BASE . '/' . self::PATH_OAUTH_GET_USER_INFO;
        $data = array(
            'access_token' => $access_token,
            'openid'       => $openid,
            'lang'         => $lang,
        );

        return Util::request($url, $data, '', \S\Http::METHOD_GET);
    }

    /**
     * 检验授权凭证是否有效
     *
     * @param string $access_token
     * @param string $openid
     *
     * @return boolean 是否有效
     */
    public function isValidAccessToken($access_token, $openid) {
        $url    = self::URL_OAUTH_TOKEN_BASE . '/' . self::PATH_OAUTH_AUTH;
        $data   = array(
            'openid'       => $openid,
            'access_token' => $access_token,
        );
        $result = Util::request($url, $data, '', \S\Http::METHOD_GET);

        return ($result ? true : false);
    }

}
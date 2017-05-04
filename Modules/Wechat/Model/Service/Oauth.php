<?php
namespace Modules\Wechat\Model\Service;

use Modules\Wechat\Model\Data\Config as DataConfig;

/**
 * Class Oauth
 *
 * @package     Modules\Wechat\Model\Service
 * @description Oauth授权服务
 */
class Oauth {

    const DEFAULT_OPENID_COOKIE_NAME_PREFIX = 'WECHAT_OPENID_';
    const DEFAULT_OPENID_COOKIE_TTL = 2592000;  // 默认cookie有效期一个月

    /**
     * @var string 微信公众号名称, 在modules\Wechat\Bootstrap中定义
     */
    private $_wechat_name;
    /**
     * @var array 微信公众号配置
     */
    private $_config;

    /**
     * Oauth constructor.
     *
     * @param string $wechat_name 微信公众号名称, 在modules\Wechat\Bootstrap中定义
     */
    public function __construct($wechat_name) {
        $this->_wechat_name = $wechat_name;
        $this->_config      = DataConfig::getBaseConfig($wechat_name);
    }

    /**
     * 获取oauth访问的地址
     *
     * @param string $url   授权后重定向的回调链接地址, 域名需要预先在公众号管理后台注册
     * @param string $state 重定向后会带上state参数，开发者可以填写a-zA-Z0-9的参数值，最多128字节
     * @param string $scope snsapi_userinfo （弹出授权页面，可通过openid拿到昵称、性别、所在地。并且，即使在未关注的情况下，只要用户授权，也能获取其信息）
     *
     * @return string
     */
    public function getOauthUrl($url, $state = '', $scope = \Wechat\Oauth::SCOPE_BASE) {
        $appid = $this->_config['appid'];

        return \Wechat\Oauth::getOauthUrl($appid, $url, $state, $scope);
    }

    /**
     * 授权获取用户信息
     *
     * @param string $scope_type
     *
     * @return string|array|bool 成功返回openid(scope_type=snsapi_base)或用户信息数组(scope_type=snsapi_userinfo)
     *                           失败返回false
     */
    public function auth($scope_type = \Wechat\Oauth::SCOPE_BASE) {
        $openid = $this->getOpenidFromCookie();
        if ($openid) {
            return $openid;
        }

        $code = \S\Request::get('code');
        if ($code) {
            $user_info = $this->getUserInfoByAuth($code, $scope_type);
            if (!$user_info) {
                return false;
            }

            $openid = (\Wechat\Oauth::SCOPE_BASE == $scope_type ? $user_info : $user_info['openid']);
            $this->setCookie($openid);

            return $user_info;
        } else {
            if (\S\Request::isAjax()) {
                $oauth_url = $this->getOauthUrl(APP_HOST_URL, '', $scope_type);
                \S\Response::outJson(3020000, '', array('url' => $oauth_url));
            } else {
                $oauth_url = $this->getOauthUrl(APP_HOST_URL . $_SERVER['REQUEST_URI'], '', $scope_type);
                header("Location:{$oauth_url}");
            }
            exit();
        }
    }

    /**
     * 通过oauth授权获得用户信息
     *
     * @param $code
     * @param $scope_type
     *
     * @return string|array|bool
     */
    protected function getUserInfoByAuth($code, $scope_type) {
        $util_oauth = new \Wechat\Oauth();

        $appid     = $this->_config['appid'];
        $appsecret = $this->_config['appsecret'];
        try {
            $access_token_data = $util_oauth->getAccessToken($appid, $appsecret, $code);
            $openid       = $access_token_data['openid'];
            $access_token = $access_token_data['access_token'];

            if (\Wechat\Oauth::SCOPE_USERINFO == $scope_type) {
                return (new \Wechat\Oauth())->getUserInfo($access_token, $openid);
            } else {
                return $openid;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 从cookie中获取openid
     *
     * @return string|bool
     */
    protected function getOpenidFromCookie() {
        $name      = $this->getOpenidCookieName();
        $openid = \S\Request::cookie($name);
        if ($openid) {
            return \S\Crypt\Aes::decrypt(urldecode($openid));
        }

        return false;
    }

    /**
     * 将openid存入cookie中
     *
     * @param     $openid
     * @param int $ttl
     *
     * @return bool
     */
    protected function setCookie($openid, $ttl = self::DEFAULT_OPENID_COOKIE_TTL) {
        $name      = $this->getOpenidCookieName();
        $value     = urlencode(\S\Crypt\Aes::encrypt($openid));
        $expire_at = time() + $ttl;
        $path      = '/';
        $domain    = APP_DOMAIN;
        $http_only = true;
        $secure    = \S\Request::isHttps();

        return setcookie($name, $value, $expire_at, $path, $domain, $secure, $http_only);
    }

    /**
     * 获取openid在cookie中的名称
     *
     * @return string
     */
    protected function getOpenidCookieName() {
        return static::DEFAULT_OPENID_COOKIE_NAME_PREFIX . $this->_wechat_name;
    }

}
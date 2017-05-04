<?php
namespace Modules\Wechat\Model\Data;

use Modules\Wechat\Model\Dao\Cache\Config as CacheConfig;

/**
 * Class Config
 *
 * @package Modules\Wechat\Model\Data
 * @description 微信公众号配置
 */
class Config {

    /**
     * access_token列表
     *
     * @var array
     *
     * 格式:
     * array(
     *     wechat_name => token,
     *     ...
     * )
     */
    private static $_access_tokens = array();
    /**
     * js_api_ticekt列表
     *
     * @var array
     *
     * 格式:
     * array(
     *     wechat_name => js_api_ticket,
     *     ...
     * )
     */
    private static $_js_api_tickets = array();

    /**
     * 根据微信公众号名称获取相应基础配置
     *
     * @param string $wechat_name 微信公众号名称
     *
     * @return array
     */
    public static function getBaseConfig($wechat_name = '') {
        return \S\Config::confServer("wechat" . ($wechat_name ? '.' . $wechat_name : ''));
    }

    /**
     * 获取access_token
     *
     * @param $wechat_name
     *
     * @return string
     */
    public function getAccessToken($wechat_name) {
        if (!self::$_access_tokens[$wechat_name]) {
            $token = (new CacheConfig())->getAccessToken($wechat_name);

            if ($token) {
                self::$_access_tokens[$wechat_name] = $token;
            }
        }

        return self::$_access_tokens[$wechat_name];
    }

    /**
     * 更新access_token
     *
     * @param $wechat_name
     *
     * @return bool
     */
    public function updateAccessToken($wechat_name) {
        $config    = \S\Config::confServer("wechat.{$wechat_name}");
        $appid     = $config['appid'];
        $appsecret = $config['appsecret'];

        $response_data = \Wechat\Config::getAccessToken($appid, $appsecret);
        $token         = $response_data['access_token'];

        if ($token) {
            (new CacheConfig())->setAccessToken($wechat_name, $token);
            self::$_access_tokens[$wechat_name] = $token;

            return $token;
        }

        return false;
    }

    /**
     * 获取js_api_ticket
     *
     * @param $wechat_name
     *
     * @return string
     */
    public function getJsApiTicket($wechat_name) {
        if (!self::$_js_api_tickets[$wechat_name]) {
            $js_api_ticket = (new CacheConfig())->getJsApiTicket($wechat_name);

            if ($js_api_ticket) {
                self::$_js_api_tickets[$wechat_name] = $js_api_ticket;
            }
        }

        return self::$_js_api_tickets[$wechat_name];
    }

    /**
     * 更新js_api_ticket
     *
     * @param $wechat_name
     *
     * @return bool
     */
    public function updateJsApiTicket($wechat_name) {
        $access_token  = self::getAccessToken($wechat_name);
        $response_data = \Wechat\Config::getJsApiTicket($access_token);
        $js_api_ticket = $response_data['ticket'];

        if ($js_api_ticket) {
            (new CacheConfig())->setJsApiTicket($wechat_name, $js_api_ticket);
            self::$_js_api_tickets[$wechat_name] = $js_api_ticket;

            return $js_api_ticket;
        }

        return false;
    }

    /**
     * 获取js配置信息
     *
     * 所有需要使用JS-SDK的页面必须先注入配置信息，否则将无法调用
     * 同一个url仅需调用一次，对于变化url的SPA的web app可在每次url变化时进行调用
     * 目前Android微信客户端不支持pushState的H5新特性，所以使用pushState来实现web app的页面会导致签名失败，此问题会在Android6.2中修复
     *
     * @param $wechat_name
     * @param $url
     *
     * @return array
     */
    public static function getJsConfig($wechat_name, $url) {
        $appid         = \S\Config::confServer("wechat.{$wechat_name}.appid");
        $js_api_ticket = self::getJsApiTicket($wechat_name);

        return \Wechat\Config::getJsConfig($appid, $js_api_ticket, $url);
    }

}
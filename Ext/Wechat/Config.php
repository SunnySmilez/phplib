<?php
namespace Wechat;

/**
 * Class BaseConfig
 *
 * @package     Wechat
 * @description 微信基础配置
 */
class Config {

    const URL_BASE = 'https://api.weixin.qq.com/cgi-bin';
    const PATH_GET_ACCESS_TOKEN = 'token';
    const URI_GET_JS_API_TICKET = 'ticket/getticket';  //获取jsapi_ticket
    const PATH_GET_SERVER_IP = "getcallbackip";  //获取微信服务器IP地址

    /**
     * 获取access_token
     *
     * @param $appid
     * @param $appsecret
     *
     * @return array
     */
    public static function getAccessToken($appid, $appsecret) {
        $request_data = array(
            'grant_type' => 'client_credential',
            'appid'      => $appid,
            'secret'     => $appsecret,
        );

        return \Wechat\Util::request(self::PATH_GET_ACCESS_TOKEN, $request_data, '', \S\Http::METHOD_GET);
    }

    /**
     * 获取js_api_ticket
     *
     * @param $access_token
     *
     * @return array
     */
    public static function getJsApiTicket($access_token) {
        $request_data = array(
            'type' => 'jsapi',
        );

        return \Wechat\Util::request(self::URI_GET_JS_API_TICKET, $request_data, $access_token, \S\Http::METHOD_GET);
    }

    /**
     * 获取js配置信息
     *
     * 所有需要使用JS-SDK的页面必须先注入配置信息，否则将无法调用
     * 同一个url仅需调用一次，对于变化url的SPA的web app可在每次url变化时进行调用
     * 目前Android微信客户端不支持pushState的H5新特性，所以使用pushState来实现web app的页面会导致签名失败，此问题会在Android6.2中修复
     *
     * @param $appid
     * @param $js_api_ticket
     * @param $url
     *
     * @return array
     */
    public static function getJsConfig($appid, $js_api_ticket, $url) {
        $timestamp = time();
        $noncestr  = self::_getJsConfigNoncestr();
        $sign      = self::_getJsConfigSignature($js_api_ticket, $url, $timestamp, $noncestr);

        return array(
            "appId"     => $appid,
            "timestamp" => $timestamp,
            "nonceStr"  => $noncestr,
            "signature" => $sign,
        );
    }

    /**
     * 生成签名字符串
     *
     * @param string $url       当前页面url
     * @param string $timestamp 时间戳
     * @param string $noncestr  随机字符串
     *
     * @return string signature
     */
    private static function _getJsConfigSignature($js_api_ticket, $url, $timestamp, $noncestr) {
        $data = array(
            "jsapi_ticket" => $js_api_ticket,
            "noncestr"     => $noncestr,
            "timestamp"    => $timestamp,
            "url"          => $url,
        );
        ksort($data);
        $string = "";
        foreach ($data as $key => $value) {
            $string .= "{$key}={$value}&";
        }

        return sha1(trim($string, '&'));
    }

    /**
     * 获取随机字符串
     *
     * @return string
     */
    private static function _getJsConfigNoncestr() {
        return md5(time() . mt_rand());
    }

}
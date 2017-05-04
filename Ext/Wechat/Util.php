<?php
namespace Wechat;

/**
 * Class Util
 *
 * @package     Wechat
 * @description 微信通用基础工具类
 */
class Util {

    const DEFAULT_TIMEOUT = 3000;  // 默认接口响应超时时间
    const SUCCESS_CODE = 0;  // 调用成功错误码

    /**
     * 对原文进行json序列化
     *
     * @param mixed $data 原文
     *
     * @return string
     */
    public static function json_encode($data) {
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 解决json_decode特殊字符问题
     * 如：地区信息为Ñl_ NSðl³，{"nickname":"火红玫瑰","city":"NSðl³","province":"Ñl_","country":""}
     * decode失败(JSON_ERROR_CTRL_CHAR)
     *
     * @param      $data
     * @param bool $bool
     *
     * @return mixed
     */
    public static function json_decode($data, $bool = true) {
        $de = json_decode($data, $bool);
        if ($de === null) {
            $data = preg_replace('/"(city|province|country)":"[^"]+"/', '"\\1":""', $data);
            $de   = json_decode($data, $bool);
            if ($de === null) {
                $data = preg_replace('/"nickname":"[^"]+"/', '"nickname":"UNKNOW_CHARS"', $data);
                $de   = json_decode($data, $bool);
            }
        }

        return $de;
    }

    /**
     * 将数组转成xml格式字符串
     *
     * @param array $data
     * @param int   $depth
     *
     * @return string
     */
    public static function toXML(array $data, $depth = 1) {
        $xml = $depth === 1 ? '<xml>' : '';
        foreach ($data as $tag => $value) {
            if (is_numeric($tag)) {
                if (isset($value['TagName'])) {
                    $tag = $value['TagName'];
                    unset($value['TagName']);
                } else {
                    $tag = 'item';
                }
            }
            if (is_array($value)) {
                $xml .= "<{$tag}>" . self::toXML($value, $depth + 1) . "</{$tag}>";
            } else {
                $xml .= "<{$tag}>" . (!is_numeric($value) ? '<![CDATA[' : '') . $value . (!is_numeric($value) ? ']]>' : '') . "</{$tag}>";
            }
        }
        $xml = preg_replace('/([\x01-\x08\x0b-\x0c\x0e-\x1f])+/', ' ', $xml);

        return $depth === 1 ? "{$xml}</xml>" : $xml;
    }

    /**
     * 解析xml
     *
     * @param $xml
     *
     * @return array
     * @throws Exception
     */
    public static function parseXML($xml) {
        $data = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        if (!$data) {
            throw new Exception('xml parse error' . str_replace(array("\n", "\r"), '', $xml), Exception::UNKNOW_ERROR);
        }

        return (array)$data;
    }

    /**
     * 获取微信服务器IP地址
     *
     * @document http://mp.weixin.qq.com/wiki/0/2ad4b6bfd29f30f71d39616c2a0fcedc.html
     */
    public static function getServerIpList() {
        return self::request(Config::PATH_GET_SERVER_IP, array(), true, \S\Http::METHOD_GET);
    }

    /**
     * 向微信服务器发送请求
     *
     * @param string $uri          请求uri, 默认根路径为: https://api.weixin.qq.com/cgi-bin
     * @param array  $data         请求数据(不包括access_token)
     * @param string $access_token 公众号的全局唯一接口调用凭据
     * @param string $http_method  请求方法, get|post, 默认使用post
     *
     * @return array
     * @throws Exception
     */
    public static function request($uri, $data = array(), $access_token = '', $http_method = \S\Http::METHOD_POST) {
        $url_data = parse_url($uri);

        if ($url_data['scheme'] && $url_data['host']) {
            $base_url = $url_data['scheme'] . '://' . $url_data['host'];
            $path     = $url_data['path'];
            $query    = $url_data['query'];
        } else {
            $default_url_data = parse_url(\Wechat\Config::URL_BASE);

            $base_url = $default_url_data['scheme'] . '://' . $default_url_data['host'];
            $path     = trim($default_url_data['path'] . '/' . $url_data['path'], '/');
            $query    = trim($default_url_data['query'] . '&' . $url_data['query'], '&');
        }

        if ($access_token) {
            $query .= trim('&access_token=' . $access_token, '&');
        }

        if (\S\Http::METHOD_GET == $http_method) {
            parse_str($query, $query_data);
            $data = array_merge($data, (array)$query_data);
        } else {
            $path .= ($query ? ('?' . $query) : '');
        }

        $options = array(
            'timeout' => self::DEFAULT_TIMEOUT,
        );

        $resp_data = (new \S\Http($base_url))->request($http_method, $path, $data, $options);
        $resp_data = ($resp_data ? self::json_decode($resp_data, true) : $resp_data);

        if ($resp_data && !empty($resp_data['errcode']) && $resp_data['errcode'] != self::SUCCESS_CODE) {
            throw new Exception($resp_data['errmsg'], $resp_data['errcode']);
        }

        return $resp_data;
    }

}
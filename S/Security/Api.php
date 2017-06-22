<?php
namespace S\Security;

use \S\Request;
use \S\Response;
use \S\Config;

/**
 * 对外接口规范
 * 对外部提供的接口需要确保请求是由应用方所发起，并且请求数据在传输中不会被第三者篡改。
 * 通过配置接口报文的过期时间，可以防止对接口的重放攻击。
 *
 * <demo>
 * ************************ request *****************************
 * //使用common配置来进行报文的签名 array('openid'='1111111111111')为向接口传递的参数
 * $sign_data = \S\Security::getSign('common', array('openid'='1111111111111'));
 *
 * 通过url链接请求
 * $request_query = http_build_query($sign_data);
 * $request_url = $url.'?'.$request_query;
 * header("Location:$request_url");
 * exit; //依据场景来确定是否需要exit
 *
 * 通过curl请求
 * $ret = \S\Http::request($url, 'POST', $sign_data)  //对curl请求有额外需求时，按需求配置request方法中的参数
 *
 * ************************* receive *****************************
 * //使用common配置校验请求报文的合法性 需要在接口中调用
 * \S\Security::check('common');
 * 校验失败时抛出异常\S\Exception
 * </demo>
 *
 * <config>
 * 配置文件/conf/security/../api.php
 * 格式：
 * array(
 *      'common' => array(
 *          'password' => 'af88b8d59f46e6af1899056e41c5e9c48994a662',
 *          'expire'   => ''
 *      ),
 * )
 *
 * 说明
 *
 * @key        => array(
 * @password   string 接口使用的密钥值,
 * @expire     int    报文有效时间 单位秒 用来防止重放攻击 不设置则不校验报文的发出时间
 *             )
 *             </config>
 * @deprecated 老项目兼容使用, 逐渐切换到nginx api gateway认证
 */
class Api {

    const CHECK_KEY_ERROR  = '请求头不合法';
    const CHECK_M_ERROR    = '参数值不合法';
    const CHECK_TIME_ERROR = '报文已过期';

    const CHECK_KEY_NO  = '4990001';
    const CHECK_M_NO    = '4990002';
    const CHECK_TIME_NO = '4990003';


    /**
     * 校验请求串
     *
     * @param $config_key
     *
     * @return bool
     * @throws \Base\Exception\Controller
     */
    public static function check($config_key = "") {
        $key = Request::request("key", null);
        $time = Request::request("t", null);
        $m = Request::request("m", null);
        $config_key = $config_key ?: $key;

        //如果参数没有，认为是有外部恶意扫描，隐藏错误码，返回404
        if (!$key || !$time || !$m) {
            Response::header404(htmlentities($_SERVER['REQUEST_URI']));
            exit;
        }

        //如果有设置过期时间 则检测time是否过期
        if ($expire = self::getApiExpire($config_key)) {
            $now = time();
            if (abs($now - $time) > $expire) {
                throw new \Base\Exception\Controller(self::CHECK_TIME_ERROR, self::CHECK_TIME_NO);
            }
        }

        //校验m值是否正确
        $params = $_REQUEST;
        unset($params['key']);
        unset($params['t']);
        unset($params['m']);
        if ($m !== self::getM($key, $time, self::getPassword($key), $params)) {
            throw new \Base\Exception\Controller(self::CHECK_M_ERROR, self::CHECK_M_NO);
        }
        return true;
    }

    /**
     * 生成请求时所需的签名串
     *
     * @param string $key      接口对应的key值
     * @param string $password 与对方约定的秘钥(默认取配置文件下的)
     * @param array  $params   向接口传输的参数
     * @return array 带签名的参数串
     */
    public static function getSign($key, array $params = array(), $password = "") {

        $time = time();
        $password = $password ? : self::getPassword($key);
        $m = self::getM($key, $time, $password, $params);

        $data = array(
            'key'  => $key,
            't' => $time,
            'm'    => $m,
        );
        return $data;
    }

    /**
     * 获得m值
     *
     * @param        $key
     * @param int    $time     当前时间戳（毫秒）
     * @param string $password 与对方约定的秘钥
     * @param array  $params   需要传输的字段
     * @return array 返回m
     */
    public static function getM($key, $time, $password, array $params = array()) {
        if ($params) {
            ksort($params);
            $param_value = array_values($params);
            $param_value = implode("", $param_value);
        } else {
            $param_value = "";
        }
        $m = substr(md5($key.$time.$password.$param_value), 0, 16);
        return $m;
    }

    /**
     * 获取Key对应的密钥
     *
     * @return string
     */
    protected static function getPassword($key) {
        return Config::confSecurity('api.'.$key.'.password') ?: null;
    }

    /**
     * 获取接口报文的过期时间
     */
    protected static function getApiExpire($key) {
        return Config::confSecurity('api.'.$key.'.expire') ?: null;
    }

}


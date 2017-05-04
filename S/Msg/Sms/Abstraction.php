<?php
namespace S\Msg\Sms;

use \S\Exception;

/**
 * Class Abstraction
 *
 * @package     S\Msg\Sms
 * @description 短信通道基类
 */
abstract class Abstraction {

    /**
     * @var array 通道配置
     */
    protected $config;

    /**
     * @var string 错误码
     */
    protected $err_code;
    /**
     * @var string 错误信息
     */
    protected $err_msg;

    public function __construct($config_key) {
        $config = \S\Config::confServer('sms.' . $config_key);
        if (!$config) {
            throw new Exception("未找到配置: $config_key");
        }
        $this->config = $config;
    }

    /**
     * 获取错误码
     *
     * @return string
     */
    public function getErrCode() {
        return $this->err_code;
    }

    /**
     * 获取错误信息
     *
     * @return string
     */
    public function getErrMsg() {
        return $this->err_msg;
    }

    /**
     * 发送短信
     *
     * @param string|array $mobile  手机号码
     * @param string       $content 短信内容
     *
     * @return bool
     */
    abstract function send($mobile, $content);

}
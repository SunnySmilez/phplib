<?php
namespace S\Msg;

use S\Exception;

/**
 * Class Sms
 *
 * @package     S\Msg
 * @description 发送短信
 *              配置文件为conf/server/dev/sms.php
 *
 *              例: 使用梦网短信平台发送短信
 *              $sms_sender = new \S\Msg\Sms();
 *              $ret = $sms_sender->send('15201345781', '测试消息');
 *
 *              $ret bool true为发送成功  false为发送失败
 *              当发送失败时 通过以下方法可以拿到错误码及错误消息
 *              $err_code ＝ $sms_sender->getErrorCode();
 *              $err_msg  = $sms_sender->getErrorMessage();
 *              其中超时的统一错误码为0  平台定义的错误码详见具体程序
 */
class Sms {

    const DEFAULT_CHANNEL_KEY = 'default';  //默认通道标识

    /**
     * @var \S\Msg\Sms\Abstraction
     */
    protected $handler;

    /**
     * Sms constructor.
     *
     * @param string $key
     *
     * @throws Exception
     */
    public function __construct($key = self::DEFAULT_CHANNEL_KEY) {
        $handler_class = \S\Config::confServer("sms.{$key}.service");
        if (!$handler_class) {
            throw new Exception("未配置的短信通道服务商: {$handler_class}.{$key}.service");
        }

        $handler_class = __NAMESPACE__ . "\\Sms\\" . ucfirst($handler_class);
        $this->handler = new $handler_class($key);
    }

    /**
     * 发送短信
     *
     * @param string|array $mobile  收信人 群发时使用数组传递
     * @param string       $content 正文
     *
     * @return bool true-发送成功 false-发送失败
     */
    public function send($mobile, $content) {
        if (!$mobile || !$content) {
            return false;
        }
        $ret = $this->handler->send($mobile, $content);

        return $ret;
    }

    /**
     * 获取错误码
     *
     * @return string
     */
    public function getErrCode() {
        return $this->handler->getErrCode();
    }

    /**
     * 获取错误消息
     *
     * @return string
     */
    public function getErrMsg() {
        return $this->handler->getErrMsg();
    }

}
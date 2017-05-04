<?php
namespace S\Captcha\Handler;

use S\Captcha\Util;
use S\Exception;

/**
 * Class Sms
 *
 * @package S\Captcha\Handler
 * @description 短信验证码服务, 使用openapi短信服务作为短信通道
 */
class Sms implements CaptchaInterface {

    const SEND_TIMEOUT = 2000;

    protected static $need_args = array('phone', 'code', 'template', 'service');

    public function show($args) {
        $this->checkArgs($args);
        $content = Util::content($args['code'], $args['template']);

        $sender = new \S\Msg\Sms($args['service']);
        $ret    = $sender->send($args['phone'], $content);

        if ($ret) {
            return $ret;
        } else {
            throw new Exception($sender->getErrMsg(), $sender->getErrCode());
        }
    }

    protected function checkArgs($args) {
        foreach (self::$need_args as $key) {
            if (!isset($args[$key])) {
                throw new Exception("缺少必需参数: '$key'");
            }
        }
    }
}
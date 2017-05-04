<?php
/**
 * 亿美短信平台
 */
namespace S\Msg\Sms;

class Yimei extends Abstraction {

    const SUCC_CODE = 0;

    private $code_message_map = array(
        -1    => "系统异常",
        -2    => "客户端异常",
        -101  => "命令不被支持",
        -104  => "请求超过限制",
        -117  => "发送短信失败",
        -127  => "计费失败0余额",
        -128  => "计费失败余额不足",
        -190  => "数据操作失败",
        -1100 => "序列号错误,序列号不存在内存中,或尝试攻击的用户",
        -1102 => "序列号密码错误",
        -1103 => "序列号Key错误",
        -1104 => "路由失败，请联系系统管理员",
        -1105 => "注册号状态异常",
        -9000 => "数据格式错误,数据超出数据库允许范围",
        -9001 => "序列号格式错误",
        -9002 => "密码格式错误",
        -9003 => "客户端Key格式错误",
        -9016 => "发送短信包大小超出范围",
        -9017 => "发送短信内容格式错误",
        -9019 => "发送短信优先级格式错误",
        -9020 => "发送短信手机号格式错误",
    );

    /**
     * @param string|array $to      收信人 群发时使用数组传递 长度不能超过200
     * @param string       $content 正文   不要超过500字
     * @return bool
     */
    public function send($to, $content) {
        $mobiles = $to;

        $data = array(
            "cdkey"    => $this->config["cdkey"],
            "password" => $this->config["password"],
            'phone'    => $mobiles,
            "message"  => $this->config['signature'].$content,
        );

        $url = \S\Http::parseUrl($this->config['url']);
        $http = new \S\Http($url['base']);
        $data = $http->request("POST", $url['path'], $data);
        $code = simplexml_load_string(trim($data))->error;

        $ret = $this->codeHandle($code);
        return $ret;
    }

    protected function codeHandle($code) {
        if ($code == self::SUCC_CODE) {
            return true;
        } else {
            $this->err_code = (int) $code;
            $this->err_msg = $this->code_message_map[(int)$code] ?: "系统错误";
            return false;
        }
    }
}
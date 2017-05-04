<?php
namespace Wechat\Message;

/**
 * Class Template
 *
 * @package     Wechat\Message
 * @description 模板消息
 */
class Template {

    const PATH_SEND_TEMPLATE_MSG = "message/template/send";  //发送模版消息

    public function __construct($access_token) {
        $this->_access_token = $access_token;
    }

    /**
     * 发送模板消息
     *
     * @param string $openid      用户openid
     * @param string $template_id 模版id
     * @param array  $data        模板消息实际内容
     * @param string $url         default "" 点击模板消息跳转url
     * @param string $top_color   default "" 模板消息顶端颜色
     *
     * @return string 模版消息msgid
     */
    public function send($openid, $template_id, array $data, $url = "", $top_color = "") {
        $params = array(
            'touser'      => $openid,
            'template_id' => $template_id,
            'url'         => $url,
            'topcolor'    => $top_color,
        );
        if ($data) {
            $params['data'] = $data;
        }

        $resp_data = \Wechat\Util::request(self::PATH_SEND_TEMPLATE_MSG, \Wechat\Util::json_encode($params), $this->_access_token);

        return $resp_data['msgid'];
    }

}
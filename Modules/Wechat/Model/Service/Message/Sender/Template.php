<?php
namespace Modules\Wechat\Model\Service\Message\Sender;

/**
 * Class Template
 *
 * @package     Modules\Wechat\Model\Util\Message\Sender
 * @description 模版消息
 */
class Template {
    /**
     * @var string 微信公众号名称标识
     */
    private $_wechat_name;
    /**
     * @var string 接口调用凭证
     */
    private $_access_token;

    public function __construct($wechat_name) {
        $this->_wechat_name  = $wechat_name;
        $this->_access_token = (new \Modules\Wechat\Model\Data\Config())->getAccessToken($wechat_name);
    }

    /**
     * 发送模版消息
     *
     * @param string $openid
     * @param string $template_id
     * @param array  $data
     * @param string $url
     * @param string $top_color
     *
     * @return string
     */
    public function send($openid, $template_id, array $data, $url = "", $top_color = "") {
        foreach ($data as $index => $item) {
            if (!is_array($item)) {
                $data[$index] = array(
                    'value' => $item,
                    'color' => '',
                );
            }
        }

        return (new \Wechat\Message\Template($this->_access_token))->send($openid, $template_id, $data, $url, $top_color);
    }

}
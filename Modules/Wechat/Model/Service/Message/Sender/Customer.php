<?php
namespace Modules\Wechat\Model\Service\Message\Sender;

/**
 * Class Customer
 *
 * @package     Modules\Wechat\Model\Util\Message\Sender
 * @description 客服消息
 *
 * @method sendText($openid, $message)
 * @method sendNews($openid, array $news)
 * @method sendImage($openid, $mediaId)
 * @method sendVoice($openid, $mediaId)
 * @method sendVideo($openid, $mediaId, $thumbMediaId, $title = "", $description = "")
 * @method sendMusic($openid, $musicUrl, $hqMusicUrl, $thumbMediaId, $title = "", $description = "")
 */
class Customer {

    private $_wechat_name;
    private $_access_token;

    public function __construct($wechat_name) {
        $this->_wechat_name  = $wechat_name;
        $this->_access_token = (new \Modules\Wechat\Model\Data\Config())->getAccessToken($wechat_name);
    }

    function __call($name, $arguments) {
        return call_user_func_array(array((new \Wechat\Message\Customer($this->_access_token)), $name), $arguments);
    }

}
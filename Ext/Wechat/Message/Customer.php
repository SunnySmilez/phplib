<?php
namespace Wechat\Message;

/**
 * Class Customer
 *
 * @package     Wechat\Message
 * @description 客服消息
 */
class Customer {

    const PATH_SEND_CUSTOM_MSG = 'message/custom/send';  //发送客服消息

    const MSG_TYPE_TEXT = "text";
    const MSG_TYPE_IMAGE = "image";
    const MSG_TYPE_VOICE = "voice";
    const MSG_TYPE_VIDEO = "video";
    const MSG_TYPE_MUSIC = "music";
    const MSG_TYPE_NEWS = "news";

    const ERR_CODE_SUCCESS = 0;
    const ERR_MSG_SUCCESS = 'ok';

    private $_access_token;

    public function __construct($access_token) {
        $this->_access_token = $access_token;
    }

    /**
     * 发送文本客服消息
     *
     * @param string $openid  用户openid
     * @param string $message 文本消息
     *
     * @return bool
     */
    public function sendText($openid, $message) {
        return $this->_send($openid, self::MSG_TYPE_TEXT, array("content" => $message));
    }

    /**
     * 发送图片客服消息
     *
     * @param string $openid  用户openid
     * @param string $mediaId 图片media_id
     *
     * @return bool
     */
    public function sendImage($openid, $mediaId) {
        return $this->_send($openid, self::MSG_TYPE_IMAGE, array("media_id" => $mediaId));
    }

    /**
     * 发送图片客服消息
     *
     * @param string $openid  用户openid
     * @param string $mediaId 语音media_id
     *
     * @return bool
     */
    public function sendVoice($openid, $mediaId) {
        return $this->_send($openid, self::MSG_TYPE_VOICE, array("media_id" => $mediaId));
    }

    /**
     * 发送视频客服消息
     *
     * @param string $openid       用户openid
     * @param string $mediaId      视频media_id
     * @param string $thumbMediaId 缩略图的媒体ID
     * @param string $title        default "" 标题
     * @param string $description  default "" 描述
     *
     * @return bool
     */
    public function sendVideo($openid, $mediaId, $thumbMediaId, $title = "", $description = "") {
        $data = array(
            "media_id"       => $mediaId,
            "thumb_media_id" => $thumbMediaId,
            "title"          => $title,
            "description"    => $description,
        );

        return $this->_send($openid, self::MSG_TYPE_VIDEO, $data);
    }

    /**
     * 发送音乐客服消息
     *
     * @param string $openid       用户openid
     * @param string $musicUrl     音乐链接
     * @param string $hqMusicUrl   高品质音乐链接，wifi环境优先使用该链接播放音乐
     * @param string $thumbMediaId 缩略图的媒体ID
     * @param string $title        default "" 标题
     * @param string $description  default "" 描述
     *
     * @return bool
     */
    public function sendMusic($openid, $musicUrl, $hqMusicUrl, $thumbMediaId, $title = "", $description = "") {
        $data = array(
            "musicurl"       => $musicUrl,
            "hqmusicurl"     => $hqMusicUrl,
            "thumb_media_id" => $thumbMediaId,
            "title"          => $title,
            "description"    => $description,
        );

        return $this->_send($openid, self::MSG_TYPE_VIDEO, $data);
    }

    /**
     * 发送图文客服消息
     *
     * @param string $openid 用户openid
     * @param array  $news   图文消息
     *
     * @return bool
     */
    public function sendNews($openid, array $news) {
        return $this->_send($openid, self::MSG_TYPE_NEWS, array("articles" => $news));
    }

    /**
     * 发送客服消息
     *
     * @param string $openid  用户openid
     * @param string $msgType 消息类型，具体类型参考文档定义：http://mp.weixin.qq.com/wiki/1/70a29afed17f56d537c833f89be979c9.html#.E5.AE.A2.E6.9C.8D.E6.8E.A5.E5.8F.A3-.E5.8F.91.E6.B6.88.E6.81.AF
     * @param array  $data    内容
     *
     * @return bool
     */
    private function _send($openid, $msgType, array $data) {
        $request_params = array(
            "touser"  => $openid,
            "msgtype" => $msgType,
            $msgType  => $data,
        );

        $resp_data = \Wechat\Util::request(self::PATH_SEND_CUSTOM_MSG, \Wechat\Util::json_encode($request_params), $this->_access_token);

        return ((self::ERR_CODE_SUCCESS == $resp_data['errcode']) && (self::ERR_MSG_SUCCESS == $resp_data['errmsg']));
    }

}
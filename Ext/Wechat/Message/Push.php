<?php
namespace Wechat\Message;

use Wechat\Exception;

/**
 * 微信推送消息
 *
 * 用于构造微信推送过来的消息
 *
 * @property string FromUserName    消息发送者
 * @property string ToUserName      消息接收者
 * @property string MsgType         消息类型
 * @property int    MsgId           消息id
 * @property string CreateTime      消息发送时间
 * @property string content         消息内容正文，包括Content和Recognition
 * @property string Content         消息内容正文
 * @property string Recognition     语音识别文字内容
 * @property string PicUrl          消息内容正文
 * @property string Url             消息链接
 * @property string Title           消息标题
 * @property string Description     消息描述
 * @property string Ticket          完整的ticket
 * @property string Event           事件类型
 * @property string Encrypt         加密信息
 *
 * 为了便于使用抽象出来的一些类型
 *
 * @property array  geo             接收的位置信息 (x,y,scale,label)
 * @property array  eventgeo        上报的位置信息 (x,y,precision)
 * @property array  link            链接信息 (url,title,desc)
 * @property array  event           事件信息 (Event,EventKey)
 * @property array  voice           语音信息 (id,format)
 * @property string sceneid         二维码消息中的id
 */
class Push {

    const MSG_TYPE_TEXT = 'text';
    const MSG_TYPE_IMAGE = 'image';
    const MSG_TYPE_LOCATION = 'location';
    const MSG_TYPE_LINK = 'link';
    const MSG_TYPE_EVENT = 'event';
    const MSG_TYPE_MUSIC = 'music';
    const MSG_TYPE_NEWS = 'news';
    const MSG_TYPE_VOICE = 'voice';
    const MSG_TYPE_VIDEO = 'video';
    const MSG_TYPE_CUSTOMER = 'transfer_customer_service';

    const EVENT_SUBSCRIBE = 'subscribe';
    const EVENT_UNSUBSCRIBE = 'unsubscribe';
    const EVENT_LOCATION = 'LOCATION';
    const EVENT_CLICK = 'CLICK';
    const EVENT_VIEW = 'VIEW';
    const EVENT_TEMPLATE_SEND = 'TEMPLATESENDJOBFINISH';
    const EVENT_MASS_SEND = 'MASSSENDJOBFINISH';
    //卡券相关
    const EVENT_CARD_PASS_CHECK = 'card_pass_check';//卡券审核通过
    const EVENT_CARD_NOT_PASS_CHECK = 'card_not_pass_check';//卡券审核失败
    const EVENT_USER_GET_CARD = 'user_get_card';//用户领取卡券
    const EVENT_USER_GIFTING_CARD = 'user_gifting_card';//用户转增卡券
    const EVENT_USER_DEL_CARD = 'user_del_card';//用户删除卡券
    const EVENT_USER_CONSUME_CARD = 'user_consume_card';//用户核销卡券
    const EVENT_PAY_FROM_PAY_CELL = 'user_pay_from_pay_cell';//微信买单事件推送
    const EVENT_USER_VIEW_CARD = 'user_view_card';//用户进入会员卡页面
    const EVENT_USER_ENTER_SESSION_FROM_CARD = 'user_enter_session_from_card';//从卡券进入公众号会话事件
    const EVENT_UPDATE_MEMBER_CARD = 'update_member_card';//会员卡内容更新事件
    const EVENT_CARD_SKU_REMIND = 'card_sku_remind';//库存报警事件
    const EVENT_CARD_PAY_ORDER = 'card_pay_order';//券点变动
    const EVENT_MEMBERCARD_USER_INFO = 'submit_membercard_user_info';//会员卡激活事件

    const EVENT_POI_CHECK_NOTIFY = 'poi_check_notify';//门店审核成功

    /**
     * @var array 微信公众号配置
     *            包括:
     *            appid
     *            appsecret
     *            token
     *            encoding_aes_key
     */
    private $_config;
    /**
     * @var array 请求数据详情
     */
    private $_request_data = array();
    /**
     * @var string 请求xml报文
     */
    private $_request_xml = '';

    /**
     * Request constructor.
     *
     * @param array $config 微信配置, 包括:
     *                      appid            string 公众号appid
     *                      appsecret        string 公众号appsecret
     *                      token            string 消息签名计算token
     *                      encoding_aes_key string 安全|兼容模式下消息加解密秘钥
     *
     * @throws Exception
     */
    public function __construct(array $config) {
        $this->_config = $config;

        $this->_request_xml = file_get_contents('php://input');
        if (!$this->_request_xml) {
            throw new Exception('xml格式请求消息不能为空', Exception::UNKNOW_ERROR);
        }
        $this->_request_data = \Wechat\Util::parseXML($this->_request_xml);

        //兼容模式｜安全模式
        if (isset($this->_request_data["Encrypt"])) {
            $appid            = $this->_config['appid'];
            $encoding_aes_key = $this->_config['encoding_aes_key'];
            $token            = $this->_config['token'];
            $encrypt          = $this->_request_data["Encrypt"];

            if (!self::_checkEncryptSign($encrypt, $token)) {
                throw new Exception('加密报文签名验证失败', Exception::UNKNOW_ERROR);
            }

            $this->_request_xml  = \Wechat\Message\Crypt\Cipher::decrypt($appid, $encoding_aes_key, $encrypt);
            $this->_request_data = \Wechat\Util::parseXML($this->_request_xml);
        }
    }

    public function __get($name) {
        return isset($this->_request_data[$name]) ? $this->_request_data[$name] : (method_exists($this, '_get' . $name) ? call_user_func(array($this, '_get' . $name)) : null);
    }

    public function __set($name, $value) {
        throw new Exception('不支持属性: ' . $name, Exception::UNKNOW_ERROR);
    }

    /**
     * 校验请求签名
     *
     * @return bool
     * @throws Exception
     */
    public static function checkSign($token) {
        $signature = \S\Request::get("signature");
        $timestamp = \S\Request::get("timestamp");
        $nonce     = \S\Request::get("nonce");

        if (!$signature || !$timestamp || !$nonce) {
            throw new Exception('缺少基本请求参数: signature|timestamp|nonce', Exception::UNKNOW_ERROR);
        }

        $info = array($token, $timestamp, $nonce);
        sort($info, SORT_STRING);

        return sha1(implode($info)) === $signature;
    }

    /**
     * 获取请求xml格式报文
     *
     * @return string
     */
    public function getRequestXml() {
        return $this->_request_xml;
    }

    /**
     * 以array格式获取全部消息内容
     *
     * @return array
     */
    public function getRequestData() {
        return $this->_request_data;
    }

    /**
     * 使用当前消息生成一个回复用户的文本消息
     *
     * @param string $content
     *
     * @return string
     */
    public function responseText($content) {
        $data = array(
            'MsgType' => self::MSG_TYPE_TEXT,
            'Content' => $content,
        );

        return $this->_getResponseXml($data);
    }

    /**
     * 将当前消息转换成一个回复给用户的图文消息
     *
     * @param array $contents
     *
     * @return string
     */
    public function responseNews(array $contents) {
        $data = array(
            'MsgType'      => self::MSG_TYPE_NEWS,
            'ArticleCount' => count($contents),
            'Articles'     => $contents,
        );

        return $this->_getResponseXml($data);
    }

    /**
     * 接入多客服
     *
     * @return string
     */
    public function responseCustomer() {
        $data = array(
            'MsgType' => self::MSG_TYPE_CUSTOMER,
        );

        return $this->_getResponseXml($data);
    }

    /**
     * 获取接收消息内容正文
     */
    protected function _getContent() {
        if (isset($this->_request_data['Content'])) {
            return $this->_request_data['Content'];
        } elseif (isset($this->_request_data['Recognition'])) {
            //获取语音识别文字内容，需申请开通
            return $this->_request_data['Recognition'];
        }

        return array();
    }

    /**
     * 获取接收消息链接
     */
    protected function _getLink() {
        if (isset($this->_request_data['Url'])) {
            return array(
                'url'   => $this->_request_data['Url'],
                'title' => $this->_request_data['Title'],
                'desc'  => $this->_request_data['Description'],
            );
        }

        return array();
    }

    /**
     * 获取接收地理位置
     */
    protected function _getGeo() {
        if (isset($this->_request_data['Location_X'])) {
            return array(
                'x'     => $this->_request_data['Location_X'],
                'y'     => $this->_request_data['Location_Y'],
                'scale' => $this->_request_data['Scale'],
                'label' => $this->_request_data['Label'],
            );
        }

        return array();
    }

    /**
     * 获取上报地理位置事件
     */
    protected function _getEventGeo() {
        if (isset($this->_request_data['Latitude'])) {
            return array(
                'x'         => $this->_request_data['Latitude'],
                'y'         => $this->_request_data['Longitude'],
                'precision' => $this->_request_data['Precision'],
            );
        }

        return array();
    }

    /**
     * 获取接收事件推送
     */
    protected function _getEvent() {
        if (isset($this->_request_data['Event'])) {
            return array(
                'event' => $this->_request_data['Event'],
                'key'   => $this->_request_data['EventKey'],
            );
        }

        return null;
    }

    /**
     * 获取接收语言推送
     */
    protected function _getVoice() {
        if (isset($this->_request_data['MediaId'])) {
            return array(
                'id'     => $this->_request_data['MediaId'],
                'format' => $this->_request_data['Format'],
            );
        }

        return array();
    }

    /**
     * 获取接收视频推送
     */
    protected function _getVideo() {
        if (isset($this->_request_data['MediaId'])) {
            return array(
                'id'  => $this->_request_data['MediaId'],
                'mid' => $this->_request_data['ThumbMediaId'],
            );
        }

        return array();
    }

    /**
     * 获取二维码的场景值
     */
    protected function _getSceneId() {
        if (isset($this->_request_data['EventKey'])) {
            return str_replace('qrscene_', '', $this->_request_data['EventKey']);
        }

        return array();
    }

    /**
     * 校验加密信息签名
     *
     * @param $encrypt_msg
     * @param $token
     *
     * @return bool
     */
    private static function _checkEncryptSign($encrypt_msg, $token) {
        $msg_sign  = \S\Request::get("msg_signature");
        $timestamp = \S\Request::get("timestamp");
        $nonce     = \S\Request::get("nonce");
        $sign      = self::_getEncryptSign($encrypt_msg, $token, $timestamp, $nonce);

        return $msg_sign === $sign;
    }

    /**
     * 计算加密消息签名
     *
     * @param $encrypt_msg
     * @param $token
     * @param $timestamp
     * @param $nonce
     *
     * @return string
     */
    private static function _getEncryptSign($encrypt_msg, $token, $timestamp, $nonce) {
        $info = array($encrypt_msg, $token, $timestamp, $nonce);
        sort($info, SORT_STRING);

        return sha1(implode($info));
    }

    /**
     * 获取响应xml报文
     *
     * @param array $data
     *
     * @return string
     */
    private function _getResponseXml(array $data) {
        $data['ToUserName']   = $this->FromUserName;
        $data['FromUserName'] = $this->ToUserName;
        $data['CreateTime']   = time();

        $resp_xml = \Wechat\Util::toXML($data);

        //兼容模式｜安全模式
        if (!\S\Request::get("msg_signature")) {
            return $resp_xml;
        }

        $appid            = $this->_config['appid'];
        $encoding_aes_key = $this->_config['encoding_aes_key'];
        $token            = $this->_config['token'];
        $timestamp        = time();
        $nonce            = \S\Request::get("nonce");
        $encrypt          = \Wechat\Message\Crypt\Cipher::encrypt($appid, $encoding_aes_key, $resp_xml);

        $data = array(
            'Encrypt'      => $encrypt,
            'MsgSignature' => self::_getEncryptSign($encrypt, $token, $timestamp, $nonce),
            'TimeStamp'    => $timestamp,
            'Nonce'        => $nonce,
        );

        return \Wechat\Util::toXML($data);
    }

}
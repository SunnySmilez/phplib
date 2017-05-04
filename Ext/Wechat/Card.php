<?php
namespace Wechat;

/**
 * Class Card
 *
 * @package     Wechat\Card
 * @description 卡券
 */
class Card {
    const COLOR_010 = "Color010";
    const COLOR_020 = "Color020";
    const COLOR_030 = "Color030";
    const COLOR_040 = "Color040";
    const COLOR_050 = "Color050";
    const COLOR_060 = "Color060";
    const COLOR_070 = "Color070";
    const COLOR_080 = "Color080";
    const COLOR_081 = "Color081";
    const COLOR_082 = "Color082";
    const COLOR_090 = "Color090";
    const COLOR_101 = "Color101";
    const COLOR_102 = "Color102";

    const COLORS = [
        self::COLOR_010, self::COLOR_020, self::COLOR_030, self::COLOR_040, self::COLOR_050,
        self::COLOR_060, self::COLOR_070, self::COLOR_080, self::COLOR_081, self::COLOR_082,
        self::COLOR_090, self::COLOR_101, self::COLOR_102,
    ];

    //卡券类型
    const TYPE_GROUPON = 'GROUPON';//团购券
    const TYPE_CASH = 'CASH';//代金券类型
    const TYPE_DISCOUNT = 'DISCOUNT';//折扣券类型
    const TYPE_GIFT = 'GIFT';//兑换券类型
    const TYPE_GENERAL_COUPON = 'GENERAL_COUPON';//优惠券类型
    const TYPE_MEMBER_CARD = 'MEMBER_CARD';//会员卡
    const TYPE_SCENIC_TICKET = 'SCENIC_TICKET';//景点门票
    const TYPE_MOVIE_TICKET = 'MOVIE_TICKET';//电影票
    const TYPE_BOARDING_PASS = 'BOARDING_PASS';//飞机票
    const TYPE_MEETING_TICKET = 'MEETING_TICKET';//会议门票
    const TYPE_BUS_TICKET = 'BUS_TICKET';//汽车票

    //卡券code类型
    const CODE_TYPE_TEXT = 'CODE_TYPE_TEXT';//文本
    const CODE_TYPE_BARCODE = 'CODE_TYPE_BARCODE';//一维码
    const CODE_TYPE_QRCODE = 'CODE_TYPE_QRCODE';//二维码
    const CODE_TYPE_ONLY_QRCODE = 'CODE_TYPE_ONLY_QRCODE';//二维码无code显示
    const CODE_TYPE_ONLY_BARCODE = 'CODE_TYPE_ONLY_BARCODE';//一维码无code显示
    const CODE_TYPE_NONE = 'CODE_TYPE_ONLY_BARCODE';//不显示code和条形码类型

    //货架投放页面的场景
    const SCENE_NEAR_BY = 'SCENE_NEAR_BY';//附近
    const SCENE_MENU = 'SCENE_MENU';//自定义菜单
    const SCENE_QRCODE = 'SCENE_QRCODE';//二维码
    const SCENE_ARTICLE = 'SCENE_ARTICLE';//公众号文章
    const SCENE_H5 = 'SCENE_H5';//h5页面
    const SCENE_IVR = 'SCENE_IVR';//自动回复
    const SCENE_CARD_CUSTOM_CELL = 'SCENE_CARD_CUSTOM_CELL';//卡券自定义cell

    //卡券状态
    const CARD_STATUS_NOT_VERIFY = "CARD_STATUS_NOT_VERIFY";//待审核
    const CARD_STATUS_VERIFY_FAIL = "CARD_STATUS_VERIFY_FAIL";//审核失败
    const CARD_STATUS_VERIFY_OK = "CARD_STATUS_VERIFY_OK";//通过审核
    const CARD_STATUS_DELETE = "CARD_STATUS_DELETE";//卡券被商户删除
    const CARD_STATUS_DISPATCH = "CARD_STATUS_DISPATCH";//在公众平台投放过的卡券

    //用户卡券状态
    const USER_CARD_STATUS_NORMAL = 'NORMAL';//正常
    const USER_CARD_STATUS_CONSUMED = 'CONSUMED';//已核销
    const USER_CARD_STATUS_EXPIRE = 'EXPIRE';//已过期
    const USER_CARD_STATUS_GIFTING = 'GIFTING';//转赠中
    const USER_CARD_STATUS_GIFT_TIMEOUT = 'GIFT_TIMEOUT';//转赠超时
    const USER_CARD_STATUS_GIFT_DELETE = 'DELETE';//已删除
    const USER_CARD_STATUS_GIFT_UNAVAILABLE = 'UNAVAILABLE';//已失效

    //支付营销规则类型
    const RULE_TYPE_PAY_MEMBER_CARD = 'RULE_TYPE_PAY_MEMBER_CARD';//支付即会员

    //接口列表
    //创建卡券
    const API_CREATE = 'https://api.weixin.qq.com/card/create';
    const API_SET_PAY_CELL = 'https://api.weixin.qq.com/card/paycell/set';//设置买单接口
    const API_SET_SELF_CONSUME_CELL = 'https://api.weixin.qq.com/card/selfconsumecell/set';//自助核销
    //投放卡券
    const API_CREATE_QRCODE = 'https://api.weixin.qq.com/card/qrcode/create';//创建二维码
    const API_CREATE_LANDING_PAGE = 'https://api.weixin.qq.com/card/landingpage/create';//创建货架
    const API_DEPOSIT_CODE = 'https://api.weixin.qq.com/card/code/deposit';//导入code
    const API_GET_DEPOSIT_COUNT = 'https://api.weixin.qq.com/card/code/getdepositcount';//查询导入code数目
    const API_CHECK_CODE = 'https://api.weixin.qq.com/card/code/checkcode';//核查code
    const API_GET_HTML = 'https://api.weixin.qq.com/card/mpnews/gethtml';//图文消息群发卡券
    const API_SET_TEST_WHITE_LIST = 'https://api.weixin.qq.com/card/testwhitelist/set';//设置测试白名单
    //核销卡券
    const API_GET_CODE = 'https://api.weixin.qq.com/card/code/get';  //查询Code
    const API_CONSUME_CODE = 'https://api.weixin.qq.com/card/code/consume';//核销code
    const API_DECRYPT_CODE = 'https://api.weixin.qq.com/card/code/decrypt';//Code解码

    //管理卡券
    const API_GET_USER_CARD_LIST = 'https://api.weixin.qq.com/card/user/getcardlist';//获取用户已领取卡券
    const API_GET_CARD = 'https://api.weixin.qq.com/card/get';//查看卡券详情
    const API_BATCH_GET_CARD = 'https://api.weixin.qq.com/card/batchget';//批量查询卡券列表
    const API_UPDATE = 'https://api.weixin.qq.com/card/update';//更改卡券信息
    const API_UPDATE_CODE = 'https://api.weixin.qq.com/card/code/update';//更改Code
    const API_DELETE = 'https://api.weixin.qq.com/card/delete';//删除卡券
    const API_DISABLE = 'https://api.weixin.qq.com/card/code/unavailable';//设置卡券失效

    const API_GET_CARD_BIZUIN_INFO = 'https://api.weixin.qq.com/datacube/getcardbizuininfo';//拉取卡券概况数据
    const API_GET_CARD_INFO = 'https://api.weixin.qq.com/datacube/getcardcardinfo';//拉取卡券概况数据
    const API_GET_MEMBER_CARD_INFO = 'https://api.weixin.qq.com/datacube/getcardmembercardinfo';//拉取卡券概况数据
    const API_GET_MEMBER_CARD_DETAIL = 'https://api.weixin.qq.com/datacube/getcardmembercarddetail';//拉取单张会员卡数据

    //会员卡
    const API_ACTIVATE_MEMBER_CARD = 'https://api.weixin.qq.com/card/membercard/activate';//激活会员卡
    const API_SET_ACTIVATE_USER_FORM = 'https://api.weixin.qq.com/card/membercard/activateuserform/set';//设置开卡字段
    const API_GET_MEMBER_CARD_USER_INFO = 'https://api.weixin.qq.com/card/membercard/userinfo/get';//拉取会员信息
    const API_GET_ACTIVATE_TEMP_INFO = 'https://api.weixin.qq.com/card/membercard/activatetempinfo/get';//获取用户提交资料
    const API_UPDATE_MEMBER_CARD_USER = 'https://api.weixin.qq.com/card/membercard/updateuser';//更新会员信息

    const API_ADD_PAY_GIFT_CARD = 'https://api.weixin.qq.com/card/paygiftcard/add';//支付后营销规则添加
    const API_DEL_PAY_GIFT_CARD = 'https://api.weixin.qq.com/card/paygiftcard/delete';//支付后营销规则删除
    const API_GET_BY_ID_PAY_GIFT_CARD = 'https://api.weixin.qq.com/card/paygiftcard/getbyid';//查询支付即会员规则详情
    const API_BATCH_GET_PAY_GIFT_CARD = 'https://api.weixin.qq.com/card/paygiftcard/batchget';//批量查询支付即会员规则

    //朋友的券
    const API_ACTIVATE_PAY = 'https://api.weixin.qq.com/card/pay/activate';//开通券点账户
    const API_GET_PAY_PRICE = 'https://api.weixin.qq.com/card/pay/getpayprice';//对优惠券批价
    const API_GET_COINS_INFO = 'https://api.weixin.qq.com/card/pay/getcoinsinfo';//查询点券余额
    const API_CONFIRM_PAY = 'https://api.weixin.qq.com/card/pay/confirm';//确认兑换库存
    const API_RECHARGE_PAY = 'https://api.weixin.qq.com/card/pay/recharge';//充值券点
    const API_GET_PAY_ORDER = 'https://api.weixin.qq.com/card/pay/getorder';//查询订单详情
    const API_GET_PAY_ORDER_LIST = 'https://api.weixin.qq.com/card/pay/getorderlist';//查询券点流水详情
    const API_GET_TICKET = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket';//获取卡券API_TICKET
    const API_MARK_CODE = 'https://api.weixin.qq.com/card/code/mark';//Mark(占用)Code接口

    //会议门票
    const API_UPDATE_MEETING_TICKET_USER = 'https://api.weixin.qq.com/card/meetingticket/updateuser';//更新会议门票
    //电影票
    const API_UPDATE_MOVIE_TICKET_USER = 'https://api.weixin.qq.com/card/movieticket/updateuser';//更新电影票
    //飞机票
    const API_CHECK_IN_BOARDING_PASS = 'https://api.weixin.qq.com/card/boardingpass/checkin';//更新飞机票


    private $_access_token;

    public function __construct($access_token) {
        $this->_access_token = $access_token;
    }

    /**
     * @name  创建卡券
     *
     * @param $card_type     |团购券类型
     * @param $base_info     |基本的卡券数据，见下表，所有卡券类型通用
     * @param $special       |卡券特殊信息
     * @param $advanced_info |卡券高级信息
     *
     * @return array
     */
    public function create($card_type, $base_info, $special, $advanced_info = []) {
        $data = [
            'card' => [
                'card_type'            => strtoupper($card_type),
                strtolower($card_type) => array_merge(['base_info' => $base_info], $special, ['advanced_info' => $advanced_info]),
            ],
        ];

        return self::_request(self::API_CREATE, $data, $this->_access_token);
    }

    /**
     * 设置微信买单接口.
     * 设置买单的 card_id 必须已经配置了门店，否则会报错.
     *
     * @param      $card_id |卡券ID
     * @param bool $is_open |是否开启买单功能，填true/false
     *
     * @return mixed
     */
    public function setPayCell($card_id, $is_open = true) {
        $data = [
            'card_id' => $card_id,
            'is_open' => $is_open,
        ];

        return self::_request(self::API_SET_PAY_CELL, $data, $this->_access_token);
    }


    /**
     * 设置自助核销接口
     *
     * @param      $card_id            |卡券ID
     * @param bool $is_open            |是否开启自助核销功能，填true/false，默认为false
     * @param bool $need_verify_cod    |用户核销时是否需要输入验证码，填true/false，默认为false
     * @param bool $need_remark_amount |用户核销时是否需要备注核销金额，填true/false，默认为false
     *
     * @return array
     */
    public function setSelfConsumeCell($card_id, $is_open = true, $need_verify_cod = false, $need_remark_amount = false) {
        $data = [
            'card_id'            => $card_id,
            'is_open'            => $is_open,
            'need_verify_cod'    => $need_verify_cod,
            'need_remark_amount' => $need_remark_amount,
        ];

        return self::_request(self::API_SET_SELF_CONSUME_CELL, $data, $this->_access_token);
    }

    /**
     * 创建二维码
     *
     * @param array $cards
     *
     * @return array
     */
    public function createQRCode(array $cards) {
        return self::_request(self::API_CREATE_QRCODE, $cards, $this->_access_token);
    }

    /**
     * 创建货架接口
     *
     * @param $banner     |页面的banner图片链接，须调用，建议尺寸为640*300
     * @param $page_title |页面的title
     * @param $can_share  |页面是否可以分享,填入true/false
     * @param $scene      |投放页面的场景值
     * @param $card_list  |卡券列表，每个item有两个字段
     *
     * @return array
     */
    public function createLandingPage($banner, $page_title, $can_share, $scene, $card_list) {
        $data = [
            'banner'     => $banner,
            'page_title' => $page_title,
            'can_share'  => $can_share,
            'scene'      => $scene,
            'card_list'  => $card_list,
        ];

        return self::_request(self::API_CREATE_LANDING_PAGE, $data, $this->_access_token);
    }

    /**
     * 导入code接口
     *
     * @param             $card_id |需要进行导入code的卡券ID
     * @param array       $code    |需导入微信卡券后台的自定义code，上限为100个
     *
     * @return mixed
     */
    public function depositCode($card_id, array $code) {
        $data = [
            'card_id' => $card_id,
            'code'    => $code,
        ];

        return self::_request(self::API_DEPOSIT_CODE, $data, $this->_access_token);
    }

    /**
     * 查询导入code数目
     *
     * @param $card_id |进行导入code的卡券ID
     *
     * @return array
     */
    public function getDepositCount($card_id) {
        $data = [
            'card_id' => $card_id,
        ];

        return self::_request(self::API_GET_DEPOSIT_COUNT, $data, $this->_access_token);
    }

    /**
     * 核查code接口
     *
     * @param       $card_id |进行导入code的卡券ID
     * @param array $code    |已经微信卡券后台的自定义code，上限为100个
     *
     * @return array
     */
    public function checkCode($card_id, array $code) {
        $data = [
            'card_id' => $card_id,
            'code'    => $code,
        ];

        return self::_request(self::API_CHECK_CODE, $data, $this->_access_token);
    }

    /**
     * 图文消息群发卡券
     *
     * @param $card_id |卡券ID
     *
     * @return array
     */
    public function getHtml($card_id) {
        $data = [
            'card_id' => $card_id,
        ];

        return self::_request(self::API_GET_HTML, $data, $this->_access_token);
    }

    /**
     * 设置测试白名单
     *
     * @param array $open_id  |测试的openid列表
     * @param array $username |测试的微信号列表
     *
     * @return array
     */
    public function setTestWhitelist(array $open_id = [], array $username = []) {
        $data = [
            'openid'   => $open_id,
            'username' => $username,
        ];

        return self::_request(self::API_SET_TEST_WHITE_LIST, $data, $this->_access_token);
    }

    /**
     * 查询Code接口
     *
     * @param $code          |单张卡券的唯一标准
     * @param $check_consume |是否校验code核销状态，填入true和false时的code异常状态返回数据不同
     * @param $card_id       |卡券ID代表一类卡券。自定义code卡券必填
     *
     * @return array
     */
    public function getCode($code, $check_consume, $card_id) {
        $data = [
            'code'          => $code,
            'check_consume' => $check_consume,
            'card_id'       => $card_id,
        ];

        return self::_request(self::API_GET_CODE, $data, $this->_access_token);
    }

    /**
     * 核销Code接口
     *
     * @param      $code    |需核销的Code码
     * @param null $card_id |卡券ID。创建卡券时use_custom_code填写true时必填。非自定义Code不必填写
     * @param null $openid
     *
     * @return array
     */
    public function consumeCode($code, $card_id = null, $openid = null) {

        $data = [
            'code' => $code,
        ];

        if ($card_id) {
            $data['card_id'] = $card_id;
        }

        if ($openid) {
            $data['openid'] = $openid;

        }

        return self::_request(self::API_CONSUME_CODE, $data, $this->_access_token);
    }

    /**
     * Code解码接口
     *
     * @param $encrypted_code |经过加密的Code码
     *
     * @return array
     */
    public function decryptCode($encrypted_code) {
        $data = [
            'encrypt_code' => $encrypted_code,
        ];

        return self::_request(self::API_DECRYPT_CODE, $data, $this->_access_token);
    }

    /**
     * 获取用户已领取卡券接口
     *
     * @param        $openid  |需要查询的用户openid
     * @param string $card_id |卡券ID。不填写时默认查询当前appid下的卡券
     *
     * @return mixed
     */
    public function getUserCardList($openid, $card_id = '') {
        $data = [
            'openid'  => $openid,
            'card_id' => $card_id,
        ];

        return self::_request(self::API_GET_USER_CARD_LIST, $data, $this->_access_token);
    }

    /**
     * 查看卡券详情
     *
     * @param $card_id |卡券ID
     *
     * @return array
     */
    public function getCard($card_id) {
        $data = [
            'card_id' => $card_id,
        ];

        return self::_request(self::API_GET_CARD, $data, $this->_access_token);
    }

    /**
     * 批量查询卡列表
     *
     * @param int   $offset      |查询卡列表的起始偏移量，从0开始，即offset: 5是指从从列表里的第六个开始读取
     * @param int   $count       |需要查询的卡片的数量（数量最大50）
     * @param array $status_list |支持开发者拉出指定状态的卡券列表
     *
     * @return array
     */
    public function batchGetCard($offset = 0, $count = 10, array $status_list = [self::CARD_STATUS_VERIFY_OK]) {
        $data = [
            'offset'      => $offset,
            'count'       => $count,
            'status_list' => json_encode($status_list),
        ];

        return self::_request(self::API_BATCH_GET_CARD, $data, $this->_access_token);
    }

    /**
     * 更改卡券信息接口 and 设置跟随推荐接口
     *
     * @param       $card_id   |卡券ID
     * @param       $type      |卡券类型
     * @param array $base_info |卡券基础信息字段
     * @param array $special   |特殊信息
     *
     * @return array
     */
    public function update($card_id, $type, $base_info = [], $special = []) {
        $data            = [];
        $data['card_id'] = $card_id;
        $data[$type]     = [];

        $cardInfo              = [];
        $cardInfo['base_info'] = $base_info;

        $data[$type] = array_merge($cardInfo, $special);

        return self::_request(self::API_UPDATE, $data, $this->_access_token);
    }

    /**
     * 更改Code接口
     *
     * @param        $code     |需变更的Code码
     * @param        $new_code |变更后的有效Code码
     * @param string $card_id  |卡券ID。自定义Code码卡券为必填
     *
     * @return array
     */
    public function updateCode($code, $new_code, $card_id = '') {
        $data = [
            'code'     => $code,
            'new_code' => $new_code,
            'card_id'  => $card_id,
        ];

        return self::_request(self::API_UPDATE_CODE, $data, $this->_access_token);
    }

    /**
     * 删除卡券接口
     *
     * @param $card_id |卡券ID
     *
     * @return array
     */
    public function delete($card_id) {
        $data = [
            'card_id' => $card_id,
        ];

        return self::_request(self::API_DELETE, $data, $this->_access_token);
    }

    /**
     * 设置卡券失效
     *
     * @param $code    |设置失效的Code码
     * @param $card_id |卡券ID
     * @param $reason  |失效理由
     *
     * @return array
     */
    public function disable($code, $card_id, $reason = '') {
        $data = [
            'code'    => $code,
            'card_id' => $card_id,
            'reason'  => $reason,
        ];

        return self::_request(self::API_DISABLE, $data, $this->_access_token);
    }

    /**
     * 拉取卡券概况数据
     *
     * @param     $begin_date  |查询数据的起始时间
     * @param     $end_date    |查询数据的截至时间
     * @param int $cond_source |卡券来源，0为公众平台创建的卡券数据、1是API创建的卡券数据
     *
     * @return array
     */
    public function getCardBizuinInfo($begin_date, $end_date, $cond_source = 0) {
        $data = [
            'begin_date'  => $begin_date,
            'end_date'    => $end_date,
            'cond_source' => $cond_source,
        ];

        return self::_request(self::API_GET_CARD_BIZUIN_INFO, $data, $this->_access_token);
    }

    /**
     * 获取免费券数据
     *
     * @param        $begin_date  |查询数据的起始时间
     * @param        $end_date    |查询数据的截至时间
     * @param int    $cond_source |卡券来源，0为公众平台创建的卡券数据、1是API创建的卡券数据
     * @param string $card_id     |卡券ID。填写后，指定拉出该卡券的相关数据
     *
     * @return array
     */
    public function getCardInfo($begin_date, $end_date, $cond_source = 0, $card_id = '') {
        $data = [
            'begin_date'  => $begin_date,
            'end_date'    => $end_date,
            'cond_source' => $cond_source,
            'card_id'     => $card_id,
        ];

        return self::_request(self::API_GET_CARD_INFO, $data, $this->_access_token);
    }

    /**
     * 拉取会员卡概况数据
     *
     * @param     $begin_date  |查询数据的起始时间
     * @param     $end_date    |查询数据的截至时间
     * @param int $cond_source |卡券来源，0为公众平台创建的卡券数据、1是API创建的卡券数据
     *
     * @return array
     */
    public function getMemberCardInfo($begin_date, $end_date, $cond_source = 0) {
        $data = [
            'begin_date'  => $begin_date,
            'end_date'    => $end_date,
            'cond_source' => $cond_source,
        ];

        return self::_request(self::API_GET_MEMBER_CARD_INFO, $data, $this->_access_token);
    }

    /**
     * 拉取单张会员卡数据
     *
     * @param        $begin_date |查询数据的起始时间
     * @param        $end_date   |查询数据的截至时间
     * @param string $card_id    |卡券id
     *
     * @return array
     */
    public function getMemberCardDetail($begin_date, $end_date, $card_id = '') {
        $data = [
            'begin_date' => $begin_date,
            'end_date'   => $end_date,
            'card_id'    => $card_id,
        ];

        return self::_request(self::API_GET_MEMBER_CARD_DETAIL, $data, $this->_access_token);
    }

    /**
     * 会员卡接口激活
     *
     * @param array $data
     *
     * @return array
     */
    public function activateMemberCard(array $data) {
        return self::_request(self::API_ACTIVATE_MEMBER_CARD, $data, $this->_access_token);
    }

    /**
     * 设置开卡字段接口
     *
     * @param       $card_id |卡券ID
     * @param array $data
     *
     * @return array
     */
    public function setActivateUserForm($card_id, array $data) {
        $data = array_merge(['card_id' => $card_id], $data);

        return self::_request(self::API_SET_ACTIVATE_USER_FORM, $data, $this->_access_token);
    }

    /**
     * 拉取会员信息接口
     *
     * @param $card_id |卡券ID
     * @param $code    |所查询用户领取到的code值
     *
     * @return array
     */
    public function getMemberCardUserInfo($card_id, $code) {
        $data = [
            'card_id' => $card_id,
            'code'    => $code,
        ];

        return self::_request(self::API_GET_MEMBER_CARD_USER_INFO, $data, $this->_access_token);
    }

    /**
     * 获取用户提交资料
     *
     * @param $activate_ticket
     *
     * @return array
     */
    public function getActivateTempInfo($activate_ticket) {
        $data = [
            'activate_ticket' => $activate_ticket,
        ];

        return self::_request(self::API_GET_ACTIVATE_TEMP_INFO, $data, $this->_access_token);
    }

    /**
     * 更新会员信息
     *
     * @param array $data
     *
     * @return array
     */
    public function updateMemberCardUser(array $data) {
        return self::_request(self::API_UPDATE_MEMBER_CARD_USER, $data, $this->_access_token);
    }

    /**
     * 支付后营销（可实现支付即会员功能）
     *
     * @param       $type        |营销规则类型，支付即会员填写RULE_TYPE_PAY_MEMBER_CARD
     * @param array $base_info   |营销规则结构体
     * @param array $member_rule |会员卡结构体
     *
     * @return array
     */
    public function addPayGiftCard($type, array $base_info, array $member_rule) {
        $data = [
            'rule_info' => [
                'type'        => $type,
                'base_info'   => $base_info,
                'member_rule' => $member_rule,
            ],
        ];

        return self::_request(self::API_ADD_PAY_GIFT_CARD, $data, $this->_access_token);

    }

    /**
     * 查询支付即会员规则详情
     *
     * @param $rule_id |要查询规则id
     *
     * @return array
     */
    public function getByIdPayGiftCard($rule_id) {
        $data = [
            'rule_id' => $rule_id,
        ];

        return self::_request(self::API_GET_BY_ID_PAY_GIFT_CARD, $data, $this->_access_token);
    }

    /**
     * 批量查询支付即会员规则
     *
     * @param $type      |类型
     * @param $effective |是否仅查询生效的规则
     * @param $offset    |起始偏移量
     * @param $count     |查询的数量
     *
     * @return array
     */
    public function batchGetPayGiftCard($type, $effective, $offset, $count) {
        $data = [
            'type'      => $type,
            'effective' => $effective,
            'offset'    => $offset,
            'count'     => $count,
        ];

        return self::_request(self::API_BATCH_GET_PAY_GIFT_CARD, $data, $this->_access_token);
    }

    /**
     * 开通券点账户
     *
     * @return array
     */
    public function activatePay() {
        return self::_request(self::API_ACTIVATE_PAY, [], $this->_access_token, \S\Http::METHOD_GET);
    }

    /**
     * 对优惠券批价
     *
     * @param $card_id  |需要来配置库存的card_id
     * @param $quantity |本次需要兑换的库存数目
     *
     * @return array
     */
    public function getPayPrice($card_id, $quantity) {
        $data = [
            'card_id'  => $card_id,
            'quantity' => $quantity,
        ];

        return self::_request(self::API_GET_PAY_PRICE, $data, $this->_access_token);
    }

    /**
     * 查询券点余额
     *
     * @return array
     */
    public function getCoinsInfo() {
        return self::_request(self::API_GET_COINS_INFO, [], $this->_access_token, \S\Http::METHOD_GET);
    }

    /**
     * 确认兑换库存
     *
     * @param $card_id  |需要来兑换库存的card_id
     * @param $order_id |本次需要兑换的库存数目
     * @param $quantity |仅可以使用上面得到的订单号，保证批价有效性
     *
     * @return array
     */
    public function confirmPay($card_id, $order_id, $quantity) {
        $data = [
            'card_id'  => $card_id,
            'order_id' => $order_id,
            'quantity' => $quantity,
        ];

        return self::_request(self::API_CONFIRM_PAY, $data, $this->_access_token);
    }

    /**
     * 充值券点
     *
     * @param $coin_count |需要充值的券点数目，1点=1元
     *
     * @return array
     */
    public function rechargePay($coin_count) {
        $data = [
            'coin_count' => $coin_count,
        ];

        return self::_request(self::API_RECHARGE_PAY, $data, $this->_access_token);
    }

    /**
     * 查询订单详情
     *
     * @param $order_id |上一步中获得的订单号，作为一次交易的唯一凭证
     *
     * @return array
     */
    public function getPayOrder($order_id) {
        $data = [
            'order_id' => $order_id,
        ];

        return self::_request(self::API_GET_PAY_ORDER, $data, $this->_access_token);
    }

    /**
     * 查询券点流水详情
     *
     * @param $data
     *
     * @return array
     */
    public function getPayOrderList($data) {
        return self::_request(self::API_GET_PAY_ORDER_LIST, $data, $this->_access_token);
    }

    /**
     * 获取卡券API_TICKET接口
     *
     * @return array
     */
    public function getTicket() {
        $data = ['type' => 'wx_card'];

        return self::_request(self::API_GET_TICKET, $data, $this->_access_token, \S\Http::METHOD_GET);
    }

    /**
     * Mark(占用)Code
     *
     * @param $code    |卡券的code码
     * @param $card_id |卡券的ID
     * @param $openid  |用券用户的openid
     * @param $is_mark |是否要mark（占用）这个code，填写true或者false，表示占用或解除占用
     *
     * @return array
     */
    public function markCode($code, $card_id, $openid, $is_mark) {
        $data = [
            'code'    => $code,
            'card_id' => $card_id,
            'openid'  => $openid,
            'is_mark' => $is_mark,
        ];

        return self::_request(self::API_MARK_CODE, $data, $this->_access_token);
    }


    /**
     * 更新会议门票
     *
     * @param $data
     *
     * @return array
     */
    public function updateMeetingTicketUser($data) {
        return self::_request(self::API_UPDATE_MEETING_TICKET_USER, $data, $this->_access_token);
    }

    /**
     * 更新电影门票
     *
     * @param $data
     *
     * @return array
     */
    public function updateMovieTicketUser($data) {
        return self::_request(self::API_UPDATE_MOVIE_TICKET_USER, $data, $this->_access_token);
    }

    /**
     * 更新飞机票
     *
     * @param $data
     *
     * @return array
     */
    public function checkInBoardingPass($data) {
        return self::_request(self::API_CHECK_IN_BOARDING_PASS, $data, $this->_access_token);
    }

    private static function _request($uri, $data = array(), $access_token = '', $http_method = \S\Http::METHOD_POST) {
        if ($http_method == \S\Http::METHOD_POST) {
            $data = Util::json_encode($data);
        }

        return Util::request($uri, $data, $access_token, $http_method);
    }

}
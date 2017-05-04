<?php
namespace Wechat;

/**
 * Class Pay
 *
 * @package     Wechat
 * @description 微信支付
 */
class Pay {

    const BASE_URL = 'https://api.mch.weixin.qq.com';
    const PATH_APPLY_ORDER = 'pay/unifiedorder';
    const PATH_QUERY_ORDER = 'pay/orderquery';
    const PATH_APPLY_REFUND = 'secapi/pay/refund';
    const PATH_QUERY_REFUND = 'pay/refundquery';
    const PATH_DOWNLOAD_BILL = 'pay/downloadbill';

    const TRADE_TYPE_JSAPI = 'JSAPI';
    const DEVICE_INFO_WEB = 'WEB';
    const CURRENCY_CNY = 'CNY';
    const LIMIT_PAY_NO_CREDIT = 'no_credit';
    const BILL_TYPE_ALL = 'ALL';

    const DEFAULT_TIMEOUT = 450000;  //默认超时时间
    const SUCCESS_CODE = 'SUCCESS';

    /**
     * @var array 三方请求配置
     */
    private $_config;

    /**
     * 设置请求配置
     *
     * @param array $config 微信支付配置
     *                      appid
     *                      mch_id
     *                      pay_key
     */
    public function __construct(array $config) {
        $this->_config = $config;
    }

    /**
     * 统一下单
     *
     * @param string $order_no          商户订单号
     * @param int    $amt               标价金额
     *                                  订单总金额，单位为分
     * @param string $description       商品描述
     *                                  商品简单描述，该字段请按照规范传递，具体请见参数规定
     *                                  https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=4_2
     * @param        $callback_url
     * @param bool   $credit_card_limit 是否支持信用卡 true-指定不能使用信用卡支付 false-支持信用卡
     * @param string $start_time        订单生成时间
     * @param string $end_time          订单失效时间
     *                                  注意: 最短失效时间间隔必须大于5分钟
     * @param string $trade_type        交易类型
     * @param array  $options           可选参数
     *
     *
     * @return array|bool
     */
    public function applyOrder($order_no, $amt, $description, $callback_url, $credit_card_limit = false,
                               $start_time, $end_time, $trade_type = self::TRADE_TYPE_JSAPI, array $options = array()) {
        $params = array(
            'body'             => $description, //商品描述
            'out_trade_no'     => $order_no, //商户订单号
            'total_fee'        => $amt, //总金额 单位分
            'spbill_create_ip' => \S\Util\Ip::getServerIp(), //支付终端IP
            'trade_type'       => $trade_type, //交易类型
            'notify_url'       => $callback_url, //交易结果回调地址
            'device_info'      => self::DEVICE_INFO_WEB, //设备号
            'fee_type'         => self::CURRENCY_CNY, //币种
        );
        if ($start_time) {
            $params["time_start"] = date("YmdHis", $start_time);
        }
        if ($end_time) {
            $params["time_expire"] = date("YmdHis", $end_time);
        }
        if ($credit_card_limit) {
            $params['limit_pay'] = self::LIMIT_PAY_NO_CREDIT;
        }
        if ((self::TRADE_TYPE_JSAPI == $trade_type) && $options["openid"]) {
            $params['openid'] = $options["openid"];
        }

        return $this->_request(self::PATH_APPLY_ORDER, $params);
    }

    /**
     * 查询订单
     *
     * @param string $request_order_id 商户订单号
     *                                 商户系统内部的订单号，请确保在同一商户号下唯一。
     * @param string $wechat_order_id  微信订单号
     *                                 微信的订单号，建议优先使用
     *
     * @return array
     * @throws Exception
     */
    public function queryOrder($request_order_id = null, $wechat_order_id = null) {
        $params = array();
        if ($wechat_order_id) {
            $params["transaction_id"] = $wechat_order_id;
        } else if ($request_order_id) {
            $params["out_trade_no"] = $request_order_id;
        } else {
            throw new Exception("微信订单号和商户订单号不能同时为空", Exception::DEFAULT_ERROR);
        }

        return $this->_request(self::PATH_QUERY_ORDER, $params);
    }

    /**
     * 申请退款
     *
     * @param string $request_id       商户退款单号
     *                                 商户系统内部的退款单号，商户系统内部唯一，同一退款单号多次请求只退一笔
     * @param int    $amt              退款金额
     *                                 退款总金额，订单总金额，单位为分，只能为整数，详见支付金额
     * @param int    $order_amt        订单金额
     *                                 订单总金额，单位为分，只能为整数，详见支付金额
     * @param string $request_order_id 商户订单号
     *                                 商户系统内部的订单号，请确保在同一商户号下唯一。
     * @param string $wechat_order_id  微信订单号
     *                                 微信的订单号，建议优先使用
     *
     * @return array
     * @throws Exception
     */
    public function refund($request_id, $amt, $order_amt, $request_order_id = null, $wechat_order_id = null) {
        $params = array(
            'out_refund_no' => $request_id,
            'total_fee'     => $order_amt,
            'refund_fee'    => $amt,
            'op_user_id'    => $this->_config["mch_id"],  //操作员帐号, 默认为商户号
        );
        if ($wechat_order_id) {
            $params["transaction_id"] = $wechat_order_id;
        } else if ($request_order_id) {
            $params["out_trade_no"] = $request_order_id;
        } else {
            throw new Exception("微信订单号和商户订单号不能同时为空", Exception::DEFAULT_ERROR);
        }

        return $this->_request(self::PATH_APPLY_REFUND, $params, true);
    }

    /**
     * 查询退款
     *
     * @param string $request_refund_id 商户退款单号
     *                                  商户侧传给微信的退款单号
     * @param string $wechat_refund_id  微信退款单号
     *                                  微信生成的退款单号，在申请退款接口有返回
     * @param string $request_order_id  商户订单号
     *                                  商户系统内部的订单号
     * @param string $wechat_order_id   微信订单号
     *
     * @return array
     * @throws Exception
     */
    public function queryRefund($request_refund_id = null, $wechat_refund_id = null, $request_order_id = null, $wechat_order_id = null) {
        $params = array();
        if ($wechat_refund_id) {
            $params['refund_id'] = $wechat_refund_id;
        } else if ($request_refund_id) {
            $params['out_refund_no'] = $request_refund_id;
        } else if ($wechat_order_id) {
            $params["transaction_id"] = $wechat_order_id;
        } else if ($request_order_id) {
            $params["out_trade_no"] = $request_order_id;
        } else {
            throw new Exception("微信退款单号、商户退款单号、微信订单号和商户订单号不能同时为空", Exception::DEFAULT_ERROR);
        }

        return $this->_request(self::PATH_QUERY_REFUND, $params);
    }

    /**
     * 下载对账单
     *
     * @param string $date       对账单日期
     *                           下载对账单的日期，格式：20140603
     * @param string $type       账单类型
     *                           ALL，返回当日所有订单信息，默认值
     *                           SUCCESS，返回当日成功支付的订单
     *                           REFUND，返回当日退款订单
     *                           RECHARGE_REFUND，返回当日充值退款订单（相比其他对账单多一栏“返还手续费”）
     * @param string $tar_type   压缩账单
     *                           非必传参数，固定值：GZIP，返回格式为.gzip的压缩包账单。不传则默认为数据流形式。
     *
     * @return array|mixed
     */
    public function downloadBill($date, $type = self::BILL_TYPE_ALL, $tar_type = null) {
        $params = array(
            'bill_date' => $date,
            'bill_type' => $type,
        );
        if ($tar_type) {
            $params['tar_type'] = $tar_type;
        }

        return $this->_request(self::PATH_DOWNLOAD_BILL, $params);
    }

    /**
     * 解析xml
     *
     * @param string $resp_msg
     *
     * @return array
     * @throws \Base\Exception\Data
     * @throws \S\Exception
     */
    public function parseResponse($resp_msg) {
        $params = (array)simplexml_load_string($resp_msg, null, LIBXML_NOCDATA);

        if ($params['return_code'] != self::SUCCESS_CODE) {
            throw new Exception($params["return_msg"], crc32($params["return_code"]));
        }

        $sign = $params['sign'];
        unset($params["sign"]);

        if (!($this->_checkSign($params, $sign, $this->_config['pay_key']))) {
            throw new Exception("签名验证错误", Exception::DEFAULT_ERROR);
        }

        return $params;
    }

    /**
     * 发送请求
     *
     * @param string $uri
     * @param array  $params
     * @param bool   $need_cert
     *
     * @return array|mixed
     * @throws \S\Exception
     */
    private function _request($uri, array $params, $need_cert = false) {
        $params["appid"]     = $this->_config['appid'];
        $params["mch_id"]    = $this->_config['mch_id'];
        $params["nonce_str"] = \S\Util\Rand::alnum(32);
        $params["sign"]      = $this->_getSign($params, $this->_config['pay_key']);  //签名

        $params      = array("xml" => $params);
        $request_xml = self::_arrayToXml($params);

        $cert_base_path = APP_CONF . '/security/' . \Core\Env::getEnvName() . '/wechat/';
        $options        = array(
            'timeout' => self::DEFAULT_TIMEOUT,
        );
        if ($need_cert) {
            $options['cert']    = $cert_base_path . 'cert/' . '.pem';
            $options['ssl_key'] = $cert_base_path . 'key/' . '.pem';
        }

        $response = (new \S\Http(self::BASE_URL))->request(\S\Http::METHOD_POST, $uri, $request_xml, $options);
        $result   = self::parseResponse($response);

        return $result;
    }

    /**
     * 将数组转成xml格式字符串(有一个问题,空节点会返回 <key/>)
     *
     * @param array $params
     *
     * @return string
     */
    private static function _arrayToXml(array $params) {
        $xml = "";
        foreach ($params as $key => $value) {
            if ("" === $value) {
                $xml .= "<{$key}/>";
            } else {
                $xml .= "<{$key}>";
                if (is_array($value) && !empty($value)) {
                    $xml .= self::_arrayToXml($value);
                } else {
                    $xml .= $value;
                }
                $xml .= "</{$key}>";
            }
        }

        return $xml;
    }

    /**
     * 生成签名
     *
     * @param array $params
     * @param       $key
     *
     * @return string
     */
    private function _getSign(array $params, $key) {
        //签名步骤一：按字典序排序参数
        ksort($params);
        $string = "";
        foreach ($params as $index => $value) {
            if ($value != "" && !is_array($value)) {
                $string .= "{$index}={$value}&";
            }
        }

        //签名步骤二：在string后加入KEY
        $string .= "key={$key}";
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $sign = strtoupper($string);

        return $sign;
    }

    /**
     * 签名校验
     *
     * @param $params
     * @param $sign
     * @param $key
     *
     * @return bool
     * @throws \S\Exception
     */
    private function _checkSign(array $params, $sign, $key) {
        $make_sign = self::_getSign($params, $key);

        return ($make_sign == $sign);
    }

}
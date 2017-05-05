<?php
/**
 * 微信服务配置
 *
 * 微信公众号名称在modules\Wechat\Bootstrap中定义, 存在多个公众号情况下注意区分
 */
return array(
    'demo' => array(
        'appid'            => 'wxf8548afb63076dce',
        'appsecret'        => '88699969a0381d3b0d5aaf15cb729f19',
        'token'            => 'demo_token',
        'encoding_aes_key' => 'WJl3gf4iTGv2NILRtETI5NCcsBmEl6lMUXdjiYQBi5u',

        // 微信支付使用
        'mch_id'           => '',
        'pay_key'          => '',
    ),
);
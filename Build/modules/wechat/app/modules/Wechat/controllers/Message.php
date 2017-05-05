<?php

/**
 * Class Controller_Message
 * @description 微信推送消息接收入口
 *              填写微信推送消息接口配置信息的url时需要附带wechat_name参数, 用来标识公众号
 *              URL示例: http(s)://@appdomain@/wechat/message/?wechat_name=demo (demo为示例微信公众号名称, 在modules\Wechat\Bootstrap中定义)
 */
class Controller_Message extends \Modules\Wechat\Controllers\Message {}
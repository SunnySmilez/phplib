<?php
namespace Wechat\Model\Service\Message;

use Modules\Wechat\Model\Service\Message\Dispatcher as ServiceDispatcher;

/**
 * Class Register
 *
 * @package     Wechat\Model\Service\Message
 * @description 微信推送消息处理handler注册服务
 *              不同公众号的消息handler可以不同
 */
class Register {

    public static function register() {
        // 关注事件
        ServiceDispatcher::register(WECHAT_NAME_DEMO,
            array(
                'MsgType' => \Wechat\Message\Push::MSG_TYPE_EVENT,
                'Event'   => \Wechat\Message\Push::EVENT_SUBSCRIBE,
            ),
            '\\Wechat\\Model\\Service\\Message\\Handler\\Event\\Subscribe'
        );
        // 取消关注事件
        ServiceDispatcher::register(WECHAT_NAME_DEMO,
            array(
                'MsgType' => \Wechat\Message\Push::MSG_TYPE_EVENT,
                'Event'   => \Wechat\Message\Push::EVENT_UNSUBSCRIBE,
            ),
            '\\Wechat\\Model\\Service\\Message\\Handler\\Event\\UnSubscribe'
        );
        // 用户文本输入
        ServiceDispatcher::register(WECHAT_NAME_DEMO,
            array(
                'MsgType' => \Wechat\Message\Push::MSG_TYPE_TEXT,
            ),
            '\\Wechat\\Model\\Service\\Message\\Handler\\Text'
        );
    }

}
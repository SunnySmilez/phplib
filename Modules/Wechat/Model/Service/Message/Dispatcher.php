<?php
namespace Modules\Wechat\Model\Service\Message;

/**
 * Class Dispatcher
 *
 * @package     Modules\Wechat\Model\Service\Message
 * @description 消息分发
 *
 * 以服务的方式统一注册, 没有注册的返回默认
 */
class Dispatcher {

    /**
     * @var string 官方推荐默认回复的响应消息
     */
    const DEFAULT_RESPONSE = 'success';

    /**
     * @var \Wechat\Message\Push 微信推送过来的请求消息
     */
    protected static $request = null;
    /**
     * @var string 向微信回复的响应消息
     */
    protected static $response = self::DEFAULT_RESPONSE;
    /**
     * @var array 消息处理handler列表, 不同公众号需要注意区分
     */
    private static $_handlers = array();

    /**
     * 注册分发服务
     *
     * 根据message里的属性
     *
     * 上层注册示例:
     *
     * Dispatcher::register(
     *     array(
     *         'MsgType'=>'event',
     *         'Event'=>'subscribe'
     *     ),
     *     '\\Wechat\\Model\\Service\\Message\\Handler\\Event\\Subscibe',
     *     'wechat_demo'
     * );
     *
     * @param string $wechat_name 微信公众号名称标识, 在modules\Wechat\Bootstrap中定义
     * @param array  $property    消息类别
     * @param string $class       消息handler类名
     *
     * @return array
     */
    public static function register($wechat_name, array $property, $class) {
        self::$_handlers[$wechat_name][] = array(
            'property' => $property,
            'class'    => $class,
        );

        return self::$_handlers;
    }

    /**
     * 执行分发服务
     * 按照属性分发到指定的类
     *
     * @return string
     */
    public static function run(\Wechat\Message\Push $request) {
        self::$request = $request;
        $wechat_name   = \S\Request::get('wechat_name');

        if (self::$_handlers[$wechat_name]) {
            foreach (self::$_handlers[$wechat_name] as $handler) {
                //属性判断
                foreach ($handler['property'] as $key => $type) {
                    if (strtolower($request->$key) != strtolower($type)) {
                        continue 2;
                    }
                }

                //全部符合调用相应handler
                if (!class_exists($handler['class'])) {
                    return self::DEFAULT_RESPONSE;
                }
                /** @var \Modules\Wechat\Model\Service\Message\Handler\Base $handler_class */
                $handler_class = new $handler['class'];
                $response      = $handler_class->run($request);
                if ($response) {
                    self::$response = $response;
                    break;
                }
            }
        }

        return self::$response;
    }

}
<?php
namespace Wechat\Model\Service\Message\Handler;

/**
 * Class Text
 *
 * @package     Wechat\Model\Service\Message\Handler
 * @description 文本消息处理示例
 */
class Text extends \Modules\Wechat\Model\Service\Message\Handler\Base {

    public function action() {
        return $this->request->responseText('文本消息处理示例, 您发送的文本消息为: ' . $this->request->Content);
    }

}
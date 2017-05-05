<?php
namespace Wechat\Model\Service\Message\Handler\Event;

use Modules\Wechat\Model\Data\User as DataUser;
use Modules\Wechat\Model\Data\Config as DataConfig;

/**
 * Class Subscribe
 *
 * @package     Wechat\Model\Service\Message\Handler\Event
 * @description 关注事件
 */
class Subscribe extends \Modules\Wechat\Model\Service\Message\Handler\Base {

    public function action() {
        $openid = $this->request->FromUserName;

        $access_token = (new DataConfig())->getAccessToken($this->wechat_name);
        $user_info    = (new \Wechat\User($access_token))->getUserInfo($openid);

        (new DataUser())->save($this->wechat_name, $openid, $user_info);

        return $this->request->responseText("你好, {$this->request->FromUserName}。 欢迎关注!");
    }

}
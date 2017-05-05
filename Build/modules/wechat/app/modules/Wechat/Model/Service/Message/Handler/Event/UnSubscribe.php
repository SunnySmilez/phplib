<?php
namespace Wechat\Model\Service\Message\Handler\Event;

use Modules\Wechat\Model\Data\User as DataUser;

/**
 * Class UnSubscribe
 *
 * @package     Wechat\Model\Service\Message\Handler\Event
 * @description 取消关注事件
 */
class UnSubscribe extends \Modules\Wechat\Model\Service\Message\Handler\Base {

    public function action() {
        $openid = $this->request->FromUserName;

        $user_info = array(
            'subscribe' => DataUser::STATUS_UNSUBSCRIBE,
        );
        (new DataUser())->update($this->wechat_name, $openid, $user_info);
    }

}
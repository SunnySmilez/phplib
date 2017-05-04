<?php
namespace Modules\Wechat\Model\Data;

use Modules\Wechat\Model\Data\Config as DataConfig;
use Modules\Wechat\Model\Dao\Db\Log as DbLog;

class Log {

    /**
     * 记录微信推送消息日志
     *
     * @param        $wechat_name
     * @param string $openid   用户openid
     * @param string $msg_type 消息类型
     * @param        $ctime
     * @param array  $detail
     *
     * @return bool
     */
    public function add($wechat_name, $openid, $msg_type, $ctime, array $detail) {
        $appid = DataConfig::getBaseConfig($wechat_name)['appid'];

        return (new DbLog())->add($appid, $openid, $msg_type, $ctime, $detail);
    }

}
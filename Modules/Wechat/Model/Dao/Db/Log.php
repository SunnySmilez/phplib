<?php
namespace Modules\Wechat\Model\Dao\Db;

/**
 * Class Log
 *
 * @package     Modules\Wechat\Model\Dao\Db
 * @description 微信消息日志表
 */
class Log extends Db {

    public function __construct() {
        $this->table = self::table('log');
    }

    /**
     * 记录微信推送消息日志
     *
     * @param        $appid
     * @param string $openid   用户openid
     * @param string $msg_type 消息类型
     * @param array  $detail
     *
     * @return bool
     */
    public function add($appid, $openid, $msg_type, $ctime, array $detail) {
        $params = array(
            "appid"   => $appid,
            "openid"  => $openid,
            "msgtype" => $msg_type,
            "detail"  => json_encode($detail),
            'ctime'   => $ctime,
        );

        return self::db()->insert($this->table, $params);
    }

}
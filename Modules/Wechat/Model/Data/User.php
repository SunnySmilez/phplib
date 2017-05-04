<?php
namespace Modules\Wechat\Model\Data;

use Modules\Wechat\Model\Data\Config as DataConfig;
use Modules\Wechat\Model\Dao\Db\User as DbUser;

/**
 * Class User
 *
 * @package Modules\Wechat\Model\Data
 * @description 用户信息
 */
class User {

    const STATUS_SUBSCRIBE   = 1;  // 关注状态
    const STATUS_UNSUBSCRIBE = 0;  // 取消关注状态

    /**
     * 保存用户信息
     *
     * @param       $wechat_name
     * @param       $openid
     * @param array $data
     *
     * @return bool|int
     */
    public function save($wechat_name, $openid, array $data) {
        if ($this->getByOpenId($wechat_name, $openid)) {
            return $this->update($wechat_name, $openid, $data);
        } else {
            $data['openid'] = $openid;

            return $this->add($wechat_name, $data);
        }
    }

    public function add($wechat_name, array $data) {
        $appid  = DataConfig::getBaseConfig($wechat_name)['appid'];

        return (new DbUser())->add($appid, $data);
    }

    public function update($wechat_name, $openid, array $data) {
        $appid  = DataConfig::getBaseConfig($wechat_name)['appid'];

        return (new DbUser())->update($appid, $openid, $data);
    }

    /**
     * 根据openid获取用户信息
     *
     * @param string $wechat_name 微信公众号名称
     * @param string $openid      用户openid
     *
     * @return array|mixed
     */
    public function getByOpenId($wechat_name, $openid) {
        $appid  = DataConfig::getBaseConfig($wechat_name)['appid'];

        return (new DbUser())->getByAppidOpenid($appid, $openid);
    }

}
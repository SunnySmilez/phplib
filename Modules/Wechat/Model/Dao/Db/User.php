<?php
namespace Modules\Wechat\Model\Dao\Db;

class User extends Db {

    //表中存储的json串格式
    const COL_JSON = [
        'privilege',
        'tagid_list',
    ];

    public function __construct() {
        $this->table = self::table('user');
    }

    public function add($appid, array $data) {
        $data['appid'] = $appid;

        return self::db()->insert($this->table, self::jsonEncode($data), true, true);
    }


    public function update($appid, $openid, array $data) {
        $condition = array(
            'appid'  => $appid,
            'openid' => $openid,
        );

        return self::db()->update($this->table, self::jsonEncode($data), $condition);
    }

    public function getByAppidOpenid($appid, $openid) {
        $params = array(
            'appid'  => $appid,
            'openid' => $openid,
        );

        $ret = self::db()->queryone($this->table, $params);

        return $ret ? self::jsonDecode($ret) : $ret;
    }


    /**
     * 针对复杂结构数据进行json编码
     *
     * @param $data
     *
     * @return mixed
     */
    public static function jsonEncode($data) {
        foreach ($data as $key => $value) {
            if (in_array($key, static::COL_JSON)) {
                $data[$key] = json_encode($value);
            }
        }

        return $data;
    }

    /**
     * 针对复杂结构数据进行json解码
     *
     * @param $data
     *
     * @return mixed
     */
    public static function jsonDecode($data) {
        foreach ($data as $key => $value) {
            if (in_array($key, static::COL_JSON)) {
                $data[$key] = json_decode($value, true);
            }
        }

        return $data;
    }

}
<?php
namespace Wechat;

/**
 * Class POI
 *
 * @package     Wechat
 * @description 门店
 */
class POI {

    const API_ADD = "http://api.weixin.qq.com/cgi-bin/poi/addpoi";
    const API_GET = 'http://api.weixin.qq.com/cgi-bin/poi/getpoi';
    const API_GET_LIST = 'http://api.weixin.qq.com/cgi-bin/poi/getpoilist';
    const API_UPDATE = 'http://api.weixin.qq.com/cgi-bin/poi/updatepoi';
    const API_DELETE = 'http://api.weixin.qq.com/cgi-bin/poi/delpoi';
    const API_GET_WX_CATEGORY = 'http://api.weixin.qq.com/cgi-bin/poi/getwxcategory';

    private $_access_token;

    public function __construct($access_token) {
        $this->_access_token = $access_token;
    }

    /**
     * 添加门店
     *
     * @param array $data
     *
     * @return array
     */
    public function add(array $data) {
        return Util::request(self::API_ADD, $data, $this->_access_token);
    }

    /**
     * 查询门店
     *
     * @param $poi_id |微信的门店ID，微信内门店唯一标示ID
     *
     * @return array
     */
    public function get($poi_id) {
        $data = [
            'poi_id' => $poi_id,
        ];

        return Util::request(self::API_GET, $data, $this->_access_token);
    }

    /**
     * 查询门店列表
     *
     * @param $begin |开始位置，0 即为从第一条开始查询
     * @param $limit |返回数据条数，最大允许50，默认为20
     *
     * @return array
     */
    public function getList($begin, $limit) {
        $data = [
            'begin' => $begin,
            'limit' => $limit,
        ];

        return Util::request(self::API_GET, $data, $this->_access_token);
    }

    /**
     * 修改门店服务信息
     *
     * @param       $poi_id |微信的门店ID，微信内门店唯一标示ID
     * @param array $data
     *
     * @return array
     */
    public function update($poi_id, array $data) {

        $data = [
            'business' => [
                'base_info' => array_merge($data, ['poi_id' => $poi_id]),
            ],
        ];

        return Util::request(self::API_UPDATE, $data, $this->_access_token);

    }

    /**
     * 删除门店
     *
     * @param $poi_id |微信的门店ID，微信内门店唯一标示ID
     *
     * @return array
     */
    public function delete($poi_id) {
        $data = [
            'poi_id' => $poi_id,
        ];

        return Util::request(self::API_DELETE, $data, $this->_access_token);
    }

    /**
     * 门店类目表
     *
     * @return array
     */
    public function getWXCategory() {
        return Util::request(self::API_GET_WX_CATEGORY, [], $this->_access_token, \S\Http::METHOD_GET);

    }
}
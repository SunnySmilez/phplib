<?php

namespace Base\Dao;

use Base\Exception\Dao as Exception;

/**
 * Class Cache
 *
 * @package     Base\Dao
 * @description 缓存服务基类
 *              使用示范
 *
 *              namespace Dao\Cache
 *              /**
 *               * Class Foo
 *               * @ description Foo类展示了Dao\Cache下的缓存服务的标准样例
 *               *               建议添加 @ method 显式声明对外提供的方法以便ide正确的识别
 *               *
 *               * @ method setUserInfo($uid, array $info)
 *               * @ method getUserInfo($uid)
 *               * @ method delUserInfo($uid)
 *               *
 *               * @ method msetUserInfo(array $infos)
 *               * @ method mgetUserInfo(array $uids)
 *               * @ method mdelUserInfo(array $uids)
 *               * /
 *              class Foo extends \Base\Dao\Cache{
 *
 *                  public function __construct(){
 *                      $this->setConfig('UserInfo', 'USER_INFO', 300);
 *                  }
 *
 *              }
 *
 *              //单条目操作样例
 *              $uid = 'demo_uid';
 *              $info = array(
 *                  'name' => 'demo_name',
 *                  'gender' => 1,
 *              );
 *              $cache = new \Dao\Cache\Foo();
 *              $cache->setUserInfo($uid, $info);
 *              $user_info = $cache->getUserInfo($uid);
 *              $cache->delUserInfo($uid);
 *
 *              //批量操作样例
 *              $cache->msetUserInfo(array(
 *                  'uid1' => array('name' => 'foo_name', 'gender' => 1,),
 *                  'uid2' => array('name' => 'bar_name', 'gender' => 2, 'ext' => array('latin word ext info'),),
 *                  'uid3' => array('name' => 'boo name', 'gender' => 3, 'ext' => array('汉字扩展信息'),),
 *              ));
 *              $cache->mgetUserInfo(array('uid1', 'uid2', 'uid3'));
 *              $cache->mdelUserInfo(array('uid1', 'uid2', 'uid3'));
 */
class Cache {

    const DEFAULT_TTL = 86400;  //默认缓存对象过期时间: 1day=3600s * 24

    /**
     * @var array 定义支持的缓存操作
     */
    protected static $_functions = array('get', 'set', 'del', 'mget', 'mset', 'mdel');

    protected $pool_type = \S\Cache\Cache::TYPE_DEFAULT;  //缓存类型, 参考\S\Cache\Cache缓存类型常量定义
    protected $pool_name = \S\Cache\Cache::NAME_DEFAULT; //缓存配置名称, 参考\S\Cache\Cache常量定义

    /**
     * 魔术方法, 所有缓存操作将转到此方法中进行处理
     *
     * @param string $name      缓存操作
     *                          以: get set del mget mset mdel 等标准缓存操作开头定义的方法, 操作以外的部分将被看作缓存标识
     *                          e.g.
     *                          以UserInfo作为缓存标识定义的方法列表:
     *                          getUserInfo setUserInfo delUserInfo mgetUserInfo msetUserInfo mdelUserInfo
     * @param array  $arguments 操作参数
     *                          get|mget|del|mdel: 第0个参数代表键
     *                          set|mset         : 第0个参数代表键, 第1个参数代表值
     *
     * @return mixed 操作结果, 实际意义视具体操作而定
     * @throws Exception
     */
    public function __call($name, $arguments) {
        $function = null;
        foreach (self::$_functions as $need_function) {
            if (0 === stripos($name, $need_function)) {
                $function = $need_function;
            }
        }
        if (!$function) {
            throw new Exception("unsupported function: $function");
        }

        $cache_id = strtolower(substr($name, strlen($function)));
        if (empty($this->$cache_id)) {
            throw new Exception("$cache_id not configured");
        }
        if (!($this->pool_name && $this->pool_type)) {
            throw new Exception("class need set pool_name and pool_type");
        }

        $cache = \S\Cache\Cache::pool($this->pool_type, $this->pool_name);

        if ('get' == $function) {

            $key = $this->getKey($cache_id, $arguments[0]);
            $ret = $cache->get($key);
            $ret = ($ret ? json_decode($ret, true) : false);
        } else if ('set' == $function) {

            $config = $this->$cache_id;
            $key    = $this->getKey($cache_id, $arguments[0]);
            $data   = json_encode($arguments[1]);
            $ret    = ($cache->set($key, $data, $config['ttl']) ? true : false);
        } else if ('del' == $function) {

            $key = $this->getKey($cache_id, $arguments[0]);
            $ret = ($cache->del($key) ? true : false);
        } else if ("mget" == $function) {

            $keys = $this->getKey($cache_id, $arguments[0]);
            $vals = $cache->mget($keys);

            $ret = array();
            foreach ($keys as $idx => $key) {
                $val       = $vals[$idx];
                $ret[$key] = ($val ? json_decode($val, true) : $val);
            }
        } else if ('mset' == $function) {

            $config = $this->$cache_id;
            $vals   = array();
            foreach ($arguments[0] as $key => $val) {
                $vals[$this->getKey($cache_id, $key)] = json_encode($val);
            }
            $ret = ($cache->mset($vals, $config['ttl']) ? true : false);
        } else if ('mdel' == $function) {

            $keys = $this->getKey($cache_id, $arguments[0]);
            $ret  = ($cache->mdel($keys) ? true : false);
        } else {
            $ret = false;
        }

        return $ret;
    }

    /**
     * 获取键
     *
     * @param string       $cache_id 缓存标识
     * @param string|array $key      键
     *
     * @return string|array 添加前缀后的键
     */
    protected function getKey($cache_id, $key) {
        $prefix = ($this->$cache_id)['prefix'];

        if (is_array($key)) {
            foreach ($key as &$item) {
                $item = $prefix . '_' . $item;
            }

            return $key;
        }

        return $prefix . '_' . $key;
    }

    /**
     * 设置缓存配置
     *
     * @param string $cache_id 缓存标识
     * @param string $prefix   键前缀
     * @param int    $ttl      default 864000 缓存时效, 默认1天
     */
    protected function setConfig($cache_id, $prefix, $ttl = self::DEFAULT_TTL) {
        $cache_id        = strtolower($cache_id);
        $this->$cache_id = array(
            'prefix' => $prefix,
            'ttl'    => $ttl,
        );
    }

}
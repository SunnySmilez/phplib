<?php
namespace Data;
/**
 * Class Demo
 *
 * @package Data
 * @description 数据服务层示范
 *
 *              备注:
 *
 *              1. Data层是介于业务层(Service)和数据访问层(Dao)的桥梁;
 *              2. Data层聚合多个Dao层(Db|Cache etc.)的数据并对结果进行汇总, 进而向Service层提供整齐、统一的数据操作结果;
 *              3. Service层不关心数据的来源(关系型数据库, NoSQL数据库, 缓存等), 只关心业务流程需要的数据结果, Data层正是为了此目标而产生的;
 *              4. Data层能够调用所有Dao层的服务以及其他Data层的服务, 禁止调用Service层以及Controller层的服务;
 *              5. Data层仅包括数据操作以及数据逻辑, 禁止加入复杂业务逻辑;
 */
class Demo {

    /**
     * 保存数据
     *
     * 当保存数据流程涉及缓存写入操作时, 必须先进行持久化操作, 再进行缓存写入, 避免数据不一致问题
     * 多源数据的保存以持久化操作为准
     *
     * @param array $data 待保存数据, 格式:
     *                    array(
     *                        'id'   => 1,
     *                        'name' => 'foo',
     *                    )
     *
     * @return bool
     */
    public function add(array $data) {
        $ret = (new \Dao\Db\Demo())->add($data);
        if ($ret) {
            (new \Dao\Cache\Demo())->setData($data['id'], $data);
        }

        return $ret;
    }

    /**
     * 更新数据
     *
     * 当更新的数据存在于缓存中时, 必须先删除缓存数据, 再进行持久化操作, 避免后续读操作出现脏数据
     * 注意多源数据的同步更新, 版本以持久化数据源为准
     *
     * @param string $id
     * @param array $data
     *
     * @return bool
     */
    public function update($id, array $data){
        $cache = new \Dao\Cache\Demo();
        $cache->delData($id);

        $ret = (new \Dao\Db\Demo())->update($id, $data);
        if ($ret) {
            $cache->setData($id, $data);
        }

        return $ret;
    }

    /**
     * 获取数据
     *
     * 以下为常见获取数据流程:
     *
     * 1. 读取缓存数据;
     * 2. 缓存数据不存在时读取持久化数据, 并写入缓存;
     * 3. 返回结果;
     *
     * 设计缓存操作时应考虑数据的命中率, 读操作频繁且命中率高的数据应同步到缓存, 减少持久化数据库压力
     *
     * @param string $id
     *
     * @return array
     */
    public function get($id){
        $cache = new \Dao\Cache\Demo();
        $data = $cache->getData($id);

        if (!$data) {
            $data = (new \Dao\Db\Demo())->get($id);
            $cache->setData($id, $data);
        }

        return $data;
    }

}
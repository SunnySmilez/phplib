<?php
namespace S\Db;

use \S\Strace\Timelog as Timelog;

/**
 * 表格存储
 * NoSQL 分布式存储
 * 支持稀疏表 非结构化数据
 *
 * 写入一行数据
 * @method putRow($table, array $primary_key, array $data)
 *
 * @param string $table
 * @param array  $primary      主键
 *                             array(
 *                                  'PK0' => 123,
 *                             )
 * @param array  $data         列属性
 *                             array(
 *                                  'attr0' => 789,             // 如果该列已经存在，则更新该列的值
 *                                  'new_col' => 'abc',      // 如果该列不存在，则追加新列
 *                             )
 * @return bool
 * @throws \S\Exception
 *
 *
 * 获取行
 * @method getRow($table, array $primary_key, array $cols = array())
 * @param        $cols         array 需要获取的列 传入array()默认获取所有列
 *                             array(
 *                                  'col1', 'col2'
 *                             )
 * @return                      array(
 *                                  'col1' => 'value1',
 *                                  'col2' => 'value2',
 *                             )
 * @throws \S\Exception
 *
 *
 * 删除行
 * @method deleteRow($table, array $primary_key)
 * @return bool
 * @throws \S\Exception
 *
 *
 * 批量写入多行 单次限制在100条内
 * @method batchPutRow($table, array $batch_data)
 * @param array  $batch_data
 *                             array(
 *                                  array(
 *                                      'primary' => array(
 *                                          'PK0' => 123,
 *                                          'PK1' => 'abc',
 *                                  ),
 *                                      'columns' => array(
 *                                          'attr0' => '789',
 *                                          'attr1' => '123',
 *                                  )
 *                             ),
 *                             array(
 *                                      'primary' => array(
 *                                          'PK0' => 234,
 *                                          'PK1' => 'bcd',
 *                                      ),
 *                                      'columns' => array(
 *                                          'attr0' => '678',
 *                                          'attr1' => '234',
 *                                      )
 *                             )
 * @return bool
 * @throws \S\Exception
 *
 *
 * 批量读取多行 单次限制100条内
 * @method batchGetRow($table, array $primary_keys, array $cols = array())
 * @param array  $primary_keys 主键数组
 *                             array(
 *                                  array('PK0' => 1),  // 第一行
 *                                  array('PK0' => 2),   // 第二行
 *                             )
 * @return
 *                             array (
 *                                  array (
 *                                      'primary' =>
 *                                           array (
 *                                              'PK0' => 1
 *                                      ),
 *                                      'columns' =>
 *                                          array (
 *                                              'attr0' => '123',
 *                                              'attr1' => 'abc',
 *                                      ),
 *                                  ),
 *                                  array (
 *                                      'primary' =>
 *                                          array (
 *                                              'PK1' => 2,
 *                                          ),
 *                                      'columns' =>
 *                                          array (
 *                                              'attr0' => '456',
 *                                              'attr1' => 'def',
 *                                          ),
 *                                  ),
 *                             )
 * @throws \S\Exception
 *
 *
 * 批量获取一个范围的数据
 * ! hbase/ots 主键排序的实现机制不同 使用前需注意 (hbase是按主键值的字典序排序 ots则由Plainbuffer编码 @link https://help.aliyun.com/document_detail/27309.html?spm=5176.doc50127.6.608.eVNtrr)
 * @method getRange($table, array $start_primary_key, array $end_primary_key, array $cols = array(), $limit = 100)
 * @param array $start_primary_key 扫描开始主键 若该行存在，则响应中一定会包含此行
 * @param array $end_primary_key   扫描结束主键 无论该行是否存在，响应中都不会包含此行
 * @param int   $limit 获取条数
 * @return array 格式同batchGetRow的返回格式
 * @throws \S\Exception
 */
class Table {

    const TYPE_OTS = 'ots';
    const TYPE_HBASE = 'hbase';
    const TYPE_HBASEREST = 'hbaserest';

    const NAME_DEFAULT = 'common';

    protected $type;
    protected $name;
    protected $table_client;

    public function __construct($type, $name = self::NAME_DEFAULT) {
        $this->type = $type;
        $this->name = $name;
        //获取配置
        $config = \S\Config::confServer('table.'.$this->type.".".$this->name);
        if (!$config) {
            throw new \S\Exception(get_class($this).' need be configured. Config : '.$this->name);
        }
        //获取实例
        $handler = __NAMESPACE__."\\Table\\".ucfirst($this->type);
        $this->table_client = new $handler($name, $config);
    }

    public function __destruct() {
        if(method_exists($this->table_client, "close")){
            $this->table_client->close();
        }
    }

    /** 调用方法封装 记录strace
     *
     * @param $name
     * @param $args
     * @return mixed
     * @throws \S\Exception
     */
    public function __call($name, $args) {
        Timelog::instance()->resetTime();
        try {
            $ret = call_user_func_array(array($this->table_client, $name), $args);
        } catch (\Exception $e) {
            if ($e instanceof \S\Exception) {
                throw $e;
            } else {
                throw new \S\Exception($e->getCode().' '.$e->getMessage());
            }
        }
        $this->_setStrace($name, $args);
        return $ret;
    }

    private function _setStrace($function, $params) {
        Timelog::instance()->log($this->type, array(
            'class'    => __CLASS__,
            'method'   => $function,
            'resource' => $this->name,
            'params'   => $params,
        ));
    }
}
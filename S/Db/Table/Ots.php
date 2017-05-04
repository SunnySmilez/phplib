<?php
namespace S\Db\Table;

use \OTS\OTSClient as OTSClient;

include_once(PHPLIB."/Ext/GuzzleHttp/Promise/functions_include.php");
include_once(PHPLIB."/Ext/GuzzleHttp/functions_include.php");
include_once(PHPLIB."/Ext/GuzzleHttp/Psr7/functions_include.php");
include_once(PHPLIB."/Ext/Aliyun/OTS/functions.php");
include_once(PHPLIB."/Ext/Aliyun/OTS/ProtoBuffer/pb_message.php");
include_once(PHPLIB."/Ext/Aliyun/OTS/ProtoBuffer/pb_proto_ots.php");

/**
 * 阿里云表格存储服务ots
 */
class Ots implements TableInterface {

    protected static $ots_client = array();
    protected $name;
    protected $need_config = array(
        "EndPoint"        => "",
        "AccessKeyID"     => "",
        "AccessKeySecret" => "",
        "InstanceName"    => "",
    );

    public function __construct($name, array $config) {
        $this->name = $name;

        $diff = array_diff_key($this->need_config, $config);
        if ($diff) {
            throw new \S\Exception("ots配置文件数据项缺失");
        }

        if (!isset(self::$ots_client[$this->name])) {
            /**
             * @var \OTS\OTSClient $ots_client [$this->name]
             */
            self::$ots_client[$this->name] = new OTSClient($config);
        }
    }

    /**
     * 写入一行数据。如果该行已经存在，则覆盖原有数据。返回该操作消耗的CU。
     *
     * @param string $condition |若期待该行不存在但该行已存在，则会插入失败, 返回错误；反之亦然
     *                          IGNORE 表示不做行存在性检查
     *                          EXPECT_EXIST 表示期望行存在
     *                          EXPECT_NOT_EXIST 表示期望行不存在
     * @return bool
     */
    public function putRow($table, array $primary, array $columns, $condition = 'IGNORE') {
        $request = array(
            'table_name'        => $table,
            'condition'         => $condition,
            'primary_key'       => $primary,
            'attribute_columns' => $columns,
        );
        $response = $this->_otsFunction('putRow', array($request));
        return (bool)$response['consumed']['capacity_unit']['write'];
    }

    /**
     * 写入多行数据。 请注意，BatchWriteRow在部分行读取失败时，会在返回的$response中表示，而不是抛出异常。请参见样例：处理BatchWriteRow的返回。
     * 一次操作请求操作的行数:不超过200行
     *
     * @param        $table
     * @param        $batch_datas
     * @param string $condition |若期待该行不存在但该行已存在，则会插入失败, 返回错误；反之亦然
     *                          IGNORE 表示不做行存在性检查
     *                          EXPECT_EXIST 表示期望行存在
     *                          EXPECT_NOT_EXIST 表示期望行不存在
     * @return mixed
     * [
     *     {
     *         "is_ok": true, //成功的的返回
     *         "consumed": {
     *             "capacity_unit": {
     *                 "read": 0,
     *                 "write": 1
     *             }
     *         }
     *     },
     *     {
     *         "is_ok": false, //失败的返回
     *         "error": {
     *             "code": "OTSInvalidPK",
     *             "message": "Primary key schema mismatch."
     *         }
     *     }
     * ]
     *
     * @throws \S\Exception
     */
    public function batchPutRow($table, $batch_datas, $condition = 'IGNORE') {
        $put_rows = array();
        foreach ($batch_datas as $k) {
            $put_rows[] = array(
                'condition'         => $condition,
                'primary_key'       => $k['primary'],
                'attribute_columns' => $k['columns'],
            );
        }
        $request = array(
            'tables' => array(
                array(
                    'table_name' => $table,
                    'put_rows'   => $put_rows,
                ),
            ),
        );
        $response = $this->_otsFunction('batchWriteRow', array($request));
        return $response['tables'][0]['put_rows'];
    }

    /**
     * 读取一行数据
     *
     * @param       $table
     * @param array $primary |主键
     * array(
     *      'PK0' => 123,
     *      'PK1' => 'abc',
     * )
     * @param array $columns |指定要读取的列,可选
     * array(
     *      'attr0', 'attr3', 'attr5'    // 只读取 attr0, attr3, attr5 这几列
     *)
     * @return mixed
     * {
     *     "attr0": 456,
     *     "attr3": true,
     *     "attr5": {                  // 请注意BINARY类型的表示方法
     *         "type": "BINARY",
     *         "value": "a binary string"
     *     }
     * }
     * @throws \S\Exception
     */
    public function getRow($table, array $primary, array $columns = array()) {
        $request = array(
            'table_name'     => $table,
            'primary_key'    => $primary,
            'columns_to_get' => $columns,
        );
        $response = $this->_otsFunction('getRow', array($request));
        return $response['row']['attribute_columns'];
    }


    /**
     * 读取指定的多行数据。 请注意，BatchGetRow在部分行读取失败时，会在返回的$response中表示，而不是抛出异常。
     * 请参见样例：处理BatchGetRow的返回。
     * 一次操作请求读取的行数:不超过100行
     */
    public function batchGetRow($table, array $primarys, $columns = array()) {
        $rows = array();
        foreach ($primarys as $parimary) {
            $rows[] = array('primary_key' => $parimary);
        }
        $request = array(
            'tables' => array(
                array(
                    'table_name'     => $table,
                    'columns_to_get' => $columns,
                    'rows'           => $rows,
                ),
            ),
        );
        $response = $this->_otsFunction('batchGetRow', array($request));
        $batch_result = array();
        if (count($response['tables'][0]['rows']) > 0) {
            foreach ($response['tables'][0]['rows'] as $list) {
                if ($list['is_ok'] && $list['row']['primary_key_columns']) {
                    $batch_result[] = array(
                        'primary' => $list['row']['primary_key_columns'],
                        'columns' => $list['row']['attribute_columns'],
                    );
                }
            }
        }
        return $batch_result;
    }


    /**
     * 读取一个范围的数据,可能被截断
     * @param $table
     * @param $startPK
     * @param $endPK
     * //请注意，这个例子运行时PHP占用内存较大，在我们的测试环境中，需要将php.ini中的
     * //memory_limit 设置为 256M
     *   $startPK = array(
     *       'PK0' => array('type' => 'INF_MIN'),   // array('type' => 'INF_MIN') 用来表示最小值
     *       'PK1' => array('type' => 'INF_MIN'),
     *   );
     *   $endPK = array(
     *       'PK0' => array('type' => 'INF_MAX'),   // array('type' => 'INF_MAX') 用来表示最小值
     *       'PK1' => array('type' => 'INF_MAX'),
     *   );
     *
     * //你同样可以用具体的值来表示 开始主键和结束主键，例如：
     *   $startPK = array('PK0' => 0, 'PK1' => 'aaaa');
     *   $endPK = array('PK0' => 9999, 'PK1' => 'zzzz');
     *
     * @param string $direction FORWARD(正序)/BACKWARD(倒序)
     * @param array $columns |指定要读取的列,可选
     * array(
     *      'attr0', 'attr3', 'attr5'    // 只读取 attr0, attr3, attr5 这几列
     * @param int $limit 指定最多读取多少行
     * @return array
     * @throws \S\Exception
     */
    public function getRange($table, $startPK, $endPK, array $columns = array(), $limit = 100, $direction = 'FORWARD') {
        if ($direction != 'FORWARD') {
            $direction = 'BACKWARD';
        }

        $request = array(
            'table_name'                  => $table,
            'direction'                   => $direction,       // 方向可以为 FORWARD 或者 BACKWARD
            'inclusive_start_primary_key' => $startPK,         // 开始主键
            'exclusive_end_primary_key'   => $endPK,           // 结束主键
            'columns_to_get'              => $columns,
            'limit'                       => $limit,
        );
        $response = $this->_otsFunction('getRange', array($request));

        $ret = array();
        foreach ($response['row'] as $row_info){
            $ret[] = array(
                'primary' => $row_info['primary_key_columns'],
                'columns' => $row_info['attribute_columns'],
            );
        }
        return $response;
    }


    /**
     * 更新一行数据
     * @param $table
     * @param array $primary |主键
     * array(
     *      'PK0' => 123,
     *      'PK1' => 'abc',
     * )
     * @param array $columns |属性
     * $put为true时要求以下格式
     * array(
     *      'attr0' => 789,             // 如果该列已经存在，则更新该列的值
     *      'new_column' => 'abc',      // 如果该列不存在，则追加新列
     * )
     * 或者
     * $put为false时要求以下格式
     * array(
     *      'attr1',                    // 指定删除 attr1 attr2 两列
     *      'attr2'
     * )
     * @param bool $put 是否为更新操作
     * @param string $condition |若期待该行存在但该行不存在，则本次更新操作会失败, 返回错误；若忽视该行是否存在，则无论该行是否存在，都不会因此导致本次操作失败
     *                          IGNORE 表示不做行存在性检查
     *                          EXPECT_EXIST 表示期望行存在
     * @return bool
     * @throws \S\Exception
     */
    public function updateRow($table, array $primary, array $columns, $put = true, $condition = 'EXPECT_EXIST') {
        if ($put) {
            $request = array(
                'table_name'               => $table,
                'condition'                => $condition,
                'primary_key'              => $primary,
                'attribute_columns_to_put' => $columns,
            );
        } else {
            $request = array(
                'table_name'                  => $table,
                'condition'                   => $condition,
                'primary_key'                 => $primary,
                'attribute_columns_to_delete' => $columns,
            );
        }

        $response = $this->_otsFunction('updateRow', array($request));
        return (bool)$response['consumed']['capacity_unit']['write'];
    }


    /**
     * 更新多行数据。 请注意，BatchWriteRow在部分行读取失败时，会在返回的$response中表示，而不是抛出异常。请参见样例：处理BatchWriteRow的返回。
     * 一次操作请求操作的行数:不超过200行
     * @param $table
     * @param $batch_datas
     * array(
     *      array(
     *          'primary' => array(
     *              'PK0' => 123,
     *              'PK1' => 'abc',
     *          ),
     *          'columns' => array(
     *              'attr0' => 789,
     *              'attr1' => 123,
     *          )
     *      ),
     *      array(
     *          'primary' => array(
     *              'PK0' => 234,
     *              'PK1' => 'bcd',
     *          ),
     *          'columns' => array(
     *              'attr0' => 678,
     *              'attr1' => 234,
     *          )
     *      ),
     * )
     * @param string $condition |若期待该行存在但该行不存在，则本次更新操作会失败, 返回错误；若忽视该行是否存在，则无论该行是否存在，都不会因此导致本次操作失败
     *                          IGNORE 表示不做行存在性检查
     *                          EXPECT_EXIST 表示期望行存在
     * @return mixed
     * [
     *     {
     *         "is_ok": true, //成功的的返回
     *         "consumed": {
     *             "capacity_unit": {
     *                 "read": 0,
     *                 "write": 1
     *             }
     *         }
     *     },
     *     {
     *         "is_ok": false, //失败的返回
     *         "error": {
     *             "code": "OTSInvalidPK",
     *             "message": "Primary key schema mismatch."
     *         }
     *     }
     * ]
     *
     * @throws \S\Exception
     */
    public function batchUpdateRow($table, array $batch_datas, $condition = 'EXPECT_EXIST') {
        $update_rows = array();
        foreach ($batch_datas as $k) {
            $update_rows[] = array(
                'condition'                => $condition,
                'primary_key'              => $k['primary'],
                'attribute_columns_to_put' => $k['columns'],
            );
        }
        $request = array(
            'tables' => array(
                array(
                    'table_name'  => $table,
                    'update_rows' => $update_rows,
                ),
            ),
        );
        $response = $this->_otsFunction('batchWriteRow', array($request));
        return $response['tables'][0]['update_rows'];
    }


    /**
     * 删除一行数据
     * @param $table
     * @param array $primary |主键
     * array(
     *      'PK0' => 123,
     *      'PK1' => 'abc',
     * )
     * @param string $condition |若期待该行存在但该行不存在，则本次删除操作会失败, 返回错误；若忽视该行是否存在，则无论该行实际是否存在，都不会因此导致操作失败。
     *                          IGNORE 表示不做行存在性检查
     *                          EXPECT_EXIST 表示期望行存在
     * @return bool
     * @throws \S\Exception
     */
    public function deleteRow($table, array $primary, $condition = 'IGNORE') {
        $request = array(
            'table_name'  => $table,
            'condition'   => $condition,
            'primary_key' => $primary,
        );
        $response = $this->_otsFunction('deleteRow', array($request));
        return (bool)$response['consumed']['capacity_unit']['write'];
    }

    /**
     * 删除多行数据。 请注意，BatchWriteRow在部分行读取失败时，会在返回的$response中表示，而不是抛出异常。请参见样例：处理BatchWriteRow的返回。
     * 一次操作请求操作的行数:不超过200行
     * @param $table
     * @param $batch_datas
     * array(
     *      array(
     *          'primary' => array(
     *              'PK0' => 123,
     *              'PK1' => 'abc',
     *          )
     *      ),
     *      array(
     *          'primary' => array(
     *              'PK0' => 234,
     *              'PK1' => 'bcd',
     *          )
     *      ),
     * )
     * @param string $condition |若期待该行存在但该行不存在，则本次删除操作会失败, 返回错误；若忽视该行是否存在，则无论该行实际是否存在，都不会因此导致操作失败。
     *                          IGNORE 表示不做行存在性检查
     *                          EXPECT_EXIST 表示期望行存在
     * @return mixed
     * [
     *     {
     *         "is_ok": true, //成功的的返回
     *         "consumed": {
     *             "capacity_unit": {
     *                 "read": 0,
     *                 "write": 1
     *             }
     *         }
     *     },
     *     {
     *         "is_ok": false, //失败的返回
     *         "error": {
     *             "code": "OTSInvalidPK",
     *             "message": "Primary key schema mismatch."
     *         }
     *     }
     * ]
     *
     * @throws \S\Exception
     */
    public function batchDeleteRow($table, $batch_datas, $condition = 'IGNORE') {
        $delete_rows = array();
        foreach ($batch_datas as $k) {
            $delete_rows[] = array(
                'condition'   => $condition,
                'primary_key' => $k['primary'],
            );
        }
        $request = array(
            'tables' => array(
                array(
                    'table_name'  => $table,
                    'delete_rows' => $delete_rows,
                ),
            ),
        );
        $response = $this->_otsFunction('batchWriteRow', array($request));
        return $response['tables'][0]['delete_rows'];
    }

    /**
     * 将调用均转向到封装的OTS实例
     *
     * @param       $name
     * @param array $args
     *
     * @return mixed
     * @throws \S\Exception
     */
    public function __call($name, $args = array()) {
        return $this->_otsFunction($name, $args);
    }

    /**
     * 捕获异常.抓取strace
     *
     * @param       $name
     * @param array $args
     *
     * @return mixed
     * @throws \S\Exception
     */
    private function _otsFunction($name, array $args = array()) {
        try {
            $ret = call_user_func_array(array(self::$ots_client[$this->name], $name), $args);
        } catch (\Exception $e) {
            //失败重试
            usleep(50000);
            $ret = call_user_func_array(array(self::$ots_client[$this->name], $name), $args);
        }
        return $ret;
    }
}
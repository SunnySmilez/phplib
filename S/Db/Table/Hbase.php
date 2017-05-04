<?php
namespace S\Db\Table;
/**
 * hbase客户端 基于hbase thrift api实现 推荐使用此连接方式
 * 使用前需要在hbase机器中安装thrift
 */
include_once(PHPLIB.'/Ext/Hbase/Hbase.php');
include_once(PHPLIB.'/Ext/Hbase/Types.php');

class Hbase implements TableInterface {

    const DEFAULT_TIMEOUT = 2; //请求超时时间 秒

    protected static $hbase_client;
    protected $name;
    protected $transport;
    protected $need_config = array("host" => "", "port" => "");

    public function __construct($name, array $config) {
        $this->name = $name;

        $diff = array_diff_key($this->need_config, $config);
        if ($diff) {
            throw new \S\Exception("hbase配置文件数据项缺失");
        }

        if (!self::$hbase_client[$this->name]) {
            $socket = new \Thrift\Transport\TSocket($config['host'], $config['port']);
            $timeout = $config['timeout'] ?: self::DEFAULT_TIMEOUT;
            $socket->setSendTimeout($timeout * 1000);
            $socket->setRecvTimeout($timeout * 1000 * 2); //接受数据的超时时间为请求超时时间的两倍

            $this->transport = new \Thrift\Transport\TBufferedTransport($socket);
            $protocol = new \Thrift\Protocol\TBinaryProtocol($this->transport);
            $client = new \Hbase\HbaseClient($protocol);
            $this->transport->open();

            self::$hbase_client[$this->name] = $client;
        }
    }

    public function close() {
        $this->transport->close();
        self::$hbase_client[$this->name] = null;
    }

    public function putRow($table, array $primary_key, array $data) {

        ksort($primary_key);
        $row_key = implode("-", $primary_key); //如果主键有多个字段 则聚合为字符串作为row_key

        $mutations = array();
        foreach ($data as $key => $value) {
            $mutations[] = new \Hbase\Mutation(array('column' => $key, 'value' => $value));
        }
        /**
         * @var $client \Hbase\HbaseClient
         */
        $client = self::$hbase_client[$this->name];
        $client->mutateRow($table, $row_key, $mutations, array());
        return true;
    }

    public function getRow($table, array $primary_key, array $cols = array()) {

        ksort($primary_key);
        $row_key = implode("-", $primary_key);

        /**
         * @var $client \Hbase\HbaseClient
         */
        $client = self::$hbase_client[$this->name];
        if ($cols) {
            $data = $client->getRowWithColumns($table, $row_key, $cols, array());
        } else {
            $data = $client->getRow($table, $row_key, array());
        }

        if ($data) {
            $ret = array();
            /**
             * @var $data_object \Hbase\TRowResult
             */
            $data_object = current($data);
            foreach ($data_object->columns as $cell_key => $cell) {
                /**
                 * @var $cell \Hbase\TCell
                 */
                $ret[$cell_key] = $cell->value;
            }
            return $ret;
        } else {
            return array();
        }
    }

    public function deleteRow($table, array $primary_key) {

        ksort($primary_key);
        $row_key = implode("-", $primary_key);

        /**
         * @var $client \Hbase\HbaseClient
         */
        $client = self::$hbase_client[$this->name];
        $client->deleteAllRow($table, $row_key, array());
        return true;
    }

    public function batchPutRow($table, array $batch_data) {

        $format_batch_data = array();

        foreach ($batch_data as $row) {
            $primary_key = $row['primary'];
            ksort($primary_key);
            $row_key = implode("-", $primary_key);

            $mutations = array();
            foreach ($row['columns'] as $key => $value) {
                $mutations[] = new \Hbase\Mutation(array('column' => $key, 'value' => $value));
            }

            $format_batch_data[] = new \Hbase\BatchMutation(array('row' => $row_key, 'mutations' => $mutations));
        }

        /**
         * @var $client \Hbase\HbaseClient
         */
        $client = self::$hbase_client[$this->name];
        $client->mutateRows($table, $format_batch_data, array());
        return true;
    }

    public function batchGetRow($table, array $primary_keys, array $cols = array()) {

        $row_keys = array();
        $row_keys_map = array(); // 存储row_key与primary_key的对应关系
        foreach ($primary_keys as $primary_key) {
            ksort($primary_key);
            $row_key = implode("-", $primary_key);
            $row_keys[] = $row_key;
            $row_keys_map[$row_key] = $primary_key;
        }

        /**
         * @var $client \Hbase\HbaseClient
         */
        $client = self::$hbase_client[$this->name];
        if ($cols) {
            $data = $client->getRowsWithColumns($table, $row_keys, $cols, array());
        } else {
            $data = $client->getRows($table, $row_keys, array());
        }

        if ($data) {
            $ret = array();
            /**
             * @var $object \Hbase\TRowResult
             */
            foreach ($data as $object) {
                $row_data = array();
                $row_key = $row_keys_map[$object->row];
                foreach ($object->columns as $cell_key => $cell) {
                    /**
                     * @var $cell \Hbase\TCell
                     */
                    $row_data[$cell_key] = $cell->value;
                }

                $ret[] = array(
                    'primary' => $row_key,
                    'columns' => $row_data,
                );
            }

            return $ret;
        } else {
            return array();
        }
    }

    public function getRange($table, array $start_primary_key, array $end_primary_key, array $cols = array(), $limit = 100){
        ksort($start_primary_key);
        ksort($end_primary_key);
        $start_row_key = implode("-", $start_primary_key);
        $end_row_key = implode("-", $end_primary_key);

        /**
         * @var $client \Hbase\HbaseClient
         */
        $client = self::$hbase_client[$this->name];
        $scan = $client->scannerOpenWithStop($table, $start_row_key, $end_row_key, $cols, array());
        $data = $client->scannerGetList($scan, $limit);
        $client->scannerClose($scan);

        if ($data) {
            $ret = array();
            /**
             * @var $object \Hbase\TRowResult
             */
            foreach ($data as $object) {
                $row_data = array();
                $row_key = explode("-", $object->row);
                foreach ($object->columns as $cell_key => $cell) {
                    /**
                     * @var $cell \Hbase\TCell
                     */
                    $row_data[$cell_key] = $cell->value;
                }

                $ret[] = array(
                    'primary' => $row_key,
                    'columns' => $row_data,
                );
            }

            return $ret;
        } else {
            return array();
        }
    }
}
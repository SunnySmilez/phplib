<?php
namespace S\Db\Table;
/**
 * hbase客户端 基于hbase rest api实现
 * ! 不支持batchGetRow getRange方法
 */
class Hbaserest implements TableInterface {

    const DEFAULT_TIMEOUT = 5; //http请求超时时间 秒

    protected static $hbase_client;
    protected $name;
    protected $timeout;
    protected $need_config = array("host" => "", "port" => "");

    public function __construct($name, array $config) {
        $this->name = $name;

        $diff = array_diff_key($this->need_config, $config);
        if ($diff) {
            throw new \S\Exception("hbase配置文件数据项缺失");
        }

        if (!self::$hbase_client[$this->name]) {
            $this->timeout = $config['timeout'] ?: self::DEFAULT_TIMEOUT;
            $http_con = new \S\Http($config['host'].":".$config['port']);
            self::$hbase_client[$this->name] = $http_con;
        }
    }

    public function close(){
        self::$hbase_client[$this->name] = null;
    }

    public function putRow($table, array $primary_key, array $data) {

        ksort($primary_key);
        $row_key = implode("-", $primary_key); //如果主键有多个字段 则聚合为字符串作为row_key

        $cell_data = array();
        foreach ($data as $cell_key => $cell_value) {
            $cell_data[] = array(
                'column' => base64_encode($cell_key),
                '$'      => base64_encode($cell_value),
            );
        }
        $post_data = array(
            'Row' => array(
                array(
                    'key'  => base64_encode($row_key),
                    'Cell' => $cell_data,
                ),
            ),
        );

        $path = "{$table}/{$row_key}";
        $ret = $this->request($path, "PUT", json_encode($post_data), array("Content-Type" => "application/json", "Accept" => "application/json"));
        return $ret;
    }

    public function getRow($table, array $primary_key, array $cols = array()) {

        ksort($primary_key);
        $row_key = implode("-", $primary_key);
        $cols_key = implode(",", $cols);

        $path = "{$table}/{$row_key}/{$cols_key}";
        $data = $this->request($path, "GET", "", array("Accept" => "application/json"));

        $ret = array();
        foreach ($data['Row'] as $row) {
            foreach ($row["Cell"] as $cell) {
                $key = base64_decode($cell["column"]);
                $value = base64_decode($cell["$"]);
                $ret[$key] = $value;
            }
        }
        return $ret;
    }

    public function deleteRow($table, array $primary_key) {

        ksort($primary_key);
        $row_key = implode("-", $primary_key);

        $path = "{$table}/{$row_key}";
        $ret = $this->request($path, "DELETE", "", array("Accept" => "application/json"));
        return $ret;
    }

    public function batchPutRow($table, array $batch_data) {

        $post_data = array();

        foreach ($batch_data as $row) {
            $primary_key = $row['primary'];
            ksort($primary_key);
            $row_key = implode("-", $primary_key);

            $cell_data = array();
            foreach ($row['columns'] as $cell_key => $cell_value) {
                $cell_data[] = array(
                    'column' => base64_encode($cell_key),
                    '$'      => base64_encode($cell_value),
                );
            }

            $post_data["Row"][] = array(
                'key'  => base64_encode($row_key),
                'Cell' => $cell_data,
            );
        }

        $path = "{$table}/row";
        $ret = $this->request($path, "PUT", json_encode($post_data), array("Content-Type" => "application/json", "Accept" => "application/json"));
        return $ret;
    }

    private function request($path, $method, $data, array $headers) {
        /**
         * @var \S\Http $http_con
         */
        $http_con = self::$hbase_client[$this->name];
        $options = array(
            'headers' => $headers,
            'timeout' => $this->timeout,
        );

        try {
            $ret = $http_con->request($method, $path, $data, $options);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            //失败重试
            usleep(50000);
            $ret = $http_con->request($method, $path, $data, $options);
        }
        if ($ret) {
            $ret = json_decode($ret, true);
        }

        return $ret ?: true;
    }
}
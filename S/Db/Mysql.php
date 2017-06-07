<?php
namespace S\Db;

use S\Strace\Timelog as Timelog;

/**
 *****************************************
 * 注意：无特殊原因禁止自己拼接sql执行！！！  *
 *****************************************
 *
 * mysql 实现类
 *
 * table_name xhprof
 * ---------------------------------------------
 * `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
 * `content` text,
 * `ip` int(11) unsigned DEFAULT NULL,
 * `Type` varchar(10) DEFAULT NULL,
 * `ctime` datetime DEFAULT NULL,
 * ---------------------------------------------
 * database config $_name = ssolog_monitor
 *  "master" => array(
 *      "username"  => "*",
 *      "password"  => "*",
 *      "host"      => "192.168.1.198",
 *      "port"      => "3306",
 *      "dbname"    => "wechat",
 *      "charset"   => "utf-8",
 *      "pconnect"  => false,
 *      "timeout"   => 3,
 * ),
 * $db = new SMysql($_name);
 *
 * query:
 * $rows = $db->query($this->_table_name,array("content"=>"123","ip"=>"1","Type"=>1));
 *
 * insert a row
 * $uid = $db->insert($this->_table_name,array("content"=>"123","ip"=>"1","Type"=>1,"ctime"=>date("Y-m-d H:i:s")));
 *
 * update a row
 * $db->update($this->_table_name,array("ctime"=>date("Y-m-d H:i:s")),array("content"=>"123","ip"=>"1","Type"=>1));
 *
 * delete row
 * $db->delete($this->_table_name,array("content"=>"123","ip"=>"1","Type"=>1));
 *
 * 分库分表查询--通过在实现的方法里实现回调，传入配置
 * $mysql = new SMysql($this->_config_name,array($this,"getconf"));
 * public function getconf(){
 *  $config = LibTools::getSsoRes('mysql', $this->_config_name);
 *  return $config;
 * }
 * 事务及其他用法见测试用例https://svn1.intra.sina.com.cn/sso/BSSO/testing/framework/util/mysqlTest.php
 */
class Mysql {

    const PARAMETER_IS_EMPTY = 30030001;    //传入的参数为空
    const MYSQL_CONFIG_NAME_EMPTY = 30030002;    //MYSQL配置的name为空
    const MYSQL_CONFIG_VALUE_CHECK = 30030003;    //mysql配置信息格式错误
    /**
     * 数据库资源共有三种 主从备
     */
    const READ = 'slave', WRITE = 'master', BACKUP = 'backup';
    /**
     * 操作超时默认时间，单位s
     */
    const TIMEOUT = 3;

    /**
     * 记录性能日志
     */
    public static $service_test = false; //sql性能测试开关
    public static $debug_log = array();

    /**
     * @var \PDO
     */
    private static $_db = array();
    /**
     * 配置项数组可用key值
     */
    private static $config_keys = array('host', 'port', 'dbname', 'username', 'password', '*encoding', '*pconnect', '*charset', '*timeout');

    /**
     * @var string
     */
    private $_name = null;
    /**
     * connection setting
     *
     * @var string
     */
    private $_dsn = null;
    /**
     * 强制读模式设置
     *
     * @var string
     */
    private $_read_mode = null;
    /**
     * 是否区分读写模式，用于主从同时使用
     *
     * @var bool
     */
    private $_mode_flag = false;
    /**
     * 是否在事务处理过程中，如果是会强制 $mode 使用WRITE
     *
     * @var bool
     */
    private $_in_transaction = false;
    /**
     * 用于判断是否使用持久连接，如果使用，析构函数中不做close操作
     *
     * @var bool
     */
    private $_persistent = false;

    /**
     * 构造函数
     *
     * @param $name
     */
    public function __construct($name) {
        $this->_name = $name;
    }

    /**
     * 析构函数
     *
     * 非长连接模式下关闭连接
     */
    public function __destruct() {
        if (!$this->_persistent) {
            $this->close();
        }
    }

    /**
     * 使用时请确认，暂时只操作读
     *
     * @param $func
     * @param $agrvs
     *
     * @return mixed
     */
    public function __call($func, array $agrvs = array()) {
        $mode = self::READ;
        if (isset($agrvs[0]) && in_array($agrvs[0], array(self::READ, self::WRITE))) {
            $mode = $agrvs[0];
            unset($agrvs[0]);
        }

        return call_user_func(array($this->getPdo($mode), $func), $agrvs);
    }

    /**
     * 查询全部结果
     *
     * select *|col1.col2... from $table where..order by...limit...
     * return all rows
     *
     * @param string $table  表名
     * @param array  $params where参数, kv格式
     * @param array  $cols   select列名集合
     * @param array  $orders order by参数, kv格式
     *                       示例:
     *                       array(
     *                          'col' => 'asc',
     *                       )
     * @param int    $limit 查询条数
     * @param int    $style 结果格式, 默认: 列名-值 格式
     *
     * @return array
     */
    public function query($table, array $params, array $cols = array(), array $orders = array(), $limit = 0, $style = \PDO::FETCH_ASSOC) {
        $sql  = $this->arrayToSql($table, $params, $cols, $orders, $limit);
        $stmt = $this->execute($sql['sql'], $sql['param']);

        return $stmt ? $stmt->fetchAll($style) : false;
    }

    public function qsql($sql, array $params = array(), $style = \PDO::FETCH_ASSOC) {
        $stmt = $this->execute($sql, $params);

        return $stmt ? $stmt->fetchAll($style) : false;
    }

    /**
     * select *|col1.col2... from $table where..order by...limit...
     * return all rows/a row
     *
     * @param string $table  table name
     * @param array  $params after where
     * @param array  $cols   table.col_name
     * @param int    $style
     * @param array  $orders
     * @param int    $limit
     *
     * @return array
     */
    public function queryone($table, array $params, array $cols = array(), array $orders = array(), $limit = 0, $style = \PDO::FETCH_ASSOC) {
        $sql  = $this->arrayToSql($table, $params, $cols, $orders, $limit);
        $stmt = $this->execute($sql['sql'], $sql['param']);

        return $stmt ? $stmt->fetch($style) : false;
    }

    public function qsqlone($sql, array $params = array(), $style = \PDO::FETCH_ASSOC) {
        $stmt = $this->execute($sql, $params);

        return $stmt ? $stmt->fetch($style) : false;
    }

    /**
     * 带总记录数的查询
     * SELECT SQL_CALC_FOUND_ROWS * FROM tbl_name;SELECT FOUND_ROWS();
     *
     * @param       $table
     * @param array $params
     * @param array $cols
     * @param int   $style
     * @param array $orders
     * @param int   $limit
     *
     * @return array|bool
     */
    public function querycount($table, array $params, array $cols = array(), array $orders = array(), $limit = 0, $style = \PDO::FETCH_ASSOC) {
        $sql = $this->arrayToSql($table, $params, $cols, $orders, $limit);
        $sql = preg_replace('/^\s*SELECT\s+/i', 'SELECT SQL_CALC_FOUND_ROWS ', $sql);

        $stmt = $this->execute($sql['sql'], $sql['param']);
        if (!$stmt) return false;
        //总数
        $rows['total'] = $this->getPdo()->query('SELECT FOUND_ROWS()')->fetchColumn();
        $rows['data']  = $stmt->fetchAll($style);

        return $rows;
    }

    public function count($table, array $params = array(), $col = "", $distinct = false) {
        $where_info = $this->_conditionToSqlInfo($params);
        $where      = $where_info['where'] ? "WHERE {$where_info['where']}" : "";
        if ($col) {
            $col = $distinct ? "DISTINCT " . $col : $col;
        } else {
            $col = "*";
        }

        $sql  = "SELECT count({$col}) FROM {$table} {$where}";
        $stmt = $this->execute($sql, $where_info['param']);

        return $stmt ? current($stmt->fetch(\PDO::FETCH_ASSOC)) : false;
    }

    public function querysum($table, array $params, $sum_col) {
        $where_info = $this->_conditionToSqlInfo($params);
        $sql        = "SELECT sum($sum_col) FROM {$table} WHERE {$where_info['where']}";
        $stmt       = $this->execute($sql, $where_info['param']);

        return $stmt ? $stmt->fetch(\PDO::FETCH_ASSOC) : false;
    }

    /**
     * 返回结果数组中的key为所指定的$key
     *
     * @param        $table
     * @param        $key
     * @param string $prefix
     * @param array  $params
     * @param array  $cols
     * @param array  $orders
     *
     * @return array|bool
     */
    public function querykey($table, $key, $prefix, array $params, array $cols = array(), array $orders = array()) {
        $sql = $this->arrayToSql($table, $params, $cols, $orders);

        $stmt = $this->execute($sql['sql'], $sql['param']);
        if (!$stmt) return false;
        $data = array();
        if (is_array($key)) {
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $nkey = $prefix;
                foreach ($key as $k) {
                    $nkey .= "{$row[$k]}";
                }
                $data[$nkey] = $row;
            }
        } else {
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $data[$prefix . $row[$key]] = $row;
            }
        }

        return $data;
    }

    /**
     * 返回 1 维的关联数组，key为$key所指定的列，value为$value所指定的列
     *
     * @param       $table
     * @param       $key
     * @param       $value
     * @param       $params
     * @param array $orders
     * @param int   $limit
     *
     * @return array|bool
     */
    public function querykv($table, $key, $value, $params = array(), array $orders = array(), $limit = 0) {
        $is_value_array = is_array($value);
        if ($key) {
            $cols = $is_value_array ? array_merge($value, array($key)) : array($key, $value);
        } else {
            $cols = $is_value_array ? $value : array($value);
        }
        $sql  = $this->arrayToSql($table, $params, $cols, $orders, $limit);
        $stmt = $this->execute($sql['sql'], $sql['param']);
        if (!$stmt) return false;

        $data = array();
        $i    = 0;
        if ($is_value_array) {
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $rowk = $row[$key];
                unset($row[$key]);
                $data[$rowk] = $row;
            }
        } else {
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                if ($key) {
                    if ($value) {
                        $data[$row[$key]] = $row[$value];
                    } else {
                        $data[$row[$key]] = $i++;
                    }
                } else {
                    $data[] = $row[$value];
                }
            }
        }

        return $data;
    }

    public function qsqlkv($sql, $key, $value, $params = array()) {
        $stmt = $this->execute($sql, array_values($params));
        if (!$stmt) return false;

        $data           = array();
        $i              = 0;
        $is_value_array = is_array($value);
        if ($is_value_array) {
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $rowk = $row[$key];
                unset($row[$key]);
                $data[$rowk] = $row;
            }
        } else {
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                if ($key) {
                    if ($value) {
                        $data[$row[$key]] = $row[$value];
                    } else {
                        $data[$row[$key]] = $i++;
                    }
                } else {
                    $data[] = $row[$value];
                }
            }
        }

        return $data;
    }

    /**
     * insert a array data to $table
     *
     * @param       $table
     * @param array $data
     * @param bool  $returnid
     * @param bool  $replaceinto
     *
     * @return bool|int
     * @throws \S\Exception
     */
    public function insert($table, array $data, $returnid = false, $replaceinto = false) {
        if (empty($data)) {
            throw new \S\Exception('data is empty:' . __LINE__, self::PARAMETER_IS_EMPTY);
        }

        $names       = array_keys($data);
        $values      = array_values($data);
        $fields      = implode('`,`', $names);
        $placeholder = substr(str_repeat('?, ', count($values)), 0, -2);
        $sql         = ($replaceinto ? 'REPLACE' : 'INSERT') . sprintf(' INTO `%s` (`%s`) VALUES (%s)', $table, $fields, $placeholder);

        $statement = $this->execute($sql, $values);
        if ($returnid) {
            return $statement === false ? false : (int)($this->getPdo(self::WRITE)->lastInsertId());
        } else {
            return $statement ? true : false;
        }
    }


    public function lastInsertId() {
        return $this->getPdo(self::WRITE)->lastInsertId();
    }

    /**
     * 多条数据同时插入
     *
     * @param       $table
     * @param array $fields
     * @param array $data
     * @param bool  $replaceinto
     *
     * @return bool
     * @throws \S\Exception
     */
    public function multiInsert($table, array $fields, array $data, $replaceinto = false) {
        if (empty($fields) || empty($data)) {
            throw new \S\Exception('fields or data is empty:' . __LINE__, self::PARAMETER_IS_EMPTY);
        }

        // 用空字符填充数据中缺失字段 或 截断多出的元素
        $values     = array();
        $field_size = count($fields);
        foreach ($data as $v) {
            $vsize = count($v);
            if ($vsize < $field_size) {
                $v = array_fill($vsize, $field_size - $vsize, '');
            } elseif ($vsize > $field_size) {
                $v = array_slice($v, 0, $field_size);
            }
            $values = array_merge($values, array_values($v));
        }

        $fields      = implode('`,`', $fields);
        $placeholder = substr(str_repeat('?, ', $field_size), 0, -2);
        $value_sql   = substr(str_repeat("({$placeholder}),", count($data)), 0, -1);
        $sql         = ($replaceinto ? 'REPLACE' : 'INSERT') . sprintf(' INTO `%s` (`%s`) VALUES %s', $table, $fields, $value_sql);

        $statement = $this->execute($sql, $values);

        return $statement ? true : false;
    }

    /**
     * update a array data to $table
     *
     * @param       $table
     * @param array $data
     * @param array $condition
     * @param int   $limit
     * @param bool  $affectRow
     *
     * @return bool
     * @throws \S\Exception
     */
    public function update($table, array $data, array $condition, $limit = 0, $affectRow = false) {
        if (empty($data) || empty($condition)) {
            throw new \S\Exception('data is not an array or empty:' . __LINE__, self::PARAMETER_IS_EMPTY);
        }

        $set_sql   = $this->_updateToSqlinfo($data);
        $where_sql = $this->_conditionToSqlInfo($condition);

        $sql = "UPDATE `{$table}` SET {$set_sql['field']} WHERE {$where_sql['where']}";
        if (intval($limit) >= 1) {
            $sql .= ' LIMIT ' . $limit;
        }

        $stmt = $this->execute($sql, array_merge($set_sql['param'], $where_sql['param']), $affectRow);

        return $stmt ? true : false;
    }

    /**
     * delete a array data to $table
     * 不允许无条件的删除
     *
     * @param       $table
     * @param array $condition
     * @param int   $limit
     * @param bool  $count 是否返回受影响的行数
     *
     * @return bool|int
     * @throws \S\Exception
     */
    public function delete($table, array $condition, $limit = 0, $count = false) {
        if (empty($condition)) {
            throw new \S\Exception('condition is empty:' . __LINE__, self::PARAMETER_IS_EMPTY);
        }

        $condition_sql = $this->_conditionToSqlInfo($condition);

        $sql = "DELETE FROM `{$table}` WHERE " . $condition_sql['where'];
        if (intval($limit) >= 1) {
            $sql .= "LIMIT " . $limit;
        }
        $stmt = $this->execute($sql, $condition_sql['param']);

        return $stmt ? ($count ? $stmt->rowCount() : true) : false;
    }

    /**
     * sql通用执行方法
     *
     * @param       $sql
     * @param array $params
     * @param bool  $rowcount 是否返回受影响的行数
     *
     * @return bool|\PDOStatement
     * @throws \S\Exception
     */
    public function execute($sql, array $params = array(), $rowcount = false) {
        if (\Core\Env::getEnvName() !== APP_ENVIRON_PRODUCT) {
            $this->sqlDebug($sql, $params);
        }

        for ($i = 0; $i < 2; $i++) {
            $mode = $this->getDbMode($sql);
            /* @var $stmt \PDOStatement */
            try {
                $stmt = $this->getPdo($mode)->prepare($sql);
                Timelog::instance()->resetTime();
                if ($params) {
                    $result = $stmt->execute($params);
                } else {
                    $result = $stmt->execute();
                }
            } catch (\PDOException $e) {
                //如果在[CLI]模式下链接断开, 清除会话并重新连接
                if (\Core\Env::isCli() && stripos('MySQL server has gone away', $e->getMessage()) !== false) {
                    $this->close();

                    if ($i < 1) {
                        $message['exception'] = $e;
                        \S\Log\Logger::getInstance()->warning($message);

                        continue;
                    }
                }
                throw new \S\Exception($e->getMessage(), '3001' . $e->getCode());
            }

            $this->_setStrace($mode, __FUNCTION__, $sql);

            return $result ? ($rowcount ? $stmt->rowCount() : $stmt) : false;
        }

        return false;
    }

    /**
     * 事务开启
     *
     * 使用主库
     */
    public function transaction() {
        $this->_in_transaction = true;
        $this->getPdo(self::WRITE)->beginTransaction();
    }

    /**
     * 事务提交
     *
     * 使用主库
     */
    public function commit() {
        $this->_in_transaction = false;
        $this->getPdo(self::WRITE)->commit();
    }

    /**
     * 事务回滚
     *
     * 使用主库
     */
    public function rollback() {
        $this->_in_transaction = false;
        $this->getPdo(self::WRITE)->rollBack();
    }

    /**
     * 强制设置读从备类型
     *
     * @param $mode
     */
    public function setDbReadMode($mode) {
        if (in_array($mode, array(self::READ, self::BACKUP))) {
            $this->_read_mode = $mode;
        }
    }

    /**
     * 设置读写是否区分模式，用于主从处理
     *
     * @param bool $flag
     */
    public function setModeFlag($flag) {
        $this->_mode_flag = (bool)$flag;
    }

    /**
     * 关闭数据库连接
     *
     * @return bool
     */
    public function close() {
        $read_id = $this->getId(self::READ);
        if (isset(self::$_db[$read_id])) {
            self::$_db[$read_id] = null;
        }
        $write_id = $this->getId(self::WRITE);
        if (isset(self::$_db[$write_id])) {
            self::$_db[$write_id] = null;
        }

        return true;
    }

    /**
     * 执行sql的debug方法
     * 1.测试及开发环境下 记录执行的sql语句
     * 2.仿真环境下 记录sql的type为range, index, ALL的语句
     *
     * @param       $sql
     * @param array $params
     *
     * @return bool
     * @throws \S\Exception
     */
    protected function sqlDebug($sql, array $params) {
        $debug_sql = $sql;
        foreach ($params as $param) {
            $debug_sql = substr_replace($debug_sql, $param, strpos($debug_sql, "?"), 1);
        }

        if (!\Core\Env::isProductEnv() && \Core\Env::getModuleName() != "Admin") {
            if(!is_dir('/data1/logs/' . APP_NAME)){
                mkdir('/data1/logs/' . APP_NAME, 0777, true);
            }
            file_put_contents('/data1/logs/' . APP_NAME . '/sql.log', date('Y-m-d H:i:s') . ' | ' . \S\Request::server('PATH_INFO') .
                ' | ' . \S\Request::server('x-rid') . ' | ' . str_replace(array("\r", "\n"), ' ', $debug_sql) . "\n", FILE_APPEND);
        }

        if (self::$service_test) {
            try {
                $explain_sql = 'EXPLAIN ' . $sql;
                $mode        = $this->getDbMode($explain_sql);
                $stmt        = $this->getPdo($mode)->prepare($explain_sql);
                if ($params) {
                    $stmt->execute($params);
                } else {
                    $stmt->execute();
                }
            } catch (\PDOException $e) {
                throw new \S\Exception($e->getMessage(), '3003' . $e->getCode());
            }
            $explain_result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            self::$debug_log[] = array(
                'sql'     => $debug_sql,
                'explain' => json_encode($explain_result),
            );
        }

        return true;
    }

    /**
     * 根据给定条件生成sql和sql中用于prepare的值
     *
     * @param       $table
     * @param array $params
     * @param array $cols
     * @param array $orders
     * @param int   $limit
     *
     * @return array('sql'=>'', 'param'=>array())
     * @throws \S\Exception
     */
    protected function arrayToSql($table, array $params, array $cols, array $orders = array(), $limit = 0) {
        $strcols = $this->arrayToFieldString($cols);
        if (!empty($params)) {
            $where_info = $this->_conditionToSqlInfo($params);
            $sql        = "SELECT {$strcols} FROM {$table} WHERE {$where_info['where']}";
        } else {
            $sql = "SELECT {$strcols} FROM {$table}";
        }

        $sql .= $this->arrayToOrderString($orders);

        if (is_array($limit)) {
            $sql .= ' LIMIT ' . intval(array_shift($limit)) . ", " . intval(array_shift($limit));
        } elseif (intval($limit) > 0) {
            $sql .= ' LIMIT ' . intval($limit);
        }

        return array('sql' => $sql, 'param' => isset($where_info['param']) ? $where_info['param'] : array());
    }

    /**
     * 将包含where部分的数组转换成字符串
     *
     * @param array $params
     *
     * @return string
     */
    protected function arrayToFieldString(array $params) {
        return !empty($params) ? '`' . join('`,`', $params) . '`' : ' * ';
    }

    protected function arrayToOrderString(array $orders) {
        $sql = '';
        if (!empty($orders)) {
            $sql         = ' ORDER BY ';
            $orderstring = array();
            foreach ($orders as $col => $order) {
                $orderstring[] = "`{$col}` {$order}";
            }
            $sql .= join(',', $orderstring);
        }

        return $sql;
    }

    /**
     * 获取读写类型否则通过sql分析读写操作
     *
     * @param $sql
     *
     * @return string
     */
    protected function getDbMode($sql) {
        //不区分读写模式时，直接用写模式，连接master
        if (!$this->_mode_flag) {
            return self::WRITE;
        }
        //如果已有开启的写会话,那复用写会话,连接master
        if (self::$_db[$this->getId(self::WRITE)]) {
            return self::WRITE;
        }
        //事务直接连接master
        if ($this->_in_transaction) {
            return self::WRITE;
        }

        $mode = stripos(trim($sql), 'select') !== false ? self::READ : self::WRITE;
        //接受外部传入的强制从或者备
        if (($mode == self::READ) && ($mode != $this->_read_mode) && isset($this->_read_mode)) {
            $mode = $this->_read_mode;
        }

        return $mode;
    }

    /**
     * @param string $mode
     *
     * @return \PDO
     */
    protected function getPdo($mode = self::READ) {
        $key = $this->getId($mode);
        if (!isset(self::$_db[$key])) {
            Timelog::instance()->resetTime();
            self::$_db[$key] = $this->_connect($this->config($mode));
            $this->_setStrace($mode, 'connect');
        }

        return self::$_db[$key];
    }

    protected function getId($mode) {
        return $mode . $this->_name;
    }

    /**
     * 可继承重写数据库配置
     *
     * @param string $mode
     *
     * @return array
     * @throws \S\Exception
     */
    protected function config($mode = self::READ) {
        $name   = $this->_name;
        $config = \S\Config::confServer('mysql.' . $name);
        if (!$config) {
            throw new \S\Exception('mysql config is not set', self::MYSQL_CONFIG_NAME_EMPTY);
        }

        $config = isset($config[$mode]) ? $config[$mode] : $config[self::WRITE];
        //如果有多个从库随机选择一个
        if ($mode == self::READ) {
            if (!$config['host'] && $config[0]['host']) {
                $config = $config[rand(0, count($config) - 1)];
            }
        }
        //检查格式
        self::_checkConfigFormat($config);
        $this->_dsn = sprintf('mysql:host=%s;port=%s;dbname=%s', $config['host'], $config['port'], $config['dbname']);

        if (isset($config['charset'])) {
            $this->_dsn .= ';charset=' . $config['charset'];
        }

        return $config;
    }

    /**
     * 处理删除条件数据，转化成prepare的sql参数和execute的参数数组
     *
     * @param $condition
     *
     * @return array
     */
    private function _conditionToSqlInfo(array $condition) {
        $where = '';
        $param = array();
        foreach ($condition as $field => $value) {
            if (is_array($value)) {
                $tmp = rtrim(str_repeat('?,', count($value)), ',');
                $where .= "`{$field}` IN ({$tmp}) AND ";
                $param = array_merge($param, array_values($value));
            } else {
                $where .= "`{$field}`=? AND ";
                $param[] = $value;
            }

        }

        return array('where' => substr($where, 0, -5), 'param' => $param);
    }

    /**
     * 处理删除条件数据，转化成prepare的sql参数和execute的参数数组
     *
     * @param array $data
     *
     * @return array
     */
    private function _updateToSqlinfo(array $data) {
        $values = array_values($data);
        $fields = implode('`=?,`', array_keys($data));

        return array('field' => "`{$fields}`=?", 'param' => $values);
    }

    /**
     * 检查配置文件格式是否合格
     *
     * @param array $config
     *
     * @throws \S\Exception
     */
    private function _checkConfigFormat($config) {
        $valid_keys = array_fill_keys(self::$config_keys, 0);
        foreach ($config as $k => $v) {
            //检查是否是必选或者可选参数。可选参数以*号开头
            if (!isset($valid_keys[$k]) && !isset($valid_keys["*$k"])) {
                throw new \S\Exception('Unused PdoMysql "' . $this->_name . '" config "' . $k . '"', self::MYSQL_CONFIG_VALUE_CHECK);
            }
            unset($valid_keys[$k]);
        }

        if ($valid_keys) {
            $keys = array_keys($valid_keys);
            //忽略掉可选参数。可选参数以*号开头
            do {
                $key = array_pop($keys);
            } while ($key{0} === '*');

            if ($key && $key{0} !== '*') {
                throw new \S\Exception('Missing PdoMysql "' . $this->_name . '" config value "' . $key . '"', self::MYSQL_CONFIG_VALUE_CHECK);
            }
        }

        if (isset($config['*pconnect']) && $config['*pconnect']) {
            $this->_persistent = true;
        }
    }

    /**
     * 数据库连接
     *
     * @param array $config 数据库配置文件
     *                      array(
     *                      'master'    => array(
     *                      'host'        => 'localhost',
     *                      'port'        => '3306',
     *                      'dbname'    => 'test',
     *                      'username'    => 'root',
     *                      'password'    => 'root',
     *                      'encoding'    => 'utf8',
     *                      'charset'    => 'utf8',
     *                      'pconnect'  => false,
     *                      )
     *                      )
     *
     * @return \PDO
     */
    private function _connect($config) {
        $option = array();
        if (isset($config['pconnect']) && $config['pconnect'] === true) {
            $option[\PDO::ATTR_PERSISTENT] = true;
        }
        // MYSQL查询缓存
        //		$option[PDO::MYSQL_ATTR_USE_BUFFERED_QUERY]	= true;

        // 错误处理方式，使用异常
        $option[\PDO::ATTR_ERRMODE] = \PDO::ERRMODE_EXCEPTION;

        // 默认连接后执行的sql
        if (!empty($config['encoding'])) {
            $option[\PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES '{$config['encoding']}'";
        }

        $option[\PDO::ATTR_TIMEOUT] = self::TIMEOUT;
        if (isset($config['timeout'])) {
            $option[\PDO::ATTR_TIMEOUT] = $config['timeout'];
        }

        return new \PDO($this->_dsn, $config['username'], $config['password'], $option);
    }

    /**
     * 关闭所有连接，请慎用，暂设置为private
     *
     * @return bool
     */
    private function _closeAll() {
        self::$_db = null;

        return true;
    }

    /**
     * 添加mysql执行时间记录
     *
     * @param string $mode     master|slave|backup
     * @param string $function 操作方法
     * @param string $sql      sql语句
     */
    private function _setStrace($mode, $function, $sql = '') {
        $config = $this->config($mode);
        Timelog::instance()->log('mysql', array(
            'class'     => __CLASS__,
            'method'    => $function,
            'idc'       => isset($config['idc']) ? $config['idc'] : \Core\Env::getIdc(),
            'resource'  => "{$config['host']}:{$config['port']}:{$config['dbname']}",
            'extension' => $sql,
        ));
    }

}

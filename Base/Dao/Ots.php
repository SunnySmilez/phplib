<?php
namespace Base\Dao;

use Base\Exception\Dao as Exception;
use S\Crypt\Aes;
use \S\Util\Genuid;

class Ots {
    protected static $db = array();
    protected $table;
    protected $primary_key = array();
    protected $columns = array();
    protected $encrypt_keys = array();

    public function __construct() {
        if (!$this->table) {
            throw new Exception("ots no table");
        }
        if (!is_array($this->primary_key) || empty($this->primary_key)) {
            throw new Exception("ots no primary_key");
        }
        if (!is_array($this->columns) || empty($this->columns)) {
            throw new Exception("ots no columns");
        }
    }

    public static function db($dbName) {
        if (!isset(self::$db[$dbName])) {
            self::$db[$dbName] = new \S\Db\Ots($dbName);
        }
        return self::$db[$dbName];
    }

    /**
     * 数据加密
     * @param $data
     * @return array|string
     * @throws \S\Exception
     */
    protected function encrypt($data) {
        $keys = $this->encrypt_keys;

        if (!is_array($data)) {
            return base64_encode(Aes::encrypt($data, 'ots'));
        }
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $key => $value) {
                    if (in_array(strtolower($key), $keys) && $value) {
                        $data[$k][$key] = base64_encode(Aes::encrypt($value, 'ots'));
                    }
                }
            } else {
                if (in_array(strtolower($k), $keys) && $v) {
                    $data[$k] = base64_encode(Aes::encrypt($v, 'ots'));
                }
            }
        }
        return $data;
    }

    /**
     * 数据解密
     * @param $data
     * @return array|string
     * @throws \S\Exception
     */
    protected function decrypt($data) {
        $keys = $this->encrypt_keys;

        if (!is_array($data)) {
            if (8 > strlen($data)) {
                return $data;
            }
            return Aes::decrypt(base64_decode($data), 'ots');
        }

        foreach ($data as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $key => $value) {
                    if (in_array(strtolower($key), $keys) && $value) {
                        $data[$k][$key] = Aes::decrypt(base64_decode($value), 'ots');
                    }
                }
            } else {
                if (in_array(strtolower($k), $keys) && $v) {
                    $data[$k] = Aes::decrypt(base64_decode($v), 'ots');
                }
            }
        }
        return $data;
    }

    /**
     * 把顺序的主键变为散列的
     * @param $id
     * @return string
     */
    public function getHashPrimary($id) {
        $prefix = substr(md5($id), 0, 4);
        return $prefix . '_' . $id;
    }

    /**
     * 给数据增加随机6位后缀
     * @param $data
     * @return string
     */
    public function addRandomSuffix($data) {
        return $data . '_' . substr(Genuid::getUid(), -6);
    }

    /**
     * 删掉随机6位后缀
     * @param $data
     * @return string
     */
    public function delRandomSuffix($data) {
        return explode('_', $data)[0];
    }
}
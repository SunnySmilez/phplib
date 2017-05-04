<?php
namespace S\FileSystem\Handler;

use \S\Exception;

class Oss extends Abstraction {

    /**
     * @var \OSS\OssClient
     */
    private $_oss_client;

    /**
     * 初始化工作
     *
     * @return bool true-成功 false-失败
     */
    public function init() {
        $this->_oss_client = new \OSS\OssClient(
            $this->config['access_id'],
            $this->config['access_key'],
            $this->config['hostname']
        );
    }

    /**
     * 上传文件
     *
     * @param string $bucket           阿里云上的bucket名
     * @param string $remote_file_name 存储在文件系统里的文件名 如有目录则采用'test/1.png'的格式
     * @param string $local_file_name  上传的本地文件 使用绝对路径 ('/home/root/a.pdf')
     * @param null   $option           上传到阿里云的配置 无特殊需求使用null
     *
     * @return mixed                   文件的url链接地址
     * @throws Exception
     * @throws \Exception
     */
    public function put($bucket, $remote_file_name, $local_file_name, $option = null) {
        $this->checkSpaceName($bucket);
        if (!file_exists($local_file_name)) {
            throw new Exception('local file is not exist');
        }
        $this->_oss_client->uploadFile($bucket, $remote_file_name, $local_file_name, $option);

        //拼接出远程url
        return "http://" . $bucket . "." . $this->config['hostname'] . "/" . $remote_file_name;
    }

    /**
     * 获取文件
     *
     * @param string $bucket           阿里云上的bucket名
     * @param string $remote_file_name 存储在文件系统里的文件名 如有目录则采用'test/1.png'的格式
     * @param null   $option           阿里云的配置 无特殊需求使用null
     *
     * @return string 文件内容
     * @throws Exception
     */
    public function get($bucket, $remote_file_name, $option = null) {
        $this->checkSpaceName($bucket);
        $ret = $this->_oss_client->getObject($bucket, self::parseUrl($bucket, $remote_file_name), $option);

        return $ret;
    }

    /**
     * 删除文件
     *
     * @param string $bucket           阿里云上的bucket名
     * @param string $remote_file_name 存储在文件系统里的文件名 如有目录则采用'test/1.png'的格式
     * @param null   $option           阿里云的配置 无特殊需求使用null
     *
     * @return bool 成功/失败
     * @throws Exception
     */
    public function delete($bucket, $remote_file_name, $option = null) {
        $this->checkSpaceName($bucket);
        $this->_oss_client->deleteObject($bucket, self::parseUrl($bucket, $remote_file_name), $option);

        return true;
    }

    /**
     * 调用 阿里云sdk里的原生方法
     * $bucket参数的位置请传入$space
     *
     * @param $method
     * @param $arg
     *
     * @return mixed
     * @throws Exception
     */
    public function __call($method, $arg) {
        $ret = call_user_func_array(array($this->_oss_client, $method), $arg);

        return $ret;
    }

    /**
     * 拆解url获得$path
     * 兼容新老两版sdk
     */
    private static function parseUrl($bucket, $url) {
        $arr_file_name = parse_url($url);
        if (!$arr_file_name['host']) {
            return $url;
        }
        $host = $arr_file_name['host'];
        if (strpos($host, $bucket) !== false) {
            $path = trim($arr_file_name['path'], '/');
        } else {
            $path = trim(str_replace('/' . $bucket . '/', '', $arr_file_name['path']), '/');
        }

        return $path;
    }

}
<?php
namespace S\FileSystem\Handler;

require_once PHPLIB . "/Ext/Azure/vendor/autoload.php";

use \S\Exception;

class Azure extends Abstraction {

    /**
     * @var \MicrosoftAzure\Storage\Blob\Internal\IBlob;
     */
    private $_azure_client;

    /**
     * 初始化工作
     *
     * @return bool true-成功 false-失败
     */
    public function init() {
        $hostname       = $this->config['hostname'];
        $name           = $this->config['name'];
        $key            = $this->config['key'];
        $connection_str = "BlobEndpoint=http://{$name}.{$hostname}/;AccountName={$name};AccountKey={$key}";

        $this->_azure_client = \WindowsAzure\Common\ServicesBuilder::getInstance()->createBlobService($connection_str);
    }

    /**
     * 存储通用put方法
     *
     * @param string $space            文件空间
     * @param string $remote_file_name 存储在文件系统中的文件名
     * @param string $local_file_name  本地文件名
     *
     * @return mixed
     * @throws Exception
     */
    public function put($space, $remote_file_name, $local_file_name, $option = null) {
        if (!file_exists($local_file_name)) {
            throw new Exception('本地文件: ' . $local_file_name . '不存在');
        }

        $content = fopen($local_file_name, "r");
        try {
            $this->_azure_client->createBlockBlob($space, $remote_file_name, $content, $option);
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return 'http://' . $this->config['name'] . '.' . $this->config['hostname'] . '/' . $space . '/' . $remote_file_name;
    }

    /**
     * 存储通用get方法
     *
     * @param string $space 文件空间
     * @param string $remote_file_name 存储在文件系统中的文件名
     *
     * @return mixed 文件内容
     * @throws Exception
     */
    public function get($space, $remote_file_name, $option = null) {
        $remote_file_name = self::_getRemoteFileName($remote_file_name);
        try {
            /** @var \MicrosoftAzure\Storage\Blob\Models\GetBlobResult $blob */
            $blob = $this->_azure_client->getBlob($space, $remote_file_name, $option);

            return stream_get_contents($blob->getContentStream());
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * 存储通用delete方法
     *
     * 删除的文件不存在时，也会返回成功
     *
     * @param string $space 文件空间
     * @param string $remote_file_name 存储在文件系统中的文件名
     *
     * @return mixed 成功/失败
     * @throws Exception
     */
    public function delete($space, $remote_file_name, $option = null) {
        $remote_file_name = self::_getRemoteFileName($remote_file_name);
        try {
            $this->_azure_client->deleteBlob($space, $remote_file_name, $option);
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return true;
    }

    /**
     * 调用Azure sdk里的原生方法
     * $bucket参数的位置请传入$space
     *
     * @param $method
     * @param $arg
     *
     * @return mixed
     * @throws Exception
     */
    public function __call($method, $arg) {
        try {
            $ret = call_user_func_array(array($this->_azure_client, $method), $arg);
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return $ret;
    }

    /**
     * 获得文件path
     *
     * @param $url
     *
     * @return string
     */
    private static function _getRemoteFileName($url) {
        $arr_file_name = parse_url($url);
        $path = trim($arr_file_name['path'], '/');

        return $path;
    }

}
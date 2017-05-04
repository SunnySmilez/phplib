<?php
namespace S\FileSystem\Handler;

use \S\Exception;

class Local extends Abstraction {

    private $dir = null; //文件存储的目录

    /**
     * 初始化工作
     *
     * @return bool true-成功 false-失败
     */
    public function init() {
        if (is_dir($this->config['path'])) {
            $this->dir = $this->config['path'];
        } else {
            $ret = mkdir($this->config['path'], 0777, true);
            if (!$ret) {
                throw new Exception(get_class() . " can't create the folder " . $this->config['path']);
            }
        }
    }

    /**
     *  上传文件
     *
     * @param string $space            文件的存储目录
     * @param string $remote_file_name 存储在文件系统里的文件名 如有目录则采用'test/1.png'的格式
     * @param string $local_file_name  需要上传的文件的路径 绝对路径
     *
     * @return mixed  文件的绝对地址
     * @throws Exception
     */
    public function put($space, $remote_file_name, $local_file_name) {
        $this->checkSpaceName($space);
        $this->checkSpace($space);
        if (!is_file($local_file_name)) {
            throw new Exception("file $local_file_name is nou found");
        }

        $dir_name  = dirname($remote_file_name);
        $file_name = basename($remote_file_name);
        if ($dir_name == ".") {
            $dir_path = $this->dir . DIRECTORY_SEPARATOR . $space;
        } else {
            $dir_path = $this->dir . DIRECTORY_SEPARATOR . $space . DIRECTORY_SEPARATOR . $dir_name;
        }
        if (!is_dir($dir_path)) {
            mkdir($dir_path, 0777, true);
        }
        $store_path = $dir_path . DIRECTORY_SEPARATOR . $file_name;

        $ret = copy($local_file_name, $store_path);
        if ($ret) {
            return $store_path;
        } else {
            throw new Exception("put file '$local_file_name' is fail");
        }
    }

    /**
     * 获取文件
     *
     * @param string $space            文件存储的目录
     * @param string $remote_file_name 存储在文件系统里的文件名 如有目录则采用'test/1.png'的格式
     *
     * @return string 文件内容
     * @throws Exception
     */
    public function get($space, $remote_file_name) {
        $this->checkSpaceName($space);
        $this->checkSpace($space);
        if (strpos($remote_file_name, $this->dir) !== false) {
            $remote_file_name = str_replace($this->dir . '/' . $space . '/', '', $remote_file_name);
        }
        $store_path = $this->dir . '/' . $space . '/' . $remote_file_name;
        if (!is_readable($store_path)) {
            throw new Exception("file '$remote_file_name' is not found or can't be read");
        }

        $ret = file_get_contents($store_path);

        return $ret;
    }

    /**
     * 删除文件
     *
     * @param string $space            文件存储的目录
     * @param string $remote_file_name 存储在文件系统里的文件名 如有目录则采用'test/1.png'的格式
     *
     * @return bool
     * @throws Exception
     */
    public function delete($space, $remote_file_name) {
        $this->checkSpaceName($space);
        $this->checkSpace($space);
        if (strpos($remote_file_name, $this->dir) !== false) {
            $remote_file_name = str_replace($this->dir . '/' . $space . '/', '', $remote_file_name);
        }
        $store_path = $this->dir . '/' . $space . '/' . $remote_file_name;
        if (!is_file($store_path)) {
            return true;
        }

        $ret = unlink($store_path);
        if ($ret) {
            return true;
        } else {
            throw new Exception("delete file '$remote_file_name' fail");
        }
    }

    private function checkSpace($space) {
        $path = $this->dir . '/' . $space;
        if (is_dir($path)) {
            return true;
        } else {
            $ret = mkdir($path, 0777, true);
            if (!$ret) {
                throw new Exception(get_class() . " can't create the folder " . $path);
            }

            return true;
        }
    }

}
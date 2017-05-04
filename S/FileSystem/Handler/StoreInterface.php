<?php
namespace S\FileSystem\Handler;

interface StoreInterface {

    /**
     * 存储通用put方法
     *
     * @param string $space            文件空间
     * @param string $remote_file_name 存储在文件系统中的文件名
     * @param string $local_file_name  本地文件名
     *
     * @return mixed 文件的链接地址
     */
    public function put($space, $remote_file_name, $local_file_name);

    /**
     * 存储通用get方法
     *
     * @param string $space            文件空间
     * @param string $remote_file_name 存储在文件系统中的文件名
     *
     * @return mixed 文件内容
     */
    public function get($space, $remote_file_name);

    /**
     * 存储通用delete方法
     *
     * 删除的文件不存在时，也会返回成功
     *
     * @param string $space            文件空间
     * @param string $remote_file_name 存储在文件系统中的文件名
     *
     * @return mixed 成功/失败
     */
    public function delete($space, $remote_file_name);

}
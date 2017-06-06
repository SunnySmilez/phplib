<?php

namespace S\Daemon;
/**
 * Class Utils
 *
 * @package     S\Daemon
 * @description 进程管理工具类
 *
 * 提供基础服务，包括：
 *     获取所有相关文件的md5集合
 *     打印带有时间戳的信息到标准输出流
 */
class Utils {

    /**
     * 获取所有相关文件的md5
     *
     * @return array
     */
    public static function getIncludedFilesMd5() {
        $ret   = array();
        $files = get_included_files();
        foreach ($files as $file) {
            $ret[$file] = md5_file($file);
        }

        return $ret;
    }

    /**
     * 打印信息
     *
     * @param string $msg 待输出信息
     */
    public static function echoInfo($msg) {
        echo date("Y-m-d H:i:s") . " $msg \n";
    }

}
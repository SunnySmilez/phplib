<?php
namespace S\Log;

/**
 * 日志公共上下文
 * Class Context
 * @package S\Log
 */
class Context {
    /**
     * 设置通用日志记录信息,会记录在所有日志里
     *
     * @param array $log
     * array(
     * 'uid' => 111,
     * 'nickname' => '小二'
     * )
     * @return bool
     */
    public static function setCommonInfo(array $log){
        return \Yaf\Registry::set('log_common_info', array_merge(self::getCommonInfo(), $log));
    }

    /**
     * 获取存储的日志记录信息
     *
     * @return array
     */
    public static function getCommonInfo(){
        return \Yaf\Registry::get('log_common_info')?:array();
    }

    /**
     * 设置正常日志信息，只记录在info日志里
     *
     * @param array $log
     * @return bool
     */
    public static function setInfo(array $log){
        return \Yaf\Registry::set('log_info', array_merge(self::getInfo(), $log));
    }

    public static function getInfo(){
        return \Yaf\Registry::get('log_info')?:array();

    }
}
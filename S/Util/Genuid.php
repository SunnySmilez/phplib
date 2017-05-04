<?php
namespace S\Util;

use S\Exception;

/**
 * Class Genuid
 * @package Base\Util
 * @desc 生成全局唯一id
 * 一般对于唯一ID生成的要求主要这么几点：
 * 毫秒级的快速响应
 * 可用性强
 * prefix有连续性方便DB顺序存储
 * 体积小，8字节为佳
 */
class Genuid {

    const RETRY_TIMES = 3; //redis重试次数

    protected static $while = 0;
    /**
     * 生成20150304YM000001类似id
     * 每天从0开始，日期开头，有自己独特标示,加后六位连续id
     * 返回结果 20150304YM000001
     *
     * @param string $flag 订单号标识
     * @param int $size default 6 自定义自增序列长度
     *
     * @return bool|string
     * @throws Exception
     */
    public static function getOrderId($flag, $size = 6) {
        $date = date("Ymd");
        $key = "GENUID_ORDERID_" . $date . $flag;

        $redis = new \S\Db\Redis();
        $retry = 0;
        $uid = null;
        do {
            try {
                $uid = $redis->incr($key);
                if ($uid < 5) {
                    $redis->expire($key, 172800);
                }
                break;
            } catch (\Exception $e) {
                $retry++;
            }
        } while ($retry < self::RETRY_TIMES);
        if ($uid === null) {
            return false;
        }

        if ($uid > pow(10, $size)) {
            return false;
        }
        $uid = sprintf("%0${size}d", $uid);
        return $date . $flag . $uid;
    }

    /**
     * 返回周期内从1~n计数数值
     * 如：$range=y表示本年内从1开始计数，$range=d表示本月内从1开始计数
     * 如：$size=4表示周期内最大值为9999（超出最大值返回false），且均返回4位数值
     *
     * @param string $flag 应用唯一标识，避免应用间相互干扰
     * @param integer $size 将返回的数值长度
     * @param string $range 周期类型 y:年 m:月 d:日 如：y 表示本年
     * @return integer|boolean
     * @throws Exception
     */
    public static function getCycleId($flag, $size=4, $range='y') {
        switch(strtolower($range)) {
            case 'm':
                $timekey = date('Ym');
                break;
            case 'd':
                $timekey = date('Ymd');
                break;
            default:
                $timekey = date('Y');
        }

        $key = "GENUID_SERVICE_NUMBER_".$timekey.$flag;
        $redis = new \S\Db\Redis();
        $retry = 0;
        $uid = null;
        do{
            try {
                $uid = $redis->incr($key);
                break;
            }catch (\Exception $e){
                $retry++;
            }
        } while ($retry < self::RETRY_TIMES);
        if ($uid === null) {
            return false;
        }

        if($uid > pow(10, $size)){
            return false;
        }
        $uid = sprintf("%0{$size}d", $uid);
        return $uid;
    }

    /**
     * 通用唯一id $flag+长度12位字符串
     *
     * * * 表现出连续性
     * * 分布式分发不重合
     * 微秒级的快速响应
     *
     * 使用场景
     * 1.短链id
     * 2.订单id
     * 3.追踪id
     */
    public static function getUid($flag=""){
        $time = microtime(true)*10000;//14位
        $server_id = substr(crc32($_SERVER['SERVER_ADDR']),0,2);//2位
        self::$while ++;
        $while = str_pad(substr(self::$while,-2), 2, 0, STR_PAD_LEFT);//2位
        $rand = str_pad(mt_rand(0, 9999),4,0,STR_PAD_LEFT);//4位

        $num = $time.$server_id.$while.$rand;
        $to = 62;
        $dict = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $ret = '';
        do {
            $ret = $dict[bcmod($num, $to)] . $ret;
            $num = bcdiv($num, $to, 0);
        } while ($num > 0);
        return $flag.$ret;
    }
}

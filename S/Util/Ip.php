<?php
namespace S\Util;

class Ip {
    private static $_location_cache = null;
    private static $server_ip;
    private static $client_ip;

    public static function getServerIp(){
        if(self::$server_ip){
            return self::$server_ip;
        }

        if (isset($_SERVER['SERVER_ADDR'])) {
            self::$server_ip = $_SERVER['SERVER_ADDR'];
        } elseif (\Core\Env::isCli() && isset($_SERVER['HOSTNAME'])) {
            self::$server_ip = gethostbyname($_SERVER['HOSTNAME']);
        }
        return self::$server_ip ?: '';
    }

    public static function getClientIp() {
        if(self::$client_ip){
            return self::$client_ip;
        }

        $ip = $_SERVER['REMOTE_ADDR'];
        if (self::isPrivateIp($ip)) {
            $xforward = $_SERVER['HTTP_X_FORWARDED_FOR'];
            if ($xforward) {
                $arr = explode(',', $xforward);
                $i   = count($arr);

                while ($i-- > 0) {
                    $ip = trim($arr[$i]);
                    if ($i == 0 || !self::isPrivateIp($ip)) {
                        break;
                    }
                }
            } elseif ($_SERVER['X-Forwarded-For']) {
                $ip = $_SERVER['X-Forwarded-For'];
            }
        }
        //如果有端口号 则去除
        if($ip){
            $ip = explode(":", $ip)[0];
        }

        self::$client_ip = $ip;
        return self::$client_ip ?: '';
    }

    public static function isPrivateIp($ip){
        if ($ip === '127.0.0.1') {
            return true;
        }

        $long = sprintf('%u', ip2long($ip));
        return ($long & 0xFF000000) === 0x0A000000 //10.0.0.0-10.255.255.255
        || ($long & 0xFFF00000) === 0xAC100000 //172.16.0.0-172.31.255.255
        || ($long & 0xFFFF0000) === 0xC0A80000//192.168.0.0-192.168.255.255
        || ($long & 0xFFFF0000) === 0x64400000//100.64.0.0-100.64.255.255 兼容阿里云
        || ($long & 0xFFFF0000) === 0x64610000;//100.97.0.0-100.97.255.255 兼容阿里云
    }

    /**
     * 通过ip获取国家-省-市信息
     * @param string $ip
     * @param string $zone      获取位置，country、province、city，默认全部返回
     * @param string $encoding  返回信息编码，默认gbk
     * @param bool $intra       是否处理内网ip情况
     * @return mixed
     */
    public static function getLocation($ip, $zone='all', $encoding='gbk', $intra = true) {
        if ($intra && Ip::isPrivateIp($ip)) {
            $ip = '61.135.152.211';
        }

        if (self::$_location_cache === null) {
            self::$_location_cache = \S\Cache\Cache::pool('ipc', 'ip_location_cache');
        }

        if (!($info = self::$_location_cache->get($ip))) {
            //TODO:undefined function
            $info = lookup_ip_source($ip);
            if (!is_array($info) || !$info) {
                return false;
            }
            self::$_location_cache->set($ip, $info);
        }

        $ret = isset($info[$zone]) ? $info[$zone] : array(
            'country'   => $info['country'],
            'province'  => $info['province'],
            'city'      => $info['city'],
        );

        return strtolower($encoding) === 'gbk' ? $ret : \S\Util\Str::convert($ret, $encoding, 'gbk');
    }

    public static function isInIpList(array $ip_list, $ip) {
        foreach ($ip_list as $subnet) {
            if (self::isInSubnet($subnet, $ip)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 检查ip是否属于某个子网
     * @param	string $subnet  子网;如: 10.54.38/24 或 10.54.38.0/24 都是一样的
     * @param	string $ip
     * @return	bool
     */
    public static function isInSubnet($subnet, $ip) {
        if ($subnet === $ip) {
            return true;
        }

        $arr    = explode('/', trim($subnet));
        if (isset($arr[1])) {
            $long   = ip2long(trim($ip));
            $net    = ip2long($arr[0]);
            $hosts  = pow(2, 32-$arr[1]) - 1; //主机部分最大值
            $host   = $net ^ $long;		//客户端ip的主机部分
            return $host >= 0 && $host <= $hosts;
        }

        return false;
    }

    /**
     * 获取B/C段IP
     * @param $ip
     * @return string
     */
    public static function getBip($ip) {
        $arr = explode('.', $ip);
        return $arr[0].'.'.$arr[1];
    }
    public static function getCip($ip) {
        return substr($ip, 0, strrpos($ip, '.'));
    }
}
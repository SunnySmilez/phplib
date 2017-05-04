<?php

namespace Wechat\Message\Crypt;

use S\Exception;

/**
 * Class Cipher
 *
 * @package     Wechat\Message\Crypt
 * @description 公众平台消息体签名及加解密工具类
 */
class Cipher {

    const RANDOM_LENGTH = 16;

    /**
     * 对明文进行加密
     *
     * @param $appid
     * @param $encoding_aes_key
     * @param $msg
     *
     * @return string
     */
    public static function encrypt($appid, $encoding_aes_key, $msg) {
        /**
         * 16位随机数＋网络字节序＋原文＋appid
         */
        $msg = \S\Util\Rand::alnum(self::RANDOM_LENGTH) . pack("N", strlen($msg)) . $msg . $appid;

        return Aes::encrypt($encoding_aes_key, $msg);
    }

    /**
     * 对密文进行解密
     *
     * @param $appid
     * @param $encoding_aes_key
     * @param $msg
     *
     * @return string
     * @throws Exception
     */
    public static function decrypt($appid, $encoding_aes_key, $msg) {
        $result = Aes::decrypt($encoding_aes_key, $msg);
        if (strlen($result) < 16) {
            return "";
        }

        //去除16位随机字符串,网络字节序和AppId
        $content     = substr($result, self::RANDOM_LENGTH, strlen($result));
        $len_list    = unpack("N", substr($content, 0, 4));
        $xml_len     = $len_list[1];
        $xml_content = substr($content, 4, $xml_len);
        $from_appid  = substr($content, $xml_len + 4);

        if ($from_appid != $appid) {
            throw new \S\Exception("无效appid");
        }

        return $xml_content;
    }

} 
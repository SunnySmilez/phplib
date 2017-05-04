<?php
namespace Wechat\Message\Crypt;

use S\Exception;

/**
 * Class Aes
 *
 * @package     Modules\Wechat\Lib
 * @description 微信消息加解密工具类
 */
class Aes {

    /**
     * 消息加密方式：使用256bit密钥的rijndael-128-cbc 等价于aes-256-cbc
     * 使用128bit向量
     */
    const CIPHER_METHOD = 'aes-256-cbc';
    const BLOCK_SIZE = 32;

    /**
     * 加密
     *
     * @param string $encoding_aes_key
     * @param string $plain 明文
     *
     * @return string
     */
    public static function encrypt($encoding_aes_key, $plain) {
        $config = self::_getCipherConfig($encoding_aes_key);

        return openssl_encrypt(self::padding($plain), self::CIPHER_METHOD, $config["key"], $config["options"], $config["iv"]);
    }

    /**
     * 解密
     *
     * @param        $encoding_aes_key
     * @param string $cipher_text 需解密的密文串
     *
     * @return string 解密后的明文串
     */
    public static function decrypt($encoding_aes_key, $cipher_text) {
        $config = self::_getCipherConfig($encoding_aes_key);
        $plain  = self::unpadding(openssl_decrypt($cipher_text, self::CIPHER_METHOD, $config["key"], $config["options"], $config["iv"]));

        return $plain;
    }

    /**
     * 对需要加密的明文进行填充补位
     *
     * @param string $raw_msg 需要进行填充补位操作的明文
     *
     * @return string 补齐明文字符串
     */
    private static function padding($raw_msg) {
        $text_length = strlen($raw_msg);
        //计算需要填充的位数
        $amount_to_pad = self::BLOCK_SIZE - ($text_length % self::BLOCK_SIZE);
        if ($amount_to_pad == 0) {
            $amount_to_pad = self::BLOCK_SIZE;
        }
        //获得补位所用的字符
        $pad_chr = chr($amount_to_pad);
        $tmp     = "";
        for ($index = 0; $index < $amount_to_pad; $index++) {
            $tmp .= $pad_chr;
        }

        return $raw_msg . $tmp;
    }

    /**
     * 对解密后的明文进行补位删除
     *
     * @param string $raw_msg 解密后的明文
     *
     * @return string 删除填充补位后的明文
     */
    private static function unpadding($raw_msg) {
        $pad = ord(substr($raw_msg, -1));
        if ($pad < 1 || $pad > 32) {
            $pad = 0;
        }

        return substr($raw_msg, 0, (strlen($raw_msg) - $pad));
    }

    /**
     * 获取加密配置
     *
     * 包括：
     *     密钥
     *     向量
     *
     * @return array
     * @throws \S\Exception
     */
    private static function _getCipherConfig($encoding_aes_key) {
        if (43 != strlen($encoding_aes_key)) {
            throw new Exception("无效encoding_aes_key长度");
        }

        $encoding_aes_key = base64_decode($encoding_aes_key . "=");
        $iv               = substr($encoding_aes_key, 0, 16);

        return array(
            "key"     => $encoding_aes_key,
            "iv"      => $iv,
            "options" => OPENSSL_ZERO_PADDING,
        );
    }

} 
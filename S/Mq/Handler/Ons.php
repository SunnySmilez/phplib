<?php

namespace S\Mq\Handler;

/**
 * Class Ons
 *
 * @package S\Mq\Handler
 * @link    https://www.aliyun.com/product/ons?spm=5176.8142029.388261.68.hZPmYV
 */
class Ons extends Abstraction {

    const DEFAULT_POP_NUM = 32;  //默认单次拉取消息数量
    const DEFAULT_TAG = 'http';

    /**
     * @var \S\Http
     */
    protected $mq = null;

    public function pub($topic, $msg, array $option = array()) {
        $date    = time() * 1000;
        // 说明： URL 中的 Key，Tag 以及 POST Content-Type 没有任何的限制，只要确保 Key 和 Tag 相同唯一即可
        $tag = ($option['tag'] ?: self::DEFAULT_TAG);
        $path    = 'message/?topic=' . $topic . '&time=' . $date . '&tag=' . $tag . '&key=' . $tag;
        $signStr = $topic . PHP_EOL . $this->config['ProducerID'] . PHP_EOL . md5($msg) . PHP_EOL . $date;

        $options = array(
            'headers' => array(
                'Signature'    => $this->_getSign($signStr),
                'AccessKey'    => $this->config['AccessKey'],
                'ProducerID'   => $this->config['ProducerID'],
                'Content-Type' => 'text/html;charset=UTF-8',
            ),
        );

        $this->getInstance()->request(\S\Http::METHOD_POST, $path, $msg, $options);

        return ('201' == $this->getInstance()->getLastResponseInfo()->getStatusCode());
    }

    public function sub($topic, array $option = array()) {
        $date = time() * 1000;
        $path = 'message/';
        $data = array(
            'topic' => $topic,
            'time'  => $date,
            'num'   => $option['num'] ?: self::DEFAULT_POP_NUM,
        );

        $signStr = $topic . PHP_EOL . $this->config['ConsumerID'] . PHP_EOL . $date;
        $options = array(
            'headers' => array(
                'Signature'    => $this->_getSign($signStr),
                'AccessKey'    => $this->config['AccessKey'],
                'ConsumerID'   => $this->config['ConsumerID'],
                'Content-Type' => 'text/html;charset=UTF-8',
            ),
            'timeout' => 60,
        );

        $ret = $this->getInstance()->request(\S\Http::METHOD_GET, $path, $data, $options);

        return json_decode($ret, true);
    }

    public function delete($topic, $msg_handle) {
        $date = intval(microtime(true) * 1000);
        $path = 'message/?msgHandle=' . $msg_handle . '&topic=' . $topic . '&time=' . $date;

        $signStr = $topic . PHP_EOL . $this->config['ConsumerID'] . PHP_EOL . $msg_handle . PHP_EOL . $date;
        $options = array(
            'headers' => array(
                'Signature'    => $this->_getSign($signStr),
                'AccessKey'    => $this->config['AccessKey'],
                'ConsumerID'   => $this->config['ConsumerID'],
                'Content-Type' => 'text/html;charset=UTF-8',
            ),
        );

        $this->getInstance()->request(\S\Http::METHOD_DELETE, $path, null, $options);

        return ('204' == $this->getInstance()->getLastResponseInfo()->getStatusCode());
    }

    public function close() {
        $this->mq = null;

        return true;
    }

    protected function getInstance() {
        if (!$this->mq) {
            $this->mq = new \S\Http($this->config['url']);
        }

        return $this->mq;
    }

    /**
     * 计算签名
     *
     * @param string $str
     *
     * @return string
     */
    private function _getSign($str) {
        $key = $this->config['SecretKey'];

        if (function_exists("hash_hmac")) {
            $sign = base64_encode(hash_hmac("sha1", $str, $key, true));
        } else {
            $blockSize = 64;
            $hashfunc  = "sha1";
            if (strlen($key) > $blockSize) {
                $key = pack('H*', $hashfunc($key));
            }
            $key  = str_pad($key, $blockSize, chr(0x00));
            $ipad = str_repeat(chr(0x36), $blockSize);
            $opad = str_repeat(chr(0x5c), $blockSize);
            $hmac = pack(
                'H*', $hashfunc(
                    ($key ^ $opad) . pack(
                        'H*', $hashfunc($key ^ $ipad) . $str
                    )
                )
            );
            $sign = base64_encode($hmac);
        }

        return $sign;
    }

}
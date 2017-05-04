<?php
namespace S\Msg;

use S\Exception;

class Mail {
    protected $config = array();
    protected $debug = false;
    protected $need_config = array(
        'host',
        'port',
        'user',
        'pwd',
        'nick',
    );

    public function __construct($key) {
        if (is_array($key)) {
            $config = $key;
        } else {
            $config = \S\Config::confServer('mail.' . $key);
            if (!$config) {
                throw new Exception("mail $key config not find");
            }
        }

        foreach ($this->need_config as $key) {
            if (!array_key_exists($key, $config)) {
                throw new Exception("mail config must have $key");
            }
        }
        $this->config = $config;
    }

    public function setDebug($debug = 0) {
        $this->debug = intval($debug);
    }

    public function send($to, $title, $content, $file = null) {
        $mail = new \PHPMailer();

        $mail->isSMTP();
        $mail->CharSet     = "UTF-8";
        $mail->SMTPDebug   = $this->debug;
        $mail->Debugoutput = 'html';
        $mail->Host        = $this->config['host'];
        $mail->Port        = $this->config['port'];
        $mail->SMTPAuth    = true;
        $mail->Username    = $this->config['user'];
        $mail->Password    = $this->config['pwd'];
        $mail->setFrom($this->config['user'], $this->config['nick']);
        $mail->Subject = $title;
        $mail->msgHTML($content);

        if (is_array($to)) {
            foreach ($to as $v) {
                $mail->addAddress($v);
            }
        } else {
            $mail->addAddress(strval($to));
        }

        if (is_array($file)) {
            foreach ($file as $v) {
                $mail->addAttachment($v);
            }
        } else {
            $mail->addAttachment($file);
        }
        if (!$mail->send()) {
            return false;
        } else {
            return true;
        }
    }
}
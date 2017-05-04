<?php
namespace S\Captcha\Handler;

use S\Captcha\Util;
use S\Exception;

class Mail implements CaptchaInterface{

    protected $need_args = array('address', 'title', 'template', 'service');

    public function show($args){
        $this->checkArgs($args);

        $sender = new \S\Msg\Mail($args['service']);
        $title  = Util::content($args['code'], $args['title']);
        $content = Util::content($args['code'], $args['template']);
        $ret = $sender->send($args['address'], $title, $content);

        if($ret){
            return true;
        }else{
            return false;
        }
    }

    protected function checkArgs($args){
        foreach($this->need_args as $key){
            if(!array_key_exists($key, $args)){
                throw new Exception("send mail captcha / config must have '$key''");
            }
        }
    }
}
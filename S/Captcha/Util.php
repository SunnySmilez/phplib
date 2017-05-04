<?php
namespace S\Captcha;

use \S\Exception;
use \S\Util\Rand;

class Util{

    const MARK = '%captcha%';

    public static function create($type, $length){
        switch($type){
            case Captcha::TYPE_DIGIT:
                $code = Rand::digit($length);
                break;
            case Captcha::TYPE_ENGLISH:
                $code = Rand::alpha($length);
                break;
            case Captcha::TYPE_ENGLISH_LOW:
                $code = Rand::lower($length);
                break;
            case Captcha::TYPE_ENGLISH_HIGH:
                $code = Rand::upper($length);
                break;
            case Captcha::TYPE_MIX:
                $code = Rand::alnum($length);
                break;
            default:
                throw new Exception(__CLASS__." captcha $type is not found");
        }

        return $code;
    }

    public static function content($code, $modules){
        return str_replace(self::MARK, $code, $modules);
    }
}
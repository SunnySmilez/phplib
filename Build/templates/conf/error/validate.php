<?php
/**
 * 此错误配置仅供参数校验使用
 *
 * 参考http响应码定义中客户端错误码的格式, 参数校验错误属于客户端错误的一种, 因此使用4开头的错误码
 * @document https://tools.ietf.org/html/rfc2616#page-65
 *
 * rfc规范中对于4**客户端错误定义如下:
 * The 4xx class of status code is intended for cases in which the
 * client seems to have erred.
 */
return array(
    'demo' => array('retcode'  => 4000000, 'msg'   => '参数错误'),
);
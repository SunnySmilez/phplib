<?php
/**
 * 业务异常定义
 *
 * 5开头错误码定义说明:
 * 参考http响应码5**定义, 当遇到5开头的错误码时认为本次请求出现异常导致流程无法正常进行, 业务异常使用此格式进行定义
 * @document https://tools.ietf.org/html/rfc2616#page-70
 *
 * rfc规范中对于5**格式状态码描述如下:
 * Response status codes beginning with the digit "5" indicate cases in
 * which the server is aware that it has erred or is incapable of
 * performing the request.
 */
return array(
    'demo_fail_check_error' => array('retcode' => 5001001, 'msg' => '校验失败', 'user_msg' => '业务校验失败, 请重试!'),
);
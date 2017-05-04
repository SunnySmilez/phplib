<?php
return array(
    // ip
    'ip_format'         => array(400110000, 'ip format error'),

    // 数字检查
    'digit_too_small'   => array(400110010, 'digit too small'),
    'digit_too_large'   => array(400110011, 'digit too large'),

    // 字符串检查
    'string_too_short'  => array(400110020, 'string too short'),
    'string_too_long'   => array(400110021, 'string too long'),

    // 邮箱验证
    'email_length'      => array(400110030, 'email length 6-64 chars'),
    'email_format'      => array(400110031, 'email format error'),
    'email_mx_not_exist'=> array(400110032, 'mx record not exist'),

    // in检查
    'in_not_in_haystack'=> array(400110040, 'not in'),

    // 正则检查
    'regx_not_matched'  => array(400110050, 'not matched'),

    // url
    'url_format'        => array(400110060, 'url format error'),
    'url_invalid'       => array(400110061, 'url is invalid'),
    'url_not_trusted'   => array(400110062, 'refer is not trusted'),

    //金钱格式错误
    'money_error'       => array(400110070, 'money format error'),
    
    // 手机格式检测
    'phone_format_error'=> array(400110080, 'phone format error'),

    //卡号格式检测
    'card_error'        => array(400110090, 'card format error'),

    //字符串长度检测
    'string_error'      => array(400110100, 'string is not format length'),

    'identify_error'    => array(400110110, 'id card not format')
);

<?php

namespace S\Validate;

/**
 * 参数验证类
 *
 * 介绍
 * <ol>
 *  <li>default_validator 数组定义了一些默认的validator的简写，如rule=>email，对应的是Validator_Email类；</li>
 *  <li>此方法返回值为验证并“处理”后的“参数数组”（非原数组）；</li>
 *  <li>参数数组中的key将与返回值数组中的key一一对应；</li>
 *  <li>简写的几种方式：'ip' => $ip, // same as 'ip'=>array('value'=>$ip) or 'ip'=>array('value'=>$ip, 'rule'=>'ip')</li>
 *  <li>当 rule 不在default_validator中，将会作为callback方式调用</li>
 *  <li>当 rule === 'filter' 时，直接使用 filter_var($value, FILTER_SANITIZE_FULL_SPECIAL_CHARS) 过滤变量</li>
 *  <li>class Code extends \S\Validate\Abstraction {}</li>
 * </ol>
 *
 * <code>
 *  $params = array(
 *      'email' => array('value'=>$email, 'rule'=>'email', 'filter'=>Validator::VALIDATOR, 'option'=>array('min'=>6,'max'=>64)),
 *      'ip'    => $ip, // same as 'ip'=>array('value'=>$ip) or 'ip'=>array('value'=>$ip, 'rule'=>'ip'),
 *
 *      // 对于一些可选参数，并且业务逻辑中有明确的对比规则，可以选择此种方式
 *      'from' => array('value'=>\S\Request::get('from'), 'filter'=>Validator::FILTER, 'rule'=>'trim'),
 *      // 自定义验证类
 *      'code' => array('value'=>\S\Request::post('code'), 'filter'=>Validator::VALIDATOR, 'rule'=>new \Util\Validator\Code),
 *  );
 *  $result = Validator::check($params);
 * </code>
 *
 */
class Handler {
    const FILTER    = 1,
        VALIDATOR   = 2;

    private static $_default_validator = array(
        'date'     =>  'Type\\Date',
        'digit'    =>  'Type\\Digit',
        'email'    =>  'Type\\Email',
        'in'       =>  'Type\\In',
        'ip'       =>  'Type\\Ip',
        'phone'    =>  'Type\\Phone',
        'regx'     =>  'Type\\Regx',
        'str'      =>  'Type\\Str',
        'url'      =>  'Type\\Url',
        'money'    =>  'Type\\Money',
        'card'     =>  'Type\\Card',
        'identify' =>  'Type\\Identify',
    );

    /**
     * 参数检查入口
     * 根据rule确定具体的验证方法，并将value，filter，option作为参数调用该调用
     *
     * @param array $params
     * @return null
     */
    public static function check(array $params) {
        $ret = array();
        foreach ($params as $name=>$info) {
            if (!is_array($info)) {
                $rule = $name;
                $param = array($info, self::VALIDATOR);
            } else {
                if (isset($info['rule'])) {
                    if ($info['rule'] === 'filter') {
                        $ret[$name] = filter_var($info['value'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                        continue;
                    } elseif ($info['rule'] === 'none') {
                        $ret[$name] = $info['value'];
                        continue;
                    }
                    $rule = $info['rule'];
                } else {
                    $rule = $name;
                }

                $param = array($info['value']);
                array_push($param, isset($info['filter']) ? $info['filter'] : self::VALIDATOR);
                array_push($param, isset($info['option']) ? $info['option'] : array());
            }

            if (is_string($rule) && isset(self::$_default_validator[$rule])) {
                $class = '\\'.__NAMESPACE__ .'\\'. self::$_default_validator[$rule];
                $tmp_class  = new $class;
                $tmp_ret    = call_user_func_array($tmp_class, $param);
                /* @var $tmp_class Abstraction */
                if (is_array($tmp_ret) && $tmp_class->is_extract_array()) {
                    // 注意，这里不会覆盖已存在的key，可能会引起问题，需要注意！
                    $ret += $tmp_ret;
                    continue;
                }
            } else {
                if ($rule instanceof Abstraction) {
                    // 自定义验证类
                    $tmp_ret = call_user_func_array($rule, $param);
                } else {
                    // 自定义验证函数
                    $tmp_ret = call_user_func_array($rule, $param);
                }
            }

            $ret[$name] = $tmp_ret;
        }
        return $ret;
    }
}

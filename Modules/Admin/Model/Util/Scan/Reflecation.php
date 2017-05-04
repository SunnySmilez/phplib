<?php
namespace Modules\Admin\Model\Util\Scan;

use S\Exception;

/**
 * @todo 对namespace支持
 */
class Reflecation {
    const RULE_EXTENDS      = 'extends',        // array 父类限制，与implements是或的关系
        RULE_IMPLEMENTS     = 'implements',     // array 接口限制，与extends是或的关系
        RULE_CLASSES        = 'classes',        // array 类名限制，与其他条件是与的关系
        RULE_CLASS_PREFIX   = 'class_prefix',   // string 类名前缀
        RULE_METHODS        = 'methods',        // array 方法名限制
        USE_AUTOLOAD        = 'use_autoload';   // 是否使用autoload，false时则直接include file

    private static $_filter_class_postfix = array('abstract', 'base');

    /**
     * 根据类的文件名确定类名，获取所有可调用的方法名（可用多态重写）
     *
     * @param string $file
     * @param array  $rule
     * @return array|bool
     */
    public static function classes($file, array $rule=array()) {
        if ($file[0] === '.') return false;

        if (!preg_match('/^.+\.php$/', $file)) {
            return false;
        }
        $class  = substr($file, 0, strrpos($file, '.'));
        if (!$rule[self::USE_AUTOLOAD]) {
            // TODO 注意目录分隔符
            $class = substr($class, strrpos($class, DIRECTORY_SEPARATOR)+1);
            if ($class[0] === '.') return false;
        }
        try {
            //TODO 类不存在|类中有错误...
            $class = isset($rule[self::RULE_CLASS_PREFIX]) && $rule[self::RULE_CLASS_PREFIX] ? $rule[self::RULE_CLASS_PREFIX].$class : $class;
            if (!$rule[self::USE_AUTOLOAD]) {
                require_once str_replace('_', DIRECTORY_SEPARATOR, $file);
            }
            $reflection = new \ReflectionClass('\\'.$class);
            if (!self::isMatchedClass($reflection, $rule)) {
                return false;
            }
            $comment = $reflection->getDocComment();
            //$methods = isset($rule[self::RULE_METHODS]) && $rule[self::RULE_METHODS] ? self::get_mectod_name($reflection, $rule) : array();
            return array(
                $class=>array(
                    'name'	    => self::getMethodAnnotationName($comment, $class),
                    'methods'   => self::getMethodName($reflection, $rule)
                )
            );
        } catch (Exception $e) {
            //TODO log
        }
        return false;
    }

    /**
     * 确认当前的类是否满足需求
     * @param \ReflectionClass $class
     * @param array $rule
     * @return bool
     */
    public static function isMatchedClass(\ReflectionClass $class, array $rule) {
        $class_name = $class->name;
        // 类名的过滤条件
        $filter     = isset($rule[self::RULE_CLASSES]) ? (array)$rule[self::RULE_CLASSES] : array();
        $is_ok      = true;
        foreach (self::$_filter_class_postfix as $fc) {
            if (strtolower(substr($class_name, -strlen($fc))) === $fc) {
                return false;
            }
        }
        if ($filter) {
            $is_ok = false;
            foreach ($filter as $r) {
                if (preg_match($r, $class_name)) {
                    $is_ok = true;
                    break;
                }
            }
            if (!$is_ok) {
                return false;
            }
        }
        // 继承关系和接口实现关系
        $extends    = isset($rule[self::RULE_EXTENDS]) ? (array)$rule[self::RULE_EXTENDS] : array();
        if ($extends) {
            foreach ($extends as $c) {
                if ($class->isSubclassOf($c)) {
                    return true;
                }
            }
        }
        $implements = isset($rule[self::RULE_IMPLEMENTS]) ? (array)$rule[self::RULE_IMPLEMENTS] : array();
        if ($implements) {
            foreach ($implements as $if) {
                if ($class->implementsInterface($if)) {
                    return true;
                }
            }
        }
        return $is_ok;
    }

    public static function isMatchedMethod(\ReflectionMethod $method, array $rule) {
        $filter = isset($rule[self::RULE_METHODS]) ? (array)$rule[self::RULE_METHODS] : array();
        if (!$filter) {
            return true;
        }
        foreach ($filter as $r) {
            if (preg_match($r, $method->name)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 根据类名，查询所有满足rule条件的public方法名
     *
     * @param \ReflectionClass $reflection
     * @param array $rule
     * @return array
     */
    public static function getMethodName(\ReflectionClass $reflection, array $rule) {
        $methods	= $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
        $results	= array();
        if (is_array($methods)) {
            /** @var \ReflectionMethod $m */
            foreach ($methods as $m) {
                if (self::isMatchedMethod($m, $rule)) {
                    $results[$m->name]= array(
                        'name'	=> self::getMethodAnnotationName($m->getDocComment(), $m->name),
                        //'format' => self::getResponseFormat($m->getDocComment(), '')
                    );
                }
            }
        }
        return $results;
    }

    /**
     * 获取action注释中的名称（显示用）
     * @param string $comment
     * @param string $default
     * @return string
     */
    public static function getMethodAnnotationName($comment, $default='') {
        $matche		= array();
        $name		= '';
        if (preg_match('/\@name\s+(.+)/i', $comment, $matche)) {
            $name = strtolower(trim($matche[1]));
        }
        return $name ? $name : $default;
    }

    /**
     * 获取action注释中的相应格式
     * @param string $comment
     * @param string $default
     * @return string
     */
    public static function getResponseFormat($comment, $default='') {
        $match		= array();
        $showformat	= '';
        if (preg_match('/\@format\s+(.+)/i', $comment, $match)) {
            $showformat = strtolower(trim($match[1]));
        }
        return $showformat ? $showformat : $default;
    }
}

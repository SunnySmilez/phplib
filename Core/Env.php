<?php
namespace Core;

use S\Response;

define('APP_ENVIRON_DEV', 'dev');
define('APP_ENVIRON_TEST', 'test');
define('APP_ENVIRON_REALTEST', 'realtest');
define('APP_ENVIRON_PRODUCT', 'product');

/**
 * Class Env
 *
 * @package     Core
 * @description 环境常量定义
 *
 * 1.环境变量设置问题
 * 2.开发测试生成环境判断问题
 */
class Env {

    const IDC_BJ = 'bj';
    const IDC_HZ = 'hz';
    const IDC_DEFAULT = self::IDC_HZ;

    public static $namespace = array('Service', 'Data', 'Dao', 'Jobs');
    public static $charset = "utf-8";

    public static $idcs = array(
        self::IDC_BJ,
        self::IDC_HZ,
        self::IDC_DEFAULT,
    );

    private static $controller = array();
    private static $module = array();
    private static $action = array();

    private static $cli_class = '';

    /**
     * 初始化环境
     *
     * @param \Yaf\Request_Abstract  $request
     * @param \Yaf\Response_Abstract $response
     */
    public static function init(\Yaf\Request_Abstract $request, \Yaf\Response_Abstract $response) {
        array_push(self::$module, $request->getModuleName() ?: '');
        array_push(self::$controller, $request->getControllerName() ?: '');
        array_push(self::$action, $request->getActionName() ?: '');

        //初始化请求类型
        if (\S\Request::isAjax()) {
            Response::setFormatter(Response::FORMAT_JSON);
        } else {
            Response::setFormatter(Response::FORMAT_HTML);
        }
    }

    /**
     * 获取当前环境名称
     *
     * @return string dev|test|realtest|product
     */
    public static function getEnvName() {
        $environ = \Yaf\Application::app()->environ();

        return $environ;
    }

    public static function getIdc() {
        $area = ini_get("yaf.area");
        if (in_array($area, self::$idcs)) {
            return $area;
        } else {
            return self::IDC_DEFAULT;
        }
    }

    /**
     * 获取控制器名称
     *
     * @param bool $is_first
     *
     * @return string
     */
    public static function getControllerName($is_first = false) {
        if ($is_first) {
            return self::$controller[0];
        } else {
            return \Yaf\Application::app()->getDispatcher()->getRequest()->getControllerName();
        }
    }

    /**
     * 获取模块名称
     *
     * @param bool $is_first
     *
     * @return string
     */
    public static function getModuleName($is_first = false) {
        if ($is_first) {
            return self::$module[0];
        } else {
            return \Yaf\Application::app()->getDispatcher()->getRequest()->getModuleName();
        }
    }

    public static function getCharset() {
        return self::$charset;
    }

    public static function getCliClass() {
        return self::$cli_class;
    }

    public static function setCliClass($class) {
        self::$cli_class = $class;

        return true;
    }

    /**
     * 判断是否生产环境
     *
     * 包括: 仿真和线上正式集群
     *
     * @return bool true-生产环境 false-开发环境
     */
    public static function isProductEnv() {
        if (self::getEnvName() === APP_ENVIRON_PRODUCT || self::getEnvName() === APP_ENVIRON_REALTEST) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 判断仿真环境
     *
     * @return bool
     */
    public static function isRealTest() {
        if (self::getEnvName() === APP_ENVIRON_REALTEST) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 判断是否cli模式
     *
     * cli模式定义
     *
     * @link http://php.net/manual/en/features.commandline.introduction.php
     *
     * @return bool true-cli模式 false-非cli模式
     */
    public static function isCli() {
        return php_sapi_name() === 'cli' ? true : false;
    }

    /**
     * 判断是否phpunit环境
     *
     * 根据APP_TEST/phpunit.xml中APP_ENV的值进行判断
     *
     * @return bool true-phpunit环境 false-非phpunit环境
     */
    public static function isPhpUnit() {
        return $_ENV['APP_ENV'] === 'phpunit' ? true : false;
    }

}
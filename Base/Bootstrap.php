<?php
namespace Base;

/**
 * Class Bootstrap
 * @package Base
 * @desc 进行初始化工作
 */
class Bootstrap extends \Yaf\Bootstrap_Abstract
{
    /**
     * 全局设置开发加载
     */
    public function _initBaseSet(\Yaf\Dispatcher $dispatcher) {
        //加载APP相关设置
        require APP_CONF . "/defined.php";
        //视图不自动渲染
        $dispatcher->autoRender(false);
    }

    /**
     * 在此处注册非YAF的autoload
     * 注册YAF的localnamespace和map
     */
    public function _initBaseLoder() {
        //yaf autoloader
        \Yaf\Loader::getInstance()->registerLocalNamespace(\Core\Env::$namespace);
        //自身 autoloader
        \Core\Loader::init();
    }

    /**
     * 注册插件
     * @param \Yaf\Dispatcher $dispatcher
     */
    public function _initBasePlugin(\Yaf\Dispatcher $dispatcher){
        $dispatcher->registerPlugin(new Plugin\Base());
    }

    /**
     * 开启日志模式
     */
    public function _initBaseLog(){
        if(\Core\Env::isProductEnv()){
            \S\Log\Logger::getInstance()->pushHandler(new \S\Log\Handler\Rsyslog());
        }else{
            \S\Log\Logger::getInstance()->pushHandler(new \S\Log\Handler\File());
        }
    }

    /**
     * 开启debug
     */
    public function _initBaseDebug(){
        if (\Core\Env::getEnvName() === APP_ENVIRON_DEV || \Core\Env::getEnvName() === APP_ENVIRON_TEST) {
            ini_set('display_errors', true);
            ini_set('xdebug.var_display_max_depth', 10);
            error_reporting(E_ALL ^ E_NOTICE);
        }
    }

}
<?php

/**
 * Class Bootstrap
 *
 * @description 微信模块启动项加载服务
 */
class Bootstrap extends \Base\Bootstrap {

    public function _initDefine() {
        /**
         * 定义微信公众号名称常量, 有多个公众号存在时注意区分
         */
        define('WECHAT_NAME_DEMO', 'demo');

        define('WECHAT_TPL_PATH', PHPLIB . "/Modules/Wechat/Views");
        define('WECHAT_PATH', '/wechat');
    }

    public function _initPlugin(\Yaf\Dispatcher $dispatcher) {
        $dispatcher->registerPlugin(new Plugin_Wechat());
    }

    /**
     * 在此处注册非YAF的autoload
     * 注册YAF的localnamespace和map
     */
    public function _initBaseLoder() {
        parent::_initBaseLoder();

        \Core\Loader::register_autoloader(array('modules\\Wechat'));
    }

}
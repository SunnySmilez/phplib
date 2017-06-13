<?php
namespace Base;

/**
 * job的bootstrap
 * Class JobBootstrap
 * @package Base
 */
class JobBootstrap extends \Base\Bootstrap {
    /**
     * 在此处注册非YAF的autoload
     * 注册YAF的localnamespace和map
     */
    public function _initBaseLoader() {
        parent::_initBaseLoder();
        \Core\Loader::register_autoloader(array('Jobs'));
    }

    /**
     * 加载进程环境变量
     */
    public function _initEnv() {
        if(!$_SERVER['x-rid']){
            $_SERVER['x-rid'] = time();
        }
    }
}
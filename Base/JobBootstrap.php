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
    public function _initBaseLoder() {
        parent::_initBaseLoder();
        \Core\Loader::register_autoloader(array('Jobs'));
    }
}
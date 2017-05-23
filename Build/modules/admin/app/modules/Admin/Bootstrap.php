<?php
class Bootstrap extends \Base\Bootstrap {

    public function _initDefine(){
        define('ADMIN_BASE_TPL_PATH', PHPLIB . "/Modules/Admin/Views/");
        define('ADMIN_SYS_NAME', "@appname@管理系统"); // 后台管理名称
        define('ADMIN_AUTH_BASE_URL', 'http://auth.com');  //身份认证服务地址, 根据实际进行替换
        define('ADMIN_STATIC_PATH', 'https://static-admin.oss-cn-beijing.aliyuncs.com'); //管理后台静态资源地址
    }

    public function _initPlugin(\Yaf\Dispatcher $dispatcher){
        $dispatcher->registerPlugin(new Plugin_Admin());
    }

    /**
     * 在此处注册非YAF的autoload
     * 注册YAF的localnamespace和map
     */
    public function _initBaseLoder() {
        parent::_initBaseLoder();
        \Core\Loader::register_autoloader(array('modules\\Admin'));
    }
}
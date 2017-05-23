<?php
namespace Modules\Admin\Controllers;

use S\Request;
use Base\Controller\Action;
use Base\Exception\Controller as Exception;
use Modules\Admin\Model\Data\Acl as DataAcl;
use Modules\Admin\Model\Data\Menu as DataMenu;

abstract class Base extends Action {

    protected $sys_view = false; //是否使用系统的模板文件

    final public function init() {
        $this->_checkAccessIp();
        if (!in_array($this->getRequest()->getControllerName(), DataAcl::getNoLoginController())) {
            $this->_checkAccessLogin();
            $this->_checkAcl();
        }

        return true;
    }

    /**
     * 页面渲染输出
     *
     * @param $tpl_vars
     *
     * @return bool
     */
    public function displayView($tpl_vars) {
        if ($this->sys_view) {
            //使用phplib下的模板文件
            $this->_view->setScriptPath(ADMIN_BASE_TPL_PATH);
        }

        $this->buildMenu();

        $ext      = \Yaf\Application::app()->getConfig()->get('yaf.view.ext');
        $tpl_path = strtolower($this->getRequest()->controller) . DIRECTORY_SEPARATOR . strtolower($this->getRequest()->action) . '.' . $ext;
        $this->_view->display($tpl_path, $tpl_vars);

        return true;
    }

    public function getRenderView($tpl = null, array $response = array()) {
        if ($this->sys_view) {
            //使用phplib下的模板文件
            $this->_view->setScriptPath(ADMIN_BASE_TPL_PATH);
        }

        if (!$tpl) {
            $ext = \Yaf\Application::app()->getConfig()->get('yaf.view.ext');
            $tpl = strtolower($this->getRequest()->getControllerName()) . DIRECTORY_SEPARATOR .
                strtolower($this->getRequest()->getActionName()) . "." . $ext;
        }
        \Yaf\Dispatcher::getInstance()->autoRender(false);
        $ret = $this->_view->render($tpl, $response);
        \Yaf\Dispatcher::getInstance()->autoRender(true);

        return $ret;
    }

    /**
     * 生成菜单并把变量抛向需要菜单有菜单的模版
     *
     * @return boolean|void
     */
    protected function buildMenu() {
        $is_admin = \S\Request::session('isadmin');
        $menu     = (new DataMenu())->getNavMenu($is_admin);

        $ext      = \Yaf\Application::app()->getConfig()->get('yaf.view.ext');
        $menuview = $this->_view->render(ADMIN_BASE_TPL_PATH . 'inc/menu.' . $ext, array('menu' => $menu));
        $this->_view->assign('menuview', $menuview);

        return true;
    }

    /**
     * 校验ip访问是否合法
     *
     * @return bool true-校验成功
     * @throws Exception
     */
    private function _checkAccessIp() {
        $client_ip = \S\Util\Ip::getClientIp();
        if (!$client_ip || (!\S\Util\Ip::isPrivateIp($client_ip) && !(new \Modules\Admin\Model\Data\Sysconfig\Ip())->isWhiteList($client_ip))) {
            throw new Exception('error.admin.illegal_request');
        }

        return true;
    }

    /**
     * 登录校验
     *
     * @return bool
     * @throws Exception
     */
    private function _checkAccessLogin() {
        if (Request::session('uid')) {
            return true;
        } else {
            $error_config = \S\Config::confError('admin.sign_in_again');
            $msg          = sprintf($error_config['msg'], APP_ADMIN_PATH);
            throw new Exception($msg, $error_config['retcode']);
        }
    }

    /**
     * 权限校验
     *
     * @return bool
     * @throws Exception
     */
    private function _checkAcl() {
        $controller = $this->getRequest()->controller;
        $action     = $this->getRequest()->action;

        if (DataAcl::isAccess($controller, $action)) {
            return true;
        } else {
            $url          = $_SERVER['HTTP_REFERER'] ?: APP_ADMIN_PATH . '/welcome/index';
            $error_config = \S\Config::confError('admin.can_not_access');
            $msg          = sprintf($error_config['msg'], $controller, $action, $url);
            throw new Exception($msg, $error_config['retcode']);
        }
    }

}

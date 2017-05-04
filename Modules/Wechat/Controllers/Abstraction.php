<?php
namespace Modules\Wechat\Controllers;

/**
 * control层的父类
 * Class Abstraction
 *
 * @package Modules\Wechat\Controllers
 */
abstract class Abstraction extends \Base\Controller\Common {

    /**
     * 页面渲染输出
     *
     * @param $tpl_vars
     *
     * @return bool
     */
    public function displayView($tpl_vars) {
        $ext      = \Yaf\Application::app()->getConfig()->get('yaf.view.ext');
        $tpl_path = str_replace('_', DIRECTORY_SEPARATOR, $this->getRequest()->controller) . '.' . $ext;
        $this->_view->display(ucfirst($tpl_path), $tpl_vars);
    }

}

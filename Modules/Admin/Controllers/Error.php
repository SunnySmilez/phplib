<?php
namespace Modules\Admin\Controllers;

use S\Response;

/**
 * 在此做错误和异常统一处理
 */
class Error extends \Base\Controller\Error {

    protected function view(\Exception $e, $is_sys=false){
        if($is_sys === true && !\Core\Env::isProductEnv()) {
            //开发环境提示错误信息
            $e = $this->getRequest()->getException();
            $tpl_vars['code']     = $e->getCode();
            $tpl_vars['message']  = $e->getMessage();
            $tpl_vars['line']     = $e->getLine();
            $tpl_vars['file']     = $e->getFile();
            $tpl_vars['exception'] = $e;
            $this->_view->display(PHPLIB."/Base/View/DevError.phtml", $tpl_vars);
        }else{
            $retcode = $e->getCode();
            $msg = ($is_sys === false)?$e->getMessage():self::ERROR_MSG;

            if (Response::getFormatter() === Response::FORMAT_PLAIN) {
                Response::outPlain($msg);
            } elseif (Response::getFormatter() === Response::FORMAT_JSON) {
                Response::outJson($retcode, $retcode . "," .$msg);
            } else {
                $tpl_vars['retcode'] = $retcode;
                $tpl_vars['msg']     = $msg;
                $this->_view->display(ADMIN_BASE_TPL_PATH."error.phtml", $tpl_vars);
            }
        }
    }

}
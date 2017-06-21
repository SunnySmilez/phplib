<?php
namespace Core;

use \S\Response;

class Error {

    /**
     * 异常及error处理方式
     *
     * @param \Throwable $e
     *
     * @return bool
     */
    public static function handle(\Throwable $e) {
        //记录info日志
        $ret_info = array(
            'retcode' => $e->getCode(),
            'retmsg' => $e->getMessage(),
        );
        \S\Log\Context::setInfo($ret_info);
        \S\Log\Logger::getInstance()->info();

        //\Error或原生\Exception记录error日志
        if ($e instanceof \Error || !($e instanceof \Base\Exception\Abstraction
            || $e instanceof \S\Exception || $e instanceof \S\Validate\Exception)) {
            $msg = array(
                'exception' => $e,
            );
            \S\Log\Logger::getInstance()->error($msg);
        }

        //cli模式下直接抛出异常信息
        if (\Core\Env::isCli() && !\Core\Env::isPhpUnit()) {
            throw $e;
        }

        //开发环境下打印调试信息(不包括业务流程异常和参数校验异常)
        if (!\Core\Env::isProductEnv() &&
            !$e instanceof \Base\Exception\Abstraction && !$e instanceof \S\Validate\Exception) {
            //判断error和异常的级别
            $response['code']      = $e->getCode();
            $response['message']   = $e->getMessage();
            $response['line']      = $e->getLine();
            $response['file']      = $e->getFile();
            $response['exception'] = $e;

            $view                  = new \Yaf\View\Simple(PHPLIB . "/Base/View/");
            $view->display('DevError.phtml', $response);

            return true;
        }

        //截获404请求特殊返回
        if (self::isNotFound($e)) {
            \S\Response::header404(\Yaf\Application::app()->getDispatcher()->getRequest()->getRequestUri());
        }

        if (\Core\Env::isPhpUnit()) {//测试用例处理
            ob_start();
        }

        if (Response::getFormatter() === Response::FORMAT_PLAIN) {
            Response::displayPlain($e->getMessage());
        } else if (Response::getFormatter() === Response::FORMAT_JSON) {
            Response::displayJson(array(), $e->getCode(), $e->getMessage());
        } else if (0 === strpos(\S\Request::server('PATH_INFO'), '/admin')) {
            $view = new \Yaf\View\Simple(ADMIN_BASE_TPL_PATH);
            $view->display('error.phtml', array(
                'retcode' => $e->getCode(),
                'msg'     => $e->getMessage(),
            ));
        } else {
            $view = new \Yaf\View\Simple(PHPLIB . "/Base/View/");
            $view->display('Error.phtml', array(
                'retcode' => $e->getCode(),
                'msg'     => $e->getMessage(),
            ));
        }

        if (\Core\Env::isPhpUnit()) {//测试用例处理
            \Yaf\Registry::set('test_response', json_encode(array('retcode' => $e->getCode(), 'msg' => $e->getMessage())));
            ob_end_clean();
        }

        return true;
    }


    /**
     * 判断错误是否为404错误
     *
     * @return bool
     */
    public static function isNotFound(\Throwable $e) {
        if (!is_a($e, '\\Yaf\\Exception')) {
            return false;
        }
        switch ($e->getCode()) {
            case 515://YAF_ERR_NOTFOUND_MODULE
                return true;
            case 516://YAF_ERR_NOTFOUND_CONTROLLER
                return true;
            case 517://YAF_ERR_NOTFOUND_ACTION
                return true;
            default:
                return false;
        }
    }

    public function cliShowError() {

    }

}
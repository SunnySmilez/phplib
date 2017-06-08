<?php
namespace Core;

use \S\Response;

class Error {
    //异常及error处理方式
    public static function handle(\Throwable $e){
        //记录日志
        \S\Log\Logger::getInstance()->info();

        //开发环境下打印调试信息
        if (!\Core\Env::isProductEnv() && !is_a($e, '\\Base\\Exception\\Abstraction')){
            //判断error和异常的级别
            $response['code']     = $e->getCode();
            $response['message']  = $e->getMessage();
            $response['line']     = $e->getLine();
            $response['file']     = $e->getFile();
            $response['exception'] = $e;
            $view = new \Yaf\View\Simple(PHPLIB."/Base/View/");
            $view->display('DevError.phtml', $response);
            return true;
        }

        //截获404请求特殊返回
        if(self::isNotFound($e)){
            \S\Response::header404(\Yaf\Application::app()->getDispatcher()->getRequest()->getRequestUri());
        }

        if (\Core\Env::isPhpUnit()) {//测试用例处理
            ob_start();
        }

        if (Response::getFormatter() === Response::FORMAT_PLAIN) {
            Response::displayPlain($e->getMessage());
        } elseif (Response::getFormatter() === Response::FORMAT_JSON) {
            Response::displayJson(array(), $e->getCode(), $e->getMessage());
        } else {
            $view = new \Yaf\View\Simple(PHPLIB."/Base/View/");
            $view->display('Error.phtml', array(
                'retcode' => $e->getCode(),
                'msg'     => $e->getMessage()
            ));
        }

        if (\Core\Env::isPhpUnit()) {//测试用例处理
            \Yaf\Registry::set('test_response', json_encode(array('retcode' => $e->getCode(), 'msg' => $e->getMessage())));
            ob_end_clean();
        }
    }


    /**
     * 判断错误是否为404错误
     * @return bool
     */
    public static function isNotFound(\Throwable $e){
        if (!is_a($e, '\\Yaf\\Exception')) {
            return false;
        }
        switch($e->getCode()){
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

    public function cliShowError(){

    }
}
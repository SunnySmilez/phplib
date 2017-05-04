<?php
namespace Base\Controller;
/**
 * 在此做错误和异常统一处理
 *
 *
 * 异常分为
 * controller和validate 参数检查异常和逻辑异常
 * dao
 * data
 * service
 * 以上5处异常信息可以展示给用户，也可以隐藏，返回系统异常
 * S和EXCEPTION    此处异常信息不应该展示给用户，返回系统异常
 *
 *
 * 异常处理
 * 页面的异常通常跳转到错误页面，页面上指出错误信息
 * ajax请求异常给返回json信息
 * api请求异常给返回json信息
 */
use S\Response;

/**
 * 当有未捕获的异常, 则控制流会流到这里
 */
class Error extends \Yaf\Controller_Abstract {
    const ERROR_MSG = '系统繁忙,稍后重试';

    public $use_self_view = false; //是否使用自定义的错误页面模版  默认使用系统自带的错误页面模版

    public function init(){
        \Yaf\Dispatcher::getInstance()->autoRender(false);
    }

    /**
     * 此时可通过$request->getException()获取到发生的异常
     * 列举了所有在控制内的异常
     */
    public function errorAction(){
        try {
            throw $this->getRequest()->getException();
        } catch (\S\Validate\Exception $e) {
            $this->view($e);
        } catch (\Base\Exception\Abstraction $e) {
            $this->view($e);
        } catch (\S\Exception $e) {
            $this->view($e, true);
        } catch (\Yaf\Exception $e) {
            $this->view($e, true);
        } catch (\Exception $e) {
            $this->view($e, true);
        }

        //判断记录日志
        $this->setLog();
    }

    protected function view(\Exception $e, $is_sys=false){
        if (\Core\Env::isPhpUnit()) {//测试用例处理
            ob_start();
        }
        if($is_sys === true && !\Core\Env::isProductEnv()) {
            //开发环境系统级别异常使用页面提示错误信息
            $tpl_vars['code']     = $e->getCode();
            $tpl_vars['message']  = $e->getMessage();
            $tpl_vars['line']     = $e->getLine();
            $tpl_vars['file']     = $e->getFile();
            $tpl_vars['exception'] = $e;

            $this->_view->display(PHPLIB."/Base/View/DevError.phtml", $tpl_vars);
        }else{
            //生产环境或开发环境下业务流程中出现的异常(Validate Controller Service Data Dao)根据响应格式进行处理
            $retcode = $e->getCode();
            $msg = ($is_sys === false)?$e->getMessage():self::ERROR_MSG;

            if (Response::getFormatter() === Response::FORMAT_PLAIN) {
                Response::outPlain($msg);
            } elseif (Response::getFormatter() === Response::FORMAT_JSON) {
                Response::outJson($retcode, $msg);
            } else {
                if($this->isNotFound()){
                    //header404
                }else{
                    $tpl_vars['retcode'] = $retcode;
                    $tpl_vars['msg']     = $msg;
                }
                if($this->use_self_view){
                    $this->_view->display(APP_VIEW."/Error.phtml", $tpl_vars);
                }else{
                    $this->_view->display(PHPLIB."/Base/View/Error.phtml", $tpl_vars);
                }
            }
        }
		if (\Core\Env::isPhpUnit()) {//测试用例处理
            \Yaf\Registry::set('test_response', json_encode(array('retcode' => $e->getCode(), 'msg' => $e->getMessage())));
            ob_end_clean();
		}
    }

    /**
     * 将该log记录到对应的control里
     */
    public function setLog(){
        if($this->isNotFound()){
            return false;
        }
        \S\Log\Logger::getInstance()->info();
        return true;
    }

    /**
     * 判断错误是否为404错误
     * @return bool
     */
    public function isNotFound(){
        switch($this->getRequest()->getException()->getCode()){
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
}
<?php
namespace Base\Controller;

use S\Response;

/**
 * Class Common
 *
 * @package Base\Controller
 * @description 主模块控制器基类
 *              app/controllers目录下所有Controller的基类, 所有主模块中控制器均需要继承此类
 */
abstract class Common extends \Yaf\Controller_Abstract {
    /**
     * 全部可用的请求参数（已经过参数检查）
     * @var array
     */
    protected $params   = array();
    /**
     * 用于输出的信息 @see APP_Abstract::response()
     * @var array
     */
    protected $response = array();

	protected $responseFormatter = null;

    public function init(){
        if($this->responseFormatter !== null){
            Response::setFormatter($this->responseFormatter);
        }
    }

    /**
     * 参数校验
     *
     * 所有请求上送的参数必须在此方法中明确定义校验配置
     * 严禁在后续流程中手动调用 \S\Request::get()和post()方法获取参数
     *
     * @return array
     */
    abstract protected function params();

    /**
     * 业务流程入口
     *
     * @return mixed
     */
    abstract protected function action();
    protected function beforeParams(){}
    protected function beforeAction(){}

    /**
     * 实现默认action
     * 在此抛出的异常交给error处理
     * @return mixed
     */
    protected function indexAction(){
        $this->beforeParams();
        //参数检查
        $this->params = \S\Validate\Handler::check($this->params());
        //逻辑处理
        $this->beforeAction();
        $this->action();

        //输出
        if (\Core\Env::isPhpUnit()) {//测试用例处理
            ob_start();
        }
        if (Response::getFormatter() === Response::FORMAT_PLAIN) {
            Response::outPlain($this->response);
        } elseif (Response::getFormatter() === Response::FORMAT_JSON) {
            $this->displayJson($this->response);
        } else {
            $this->displayView($this->response);
        }

        if (\Core\Env::isPhpUnit()) {//测试用例处理
            \Yaf\Registry::set('test_response', json_encode($this->response));
            ob_end_clean();
        }

        //记录日志
        \S\Log\Logger::getInstance()->info();
    }

    /**
     * json输出模式
     * @param $data
     */
    public function displayJson($data){
        $common = \S\Config::confError('common.succ');
        Response::outJson($common['retcode'], $common['msg'], $data);
    }

    /**
     * 页面渲染输出
     * @param $tpl_vars
     * @return bool
     */
    public function displayView($tpl_vars){
        $ext = \Yaf\Application::app()->getConfig()->get('yaf.view.ext');
        $tpl_path = APP_VIEW ."/". str_replace('_', DIRECTORY_SEPARATOR, $this->getRequest()->controller).'.'.$ext;
        $this->_view->display($tpl_path, $tpl_vars);
    }
}

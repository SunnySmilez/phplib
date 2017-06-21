<?php
namespace Base\Controller;

use S\Response;

/**
 * Class Common
 *
 * @package     Base\Controller
 * @description 主模块控制器基类
 *              app/controllers目录下所有Controller的基类, 所有主模块中控制器均需要继承此类
 */
abstract class Common extends \Yaf\Controller_Abstract {
    /**
     * 全部可用的请求参数（已经过参数检查）
     *
     * @var array
     */
    protected $params = array();
    /**
     * 用于输出的信息 @see APP_Abstract::response()
     *
     * @var array
     */
    protected $response = array();

    protected $response_formatter = null;

    public function init() {
        if ($this->response_formatter !== null) {
            Response::setFormatter($this->response_formatter);
        }
    }

    protected function beforeParams() {
    }

    protected function beforeAction() {
    }

    /**
     * 实现默认action
     * 在此抛出的异常交给error处理
     *
     * @return mixed
     */
    protected function indexAction() {
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
            Response::displayPlain($this->response);
        } elseif (Response::getFormatter() === Response::FORMAT_JSON) {
            Response::displayJson($this->response);
        } else {
            Response::displayView($this->getView(), $this->response);
        }

        if (\Core\Env::isPhpUnit()) {//测试用例处理
            \Yaf\Registry::set('test_response', json_encode($this->response));
            ob_end_clean();
        }

        return true;
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

}

<?php
namespace Base\Controller;

use S\Response;

/**
 * Class Action
 * @package Base\Controller
 *
 * YAF默认路由模式
 * 参数检查使用直接过滤html和php标签
 * 启用yaf的自动渲染，可以使用setResponseFormat明确返回格式比如json和html或者无格式
 * 异常正常转发到error上
 */
abstract class Action extends \Yaf\Controller_Abstract {

    /**
     * 用于输出的信息 @see APP_Abstract::response()
     * @var array
     */
    protected $response = array();

    /**
     * controller层获取参数方法
     * @param $key
     * @param string $type
     * @return string
     */
    public function getParams($key, $type='request'){
        switch (strtolower($type)) {
            case 'request':
                $ret = strip_tags(\S\Request::request($key));
                break;
            case 'cookie':
                $ret = strip_tags(\S\Request::cookie($key));
                break;
            default:
                $ret = '';
        }
        return $ret;
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
        $tpl_path = APP_VIEW ."/". $this->_request->controller."/".$this->_request->action .'.'.$ext;
        $this->_view->display($tpl_path, $tpl_vars);
    }

    public function setResponseFormat($format){
        if(!in_array($format,array(Response::FORMAT_HTML,Response::FORMAT_JSON,Response::FORMAT_PLAIN))){
            throw new \S\Exception('response format error');
        }
        Response::setFormatter($format);
    }

    /**
     * 获取渲染完成后的视图，多用于弹窗html获取
     * @param $tpl
     * @param array $response
     * @return string
     */
    public function getRenderView($tpl, array $response = array()){
        \Yaf\Dispatcher::getInstance()->autoRender(false);
        $ret = $this->_view->render($tpl, $response);
        \Yaf\Dispatcher::getInstance()->autoRender(true);
        return $ret;
    }

    protected function render($tpl='', array $response=null) {
        if (Response::getFormatter() === Response::FORMAT_PLAIN) {
            Response::outPlain($this->response);
        } elseif (Response::getFormatter() === Response::FORMAT_JSON) {
            $this->displayJson($this->response);
        } else {
            $this->displayView($this->response);
        }
        \S\Log\Logger::getInstance()->info();
    }

}

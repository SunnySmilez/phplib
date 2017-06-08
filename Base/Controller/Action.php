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

    public function setResponseFormat($format){
        if(!in_array($format,array(Response::FORMAT_HTML,Response::FORMAT_JSON,Response::FORMAT_PLAIN))){
            throw new \S\Exception('response format error');
        }
        Response::setFormatter($format);
    }

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
     * 自动渲染视图
     *
     * @param string $tpl
     * @param array|null $response
     * @return bool
     */
    protected function render($tpl='', array $response=null) {
        if (Response::getFormatter() === Response::FORMAT_PLAIN) {
            Response::displayPlain($this->response);
        } elseif (Response::getFormatter() === Response::FORMAT_JSON) {
            Response::displayJson($this->response);
        } else {
            Response::displayView($this->getView(), $this->response);
        }
        return true;
    }

    /**
     * 获取渲染完成后的视图，多用于弹窗html获取
     * @param $tpl
     * @param array $response
     * @return string
     */
    public function getRenderView($tpl, array $response = array()){
        \Yaf\Dispatcher::getInstance()->autoRender(false);
        $ret = $this->getView()->render($tpl, $response);
        \Yaf\Dispatcher::getInstance()->autoRender(true);
        return $ret;
    }

}

<?php

/**
 * Class Controller_Api_Demo
 *
 * @description api控制器示范
 *
 *              备注:
 *              1. Controller本身只负责对数据结果的修饰和基本处理, 并对结果稍加处理响应给上层用户;
 *              2. Controller通常调用Service层, 不放置过多业务逻辑, 但倘若业务流程及其简单, 允许直接调用Data层并在本层添加少量业务逻辑;
 *              3. 所有请求上送的参数必须在 params() 方法中明确定义校验配置, 严禁直接过滤使用;
 */
class Controller_Api_Demo extends Controller_Api_Abstract {

    /**
     * 参数校验
     *
     * 所有请求上送的参数必须在此方法中明确定义校验配置
     * 严禁在后续流程中手动调用 \S\Request::get()和post()方法获取参数
     *
     * @return array
     */
    protected function params() {
        return array(
            'id' => array(
                'value'     => \S\Request::get('id'),
                'rule' => 'digit',
                'option' => array(
                    'digit_type' => 'unsigned_int_32',
                    'error' => 'validate.demo',
                ),
            ),
            'name' => array(
                'value'     => \S\Request::get('name'),
                'rule' => 'regx',
                'option' => array(
                    'regx' => '/^[a-zA-Z ]{1,30}$/',
                    'error' => 'validate.demo',
                ),
            ),
        );
    }

    /**
     * 业务流程入口
     *
     * @return mixed
     */
    protected function action() {
        $id = $this->params['id'];
        $name = $this->params['name'];

        $ret = (new \Service\Demo())->demoServiceLogic($id, $name);

        $this->response['data'] = $ret;
    }

}
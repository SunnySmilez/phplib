<?php
namespace Base\Controller;

/**
 * Class job
 *
 * @package Base\Controller
 * @description 任务控制器基类,用来在cli模式下执行
 */
abstract class Job extends \Yaf\Controller_Abstract {

    /**
     * 添加种种限制防止被外部调用
     * @throws \Yaf\Exception\LoadFailed\Controller
     */
    public function init(){
        //限制请求为本地cli
        if (php_sapi_name() != 'cli') {
            throw new \Yaf\Exception\LoadFailed\Controller('not find');
        }

        //请求体必须是Yaf_Request_Simple
    }

    /**
     * 参数校验
     *
     * @return array
     */
    public function getParams($key){

    }
}

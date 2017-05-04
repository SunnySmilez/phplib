<?php
namespace Modules\Wechat\Job;

use Modules\Wechat\Model\Data\Config as DataConfig;

/**
 * Class Config
 *
 * @package     Modules\Wechat\Job
 * @description 微信基础配置更新任务
 */
class Config extends \Base\Jobs\Job {

    const RETRY_TIMES = 3;  //失败重试次数
    /**
     * @var array wechat账号配置列表
     */
    protected $_configs;

    public function action($argv = array()) {
        $wechat_name = $argv ? $argv[0] : null;

        $this->init();

        //获取access_token必须放在第一位，作为其他接口调用的基础
        $this->updateAccessToken($wechat_name);
        $this->updateJSApiTicket($wechat_name);
    }

    protected function init() {
        $this->_configs = DataConfig::getBaseConfig();
    }

    /**
     * 更新access_token
     *
     * @param null $wechat_name
     *
     * @return bool
     */
    protected function updateAccessToken($wechat_name = null) {
        if ($wechat_name) {
            $wechat_name_list = array($wechat_name);
        } else {
            $wechat_name_list = array_keys($this->_configs);
        }

        foreach ($wechat_name_list as $wechat_name) {
            for ($count = 0; $count < self::RETRY_TIMES; $count++) {
                try {
                    $access_token = (new DataConfig())->updateAccessToken($wechat_name);
                    \S\Log\Logger::getInstance()->debug(array("wechat_name" => $wechat_name, "get_access_token_result" => ($access_token ? 'succ' : 'fail')));

                    if ($access_token) {
                        break;
                    }
                } catch (\Exception $e) {
                    \S\Log\Logger::getInstance()->error(array($wechat_name, $e->getCode(), $e->getMessage()));

                    continue;
                }
            }
        }

        return true;
    }

    /**
     * 更新jsapi_ticket
     *
     * @param null $wechat_name
     *
     * @return bool
     */
    protected function updateJSApiTicket($wechat_name = null) {
        if ($wechat_name) {
            $wechat_name_list = array($wechat_name);
        } else {
            $wechat_name_list = array_keys($this->_configs);
        }

        foreach ($wechat_name_list as $wechat_name) {
            for ($count = 0; $count < self::RETRY_TIMES; $count++) {
                try {
                    $js_api_ticket = (new DataConfig())->updateJsApiTicket($wechat_name);
                    \S\Log\Logger::getInstance()->debug(array("wechat_name" => $wechat_name, "get_js_api_ticket_result" => ($js_api_ticket ? 'succ' : 'fail')));

                    if ($js_api_ticket) {
                        break;
                    }
                } catch (\Exception $e) {
                    \S\Log\Logger::getInstance()->error(array($wechat_name, $e->getCode(), $e->getMessage()));

                    continue;
                }
            }
        }

        return true;
    }

}
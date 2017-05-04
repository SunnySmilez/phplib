<?php
namespace Modules\Wechat\Model\Dao\Cache;

/**
 * Class Config
 *
 * @package Modules\Wechat\Model\Dao\Cache
 *
 * @method setAccessToken($wechat_name, $access_token)
 * @method getAccessToken($wechat_name)
 *
 * @method setJsApiTicket($wechat_name, $js_api_ticket)
 * @method getJsApiTicket($wechat_name)
 */
class Config extends \Base\Dao\Cache {

    const DEFAULT_TTL = 7200;

    public function __construct() {
        $this->setConfig('AccessToken', 'WECHAT_ACCESS_TOKEN', self::DEFAULT_TTL);
        $this->setConfig('JsApiTicket', 'WECHAT_JS_API_TICKET', self::DEFAULT_TTL);
    }

}
<?php

/**
 * Class Controller_Api_Abstract
 * @description api控制器基类
 */
abstract class Controller_Api_Abstract extends \Base\Controller\Common {

    protected $responseFormatter = \S\Response::FORMAT_JSON;

}
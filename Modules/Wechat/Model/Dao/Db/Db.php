<?php
namespace Modules\Wechat\Model\Dao\Db;

/**
 * Class Db
 *
 * @package     Modules\Wechat\Model\Dao\Db
 * @description 微信Db基类
 */
class Db extends \Dao\Db {

    const TABLE_PREFIX = 'wechat_';

    protected $table;

}
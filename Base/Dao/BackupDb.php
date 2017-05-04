<?php

namespace Base\Dao;

/**
 * Class BackupDb
 *
 * @package     Base\Dao
 * @description 备库查询
 *
 *              写会使用主库
 *              读会使用备库
 *
 *              使用场景: 跑脚本，管理后台，统计后台
 */
class BackupDb extends Db {

    public static function getInstance($db_name) {
        if (!isset(self::$db[$db_name])) {
            self::$db[$db_name] = new \S\Db\Mysql($db_name);
            //读写分离
            self::$db[$db_name]->setModeFlag(true);
            //指定备库查询
            self::$db[$db_name]->setDbReadMode(\S\Db\Mysql::BACKUP);
        }

        return self::$db[$db_name];
    }

}
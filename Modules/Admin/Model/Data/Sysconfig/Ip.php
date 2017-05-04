<?php

namespace Modules\Admin\Model\Data\Sysconfig;

use Modules\Admin\Model\Dao\Db\Sysconfig\Ip as DbIp;

class Ip {

    const STATUS_VALID = 1;
    const STATUS_INVALID = 2;

    public function addWhiteList($ip, $description) {
        return (new DbIp())->add($ip, $description);
    }

    public function updateWhiteList($prev_ip, $ip, $description) {
        return (new DbIp())->update($prev_ip, $ip, $description);
    }

    public function updateStatus($ip, $status) {
        return (new DbIp())->updateStatus($ip, $status);
    }

    public function queryByIp($ip) {
        return (new DbIp())->queryByIp($ip);
    }

    public function isWhiteList($ip) {
        return (new DbIp())->queryByIp($ip) ? true : false;
    }

    public function del($ip) {
        return (new DbIp())->del($ip);
    }

    public function queryWhiteList($ip = null) {
        return (new DbIp())->queryDataTable($ip);
    }

}
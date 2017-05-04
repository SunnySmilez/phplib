<?php
namespace Modules\Admin\Controllers;

use Base\Exception\Controller as Exception;
use Modules\Admin\Model\Data\Sysconfig\Ip as DataIp;

/**
 * @name 系统设置
 */
class Sysconfig extends Base {

    protected $sys_view = true;

    /**
     * @name 系统配置
     */
    public function indexAction() {
        $tab_configs = array(
            array(
                'title' => 'IP白名单',
                'url' => APP_ADMIN_PATH . "/sysconfig/ipWhiteList",
            ),
            array(
                'title' => '访问日志',
                'url' => APP_ADMIN_PATH . "/sysconfig/visitLog",
            ),
        );

        $this->response['tab_configs'] = $tab_configs;
    }

    /**
     * @name IP白名单
     */
    public function ipWhiteListAction() {
        $this->setResponseFormat(\S\Response::FORMAT_PLAIN);
        $this->response = $this->getRenderView("");
    }

    public function editIpWhiteListAction() {
        $ip = $this->getParams('ip');
        $ip_info = array();
        if ($ip) {
            $ip_info = (new DataIp())->queryByIp($ip);
        }

        $this->setResponseFormat(\S\Response::FORMAT_PLAIN);
        $this->response = $this->getRenderView("", $ip_info);
    }

    public function isIpExistAction() {
        $ip = $this->getParams('ip');

        if ((new DataIp())->queryByIp($ip)) {
            throw new Exception("IP已存在");
        }
    }

    public function saveIpWhiteListAction() {
        $ip = $this->getParams('ip');
        $description = $this->getParams('description');
        $prev_ip = $this->getParams('prev_ip');

        if ($prev_ip) {
            (new DataIp())->updateWhiteList($prev_ip, $ip, $description);
        } else {
            (new DataIp())->addWhiteList($ip, $description);
        }
    }

    public function modifyIpStatusAction() {
        $ip = $this->getParams('ip');
        $ip_info = (new DataIp())->queryByIp($ip);

        (new DataIp())->updateStatus($ip, (DataIp::STATUS_VALID == $ip_info['status'] ? DataIp::STATUS_INVALID : DataIp::STATUS_VALID));
    }

    public function delIpWhiteListAction() {
        $ip = $this->getParams('ip');

        (new DataIp())->del($ip);
    }

    /**
     * 查询IP白名单
     */
    public function queryIpWhiteListAction() {
        $ip = $this->getParams('ip');

        $this->response = (new DataIp())->queryWhiteList($ip);
    }

    /**
     * @name 访问日志
     */
    public function visitLogAction() {
        $this->setResponseFormat(\S\Response::FORMAT_PLAIN);
        $this->response = $this->getRenderView("");
    }

    /**
     * 查询访问日志
     */
    public function queryVisitLogAction() {
        $uname     = $this->getParams('uname');
        $ctrl      = $this->getParams('ctrl');
        $time_from = $this->getParams('time_from');
        $time_to   = $this->getParams('time_to');

        $data = (new \Modules\Admin\Model\Data\Sysconfig\Visitlog())->getDataTable($uname, $ctrl, $time_from, $time_to);
        if (isset($data['data']) && !empty($data['data'])) {
            foreach ($data['data'] as $key => $val) {
                if ($val['uri'] == '/admin/login/check' || $val['uri'] == '/admin/login/login') {
                    $data['data'][$key]['request_info'] = json_encode(array());
                } else {
                    $data['data'][$key]['request_info'] = \Dao\Db::decrypt($val['request_info']);
                }
            }
        }

        $this->response = $data;
    }

}

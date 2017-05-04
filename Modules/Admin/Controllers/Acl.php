<?php
namespace Modules\Admin\Controllers;

use Modules\Admin\Model\Data\Acl as DataAcl;
use Base\Exception\Controller as Exception;
use Modules\Admin\Model\Data\User as DataUser;

/**
 * @name 访问控制管理
 */
class Acl extends Base {

    protected $sys_view = true;

    /**
     * 列出所有控制器和方法，用于权限配置
     *    包括：
     *        1. user        - acl    用户
     *        2. group    - acl    用户组
     *        3. all        - acl    全局访问（默认deny）
     *    user > group > all
     *    deny > allow
     *
     * @name 权限控制列表
     * @throws Exception
     */
    public function indexAction() {
        //uid|gid|id
        $permission = $this->_getAclConf();

        // 管理员或已冻结用户无法编辑权限
        if ($permission['type'] == DataAcl::ACL_UID) {
            $data = (new DataUser())->getUserInfoById($permission['id']);

            if ($data['isadmin'] == '1' || $data['status'] != '0') {
                throw new Exception("error.admin.can_not_be_modified");
            }
        }

        $data = (new DataAcl())->getAclDataByType($permission['type'], $permission['id']);

        $this->response['id']      = $permission['id'];
        $this->response['type']    = $permission['type'];
        $this->response['data']    = json_encode($data);
        $this->response['actions'] = DataAcl::getControllerFileList();
    }

    /**
     * @name 设置权限 （添加/修改acl）
     * @format json
     * @throws Exception
     */
    public function accessControlAction() {
        $data      = $this->getParams('data');
        $node_data = json_decode(stripcslashes($data), true);
        unset($data);
        if (!$node_data) {
            throw new Exception('error.admin.no_config_info');
        }

        $permission = $this->_getAclConf();
        $id_type    = $permission['type'];
        $id         = $permission['id'];
        //	处理由继承关系生成的全部访问数据
        $data_acl = new DataAcl();
        $odata    = (array)$data_acl->getAclDataByType($id_type, $id, true);

        //controller和AccessControl访问权限相同时，只记录controller的（不记录都为空的）
        //controller和AccessControl访问权限不全相同时，记录controller和不同于action的
        $add = $del = array();
        //对比已存在的和提交的对比
        foreach ($node_data as $key => $option) {
            //add
            if (!isset($odata[$key]) && $option !== '') {
                $add[] = $this->_getAclData($key, $option, $permission);
            }
            //del
            if (isset($odata[$key]) && $option === '') {
                $del[] = $this->_getAclData($key, $option, $permission);
            }
            //update
            if (isset($odata[$key]) && $option !== $odata[$key]) {
                if ($option !== '') {
                    $add[] = $this->_getAclData($key, $option, $permission);
                }
                $del[] = $this->_getAclData($key, $option, $permission);
            }
        }

        //	已经被删除的方法	-理论上应该全部相关方法的权限都删除，暂时只删被修改的
        $lost = array_diff(array_keys($odata), array_keys($node_data));
        if ($lost) {
            foreach ($lost as $lost_info) {
                $del[] = $this->_getAclData($lost_info, $odata[$lost_info], $permission);
            }
        }

        //更新内容
        if ($del) {
            $data_acl->manageAclInfo('del', $id_type, $del);
        }
        if ($add) {
            $data_acl->manageAclInfo('add', $id_type, $add);
        }

        $this->response['msg'] = '修改成功';
    }

    private function _getAclData($key, $option, $permission) {
        $tmp = explode('-', $key);
        //匹配正常类名和函数名
        if (empty($tmp[0]) || !$this->_checkName($tmp[0]) || (!empty($tmp[1]) && !$this->_checkName($tmp[1]))) {
            return array();
        }

        $ret = array(
            'controller'        => $tmp[0],
            'action'            => (string)$tmp[1],
            'option'            => $option,
            $permission['type'] => $permission['id'],
        );

        return $ret;
    }

    private function _checkName($name) {
        return preg_match('/[a-zA-Z0-9_]+/', $name);
    }

    /**
     * 获取权限配置
     *
     * @return array
     */
    private function _getAclConf() {
        if (isset($_REQUEST['uid'])) {            //用户权限配置
            $data = array(
                'type' => DataAcl::ACL_UID,
                'id'   => (int)$this->getParams('uid'),
            );
        } else if (isset($_REQUEST['gid'])) {    //用户组权限配置
            $data = array(
                'type' => DataAcl::ACL_GID,
                'id'   => (int)$this->getParams('gid'),
            );
        } else {                                //全部权限配置
            $data = array(
                'type' => 'id',
                'id'   => 0,
            );
        }

        return $data;
    }

}
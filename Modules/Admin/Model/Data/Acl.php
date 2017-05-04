<?php
namespace Modules\Admin\Model\Data;

use Modules\Admin\Model\Util\Scan as UtilScan;
use Modules\Admin\Model\Data\User as DataUser;
use Modules\Admin\Model\Dao\Db\Acl\Group as DbGroupAcl;
use Modules\Admin\Model\Dao\Db\Acl\User as DbUserAcl;
use Modules\Admin\Model\Dao\Db\Acl\All as DbAllAcl;
use Modules\Admin\Model\Dao\Db\User\Group as DbUserGroup;

/**
 * 权限管理
 */
class Acl {

    const ACL_UID = 'uid';
    const ACL_GID = 'gid';
    const ACL_ALL = 'all';
    const ACL_USER_GROUP = 'usergroup';

    const ACL_OPTION_ALLOW = 'allow';
    const ACL_OPTION_DENY = 'deny';

    public static $action_pattern = '/Action$/';
    public static $action_suffix = 'Action';
    public static $class_pattern = '/^Controller_/i';
    public static $class_prefix = 'Controller_';
    public static $use_autoload = false;

    private static $_no_login_controller = array('Login');

    /**
     * 添加不许要登录也可以访问的控制器
     *
     * @param array $controller
     *
     * @return bool
     */
    public static function setNoLoginController(array $controller) {
        self::$_no_login_controller = array_merge(self::$_no_login_controller, $controller);
    }

    public static function getNoLoginController() {
        return self::$_no_login_controller;
    }

    /**
     * 用户是否可以访问当前地址
     *
     * @param string $controller
     * @param string $action
     *
     * @return bool
     */
    public static function isAccess($controller, $action = null) {
        $acl        = \S\Request::session('actionlist');
        $controller = strtolower(self::$class_prefix . $controller);
        $action     = strtolower($action . self::$action_suffix);

        return $action === null ? isset($acl[$controller]['methods']['indexaction']) : isset($acl[$controller]['methods'][$action]);
    }

    /**
     * 获取控制器文件列表
     *
     * @return array
     */
    public static function getControllerFileList() {
        $dir = APP_PATH . "/modules/Admin/controllers";

        return UtilScan::classes($dir, self::_getActionRules());
    }

    /**
     * 获取用户可访问的acl列表
     *
     * @param int $uid
     *
     * @return array
     */
    public function getUserAclData($uid) {
        $user_info = (new DataUser())->getUserInfoById($uid);

        //被封用户
        if ($user_info['status'] != '0') {
            return array();
        }

        //controller - action 列表
        $all = self::_formatMethodName(self::getControllerFileList());

        //管理员
        if ($user_info['isadmin'] == '1') {
            return $all;
        }

        //普通用户
        $acl = $this->getAclDataByType(self::ACL_UID, $uid);

        //只保留allow
        $data = array();
        foreach ($acl as $key => $value) {
            $key         = strtolower($key);
            $action_list = array();
            if (empty($value['action'])) {
                continue;
            }

            foreach ($value['action'] as $action => $option) {
                $action = strtolower($action);
                //allow
                if (isset($all[$key]['methods'][$action]) && $option == self::ACL_OPTION_ALLOW) {
                    $action_list[$action] = $all[$key]['methods'][$action];
                }
            }

            //如果有可以访问的action，则写入数据
            if ($action_list) {
                $data[$key]            = $all[$key];
                $data[$key]['methods'] = $action_list;
            }
        }

        return $data;
    }

    /**
     * 获取用户|用户组|全局acl
     *
     * @param string  $type uid|gid|all
     * @param int     $id
     * @param boolean $kv
     *
     * @return array
     */
    public function getAclDataByType($type, $id, $kv = false) {
        //	获取全局的配置
        $data = (array)$this->_getRawAclData(self::ACL_ALL, null, $kv);

        //	获取用户组的配置
        if ($type === self::ACL_GID) {
            $group = $this->_getRawAclData($type, $id, $kv);
            //合并
            $data = self::_arrayMergeRecursiveDistinct($data, $group);
        }

        //	获取用户的配置
        if ($type === self::ACL_UID) {
            $groups = $this->_getRawAclData(self::ACL_USER_GROUP, $id, true);
            $user   = $this->_getRawAclData($type, $id, $kv);

            $data   = self::_arrayMergeRecursiveDistinct($data, (array)$groups, (array)$user);
        }

        return $data;
    }

    /**
     * 管理acl信息
     *
     * @param string $operation 操作 add-添加 del-删除
     * @param string $id_type   id类型 uid-用户 gid-组 id-全部
     * @param array  $info      acl信息
     *
     * @return bool
     */
    public function manageAclInfo($operation, $id_type, array $info) {
        $db_type_conf = array(
            self::ACL_UID => 'User',
            self::ACL_GID => 'Group',
            'id'          => 'All',
        );

        $class_name = '\\Modules\\Admin\\Model\\Dao\\Db\\Acl\\' . $db_type_conf[$id_type];
        $db_acl     = new $class_name();

        return call_user_func_array(array($db_acl, $operation), array($info));
    }

    /**
     * 获取acl表
     *
     * @param string  $type
     * @param int     $id
     * @param boolean $kv 以controller-action = option的形式返回
     *
     * @return array
     */
    private function _getRawAclData($type, $id = null, $kv = false) {
        if ($type === self::ACL_UID) {//uid
            // 获取用户所有权限信息
            $data = (new DbUserAcl())->getList($id);
        } else if ($type === self::ACL_GID) {//gid
            // 获取组所有权限信息
            $data = (new DbGroupAcl())->getList($id);
        } else if ($type === self::ACL_USER_GROUP) {//user all groups
            // 获取用户所属组所有权限
            $gid  = (new DbUserGroup())->getGidByUid($id);
            $data = array();
            if ($gid) {
                $data = (new DbGroupAcl())->getList($gid);
            }
        } else {
            // 获取全局权限权限
            $data = (new DbAllAcl())->getList();
        }

        return self::_formatAclData($data, $kv);
    }

    /**
     * 转换执行方法格式小写方法名+Action
     *
     * @param array $all
     *
     * @return array
     */
    private static function _formatMethodName($all) {
        $lower = array();
        foreach ($all as $key => $value) {
            $methods = array();
            foreach ($value['methods'] as $action_key => $action_value) {
                $methods[strtolower($action_key)] = $action_value;
            }
            $lower[strtolower($key)] = array('name' => $value['name'], 'methods' => $methods);
        }
        unset($all, $key, $value);

        return $lower;
    }

    /**
     * 获取 action 的规则
     *
     * @return array
     */
    private static function _getActionRules() {
        return array(
            'classes'      => self::$class_pattern,
            'class_prefix' => self::$class_prefix,
            'methods'      => self::$action_pattern,
            'use_autoload' => self::$use_autoload,
        );
    }

    /**
     * 格式化acl数组
     *
     * @param array $data
     * @param bool  $kv       以kay-value格式返回数据
     * @param bool  $override 出现重复是否覆盖（主要用于处理用户组权限冲突）
     *
     * @return array
     *    array(
     *        controller => array(
     *            'option' => 'allow',
     *            'action' => array(
     *                action => 'deny',
     *            )
     *        ),
     *    )
     *    [controller][option] = 'option'
     *    [controller]['action'][action] = 'option'
     */
    private static function _formatAclData($data, $kv = false, $override = false) {
        $result = array();
        if ($data) {
            foreach ($data as $v) {
                //操作权限
                $option = $v['option'] ? $v['option'] : self::ACL_OPTION_ALLOW;
                if ($kv) {
                    if ($v['action']) {
                        if (!($override && isset($result[$v['controller'] . '-' . $v['action']]) && $option !== self::ACL_OPTION_ALLOW)) {
                            $result[$v['controller'] . '-' . $v['action']] = $option;
                        }
                    } else {
                        if (!($override && isset($result[$v['controller']]) && $option !== self::ACL_OPTION_ALLOW)) {
                            $result[$v['controller']] = $option;
                        }
                    }
                } else {
                    if ($v['action']) {
                        //	allow 覆盖 deny（如果一个用户属于几个不同的用户组，只要一个组允许就允许，即使其他组设置为禁止）
                        if (!($override && isset($result[$v['controller']]['action'][$v['action']]) && $option !== self::ACL_OPTION_ALLOW)) {
                            $result[$v['controller']]['action'][$v['action']] = $option;
                        }
                    } else {
                        if (!($override && isset($result[$v['controller']]) && $option !== self::ACL_OPTION_ALLOW)) {
                            $result[$v['controller']]['option'] = $option;
                        }
                    }
                }

            }
        }

        return $result;
    }

    /**
     * 递归地合并一个或多个数组
     * 来自php手册array_merge_recursive的评论部分
     *
     * @return array
     */
    private static function _arrayMergeRecursiveDistinct() {
        $arrays = func_get_args();
        $base   = array_shift($arrays);
        if (!is_array($base)) {
            $base = empty($base) ? array() : array($base);
        }
        foreach ($arrays as $append) {
            if (!is_array($append))
                $append = array($append);
            foreach ($append as $key => $value) {
                if (!array_key_exists($key, $base) && !is_int($key)) {
                    $base[$key] = $append[$key];
                    continue;
                }
                if (is_array($value) or is_array($base[$key])) {
                    $base[$key] = self::_arrayMergeRecursiveDistinct($base[$key], $append[$key]);
                } else if (is_numeric($key)) {
                    if (!in_array($value, $base))
                        $base[] = $value;
                } else {
                    $base[$key] = $value;
                }
            }
        }

        return $base;
    }

}
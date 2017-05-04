<?php
namespace Modules\Admin\Model\Data;

use Modules\Admin\Model\Dao\Db\Menu\Main as DbMainMenu;
use Modules\Admin\Model\Dao\Db\Menu\Sub as DbSubMenu;

/**
 * 菜单管理
 */
class Menu {

    /**
     * 获取后台菜单
     *
     * @param bool $is_admin 是否管理员 true-管理员 false-普通用户
     *
     * @return array
     */
    public function getNavMenu($is_admin) {
        $menu_controller_list = $this->getMenuControlList();

        // 非管理员用户去除权限之外的菜单
        if (!$is_admin) {
            $acl = \S\Request::session('actionlist');
            foreach ($menu_controller_list as $id => $menu_controller) {
                $controller = strtolower($menu_controller['controller']);
                $action     = strtolower($menu_controller['action']);

                if (!isset($acl[$controller]['methods'][$action])) {
                    unset($menu_controller_list[$id]);
                }
            }
        }

        // 生成二级子菜单栏
        $sub_menu_list = array();
        foreach ($menu_controller_list as $sub_menu) {
            $sub_menu_list[$sub_menu['mid']][] = array(
                'name' => $sub_menu['uname'],
                'link' => APP_ADMIN_PATH . '/' . substr(strtolower($sub_menu['controller']), 11) . '/' . substr(strtolower($sub_menu['action']), 0, -6),
            );
        }

        // 二级菜单栏分组
        $main_menu_list = $this->getMainMenuList();

        $left_nav = array();
        foreach ($main_menu_list as $mid => $menu) {
            if (isset($sub_menu_list[$mid])) {
                $left_nav[$menu['mname']] = $sub_menu_list[$mid];
            }
        }

        return $left_nav;
    }

    /**
     * 获取所有菜单控制器信息
     *
     * @return array
     */
    public function getMenuControlList() {
        $menu_list = (new DbSubMenu())->getList();

        return $menu_list;
    }

    /**
     * 获取菜单控制数据列表
     *
     * @return array
     */
    public function getMenuControlAll() {
        $menu_list = $this->getMenuControlList();
        $menus     = array();
        foreach ($menu_list as $value) {
            $menus[$value['mid']][] = $value;
        }

        return $menus;
    }

    /**
     * 获取所有菜单信息
     *
     * @return array
     */
    public function getMainMenuList() {
        $menu_list = (new DbMainMenu())->getList();

        $result       = array();
        foreach ($menu_list as $menu) {
            $result[$menu['mid']] = $menu;
        }

        return $result;
    }

    /**
     * 获取一条菜单控制器信息
     *
     * @param integer $id 菜单id
     *
     * @return array
     */
    public function getMenuControllerById($id) {
        $menu_control_info = (new DbSubMenu())->getInfoById($id);

        return $menu_control_info;
    }

    /**
     * 根据id取菜单信息
     *
     * @param $id
     *
     * @return array
     */
    public function getMenuById($id) {
        $menu_info = (new DbMainMenu())->getInfoById($id);

        return $menu_info;
    }

    /**
     * 保存菜单信息
     *
     * @param      $info
     * @param null $id
     *
     * @return bool
     */
    public function saveMenuInfo($info, $id = null) {
        $ret = (new DbMainMenu())->save($info, $id);

        return $ret;
    }

    /**
     * 保存菜单控制器信息
     *
     * @param array        $info 菜单数组
     * @param integer|null $id   菜单id
     *
     * @return boolean
     */
    public function saveMenuControlInfo($info, $id = null) {
        $ret = (new DbSubMenu())->save($info, $id);

        return $ret;
    }

    /**
     * 删除菜单及子菜单信息
     *
     * @param int $mid 菜单id
     *
     * @return bool
     */
    public function delMainMenuById($mid) {
        (new DbMainMenu())->delById($mid);
        (new DbSubMenu())->delByMid($mid);

        return true;
    }

    /**
     * 删除子菜单信息(控制器)
     *
     * @param integer $id 菜单id
     *
     * @return boolean
     */
    public function delSubMenuById($id) {
        $ret = (new DbSubMenu())->delById($id);

        return $ret;
    }

}
<?php
namespace Modules\Admin\Model\Dao\Db\Menu;

/**
 * Class Sub
 *
 * @package Modules\Admin\Model\Dao\Db\Menu
 * @description 二级子菜单表
 */
class Sub extends \Modules\Admin\Model\Dao\Db\Db {

    private $table;

    public function __construct() {
        $this->table = self::table('menu_sub');
    }

    /**
     * 获取所有菜单控制器信息
     * @return array
     */
    public function getList() {
        return self::db()->query($this->table, array(), array(), array('order' => 'ASC'));
    }

    /**
     * 获取一条菜单控制器信息
     * @param integer $id 菜单id
     * @return array
     */
    public function getInfoById($id) {
        return self::db()->queryone($this->table, array('id' => $id));
    }

    /**
     * 保存菜单控制器信息
     * @param array        $info 菜单数组
     * @param integer|null $id 菜单id
     * @return boolean
     */
    public function save($info, $id = null) {
        if ($id) {
            return self::db()->update($this->table, $info, array('id' => $id));
        } else {
            return self::db()->insert($this->table, $info);
        }
    }

    /**
     * 删除子菜单信息
     * @param integer $id 菜单id
     * @return boolean
     */
    public function delById($id) {
        return self::db()->delete($this->table, array('id' => $id));
    }

    /**
     * 删除子菜单信息
     * @param integer $mid 菜单id
     * @return boolean
     */
    public function delByMid($mid) {
        return self::db()->delete($this->table, array('mid' => $mid));
    }
}
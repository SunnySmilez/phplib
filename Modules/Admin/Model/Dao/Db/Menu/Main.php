<?php
namespace Modules\Admin\Model\Dao\Db\Menu;

/**
 * Class Menu
 *
 * @package Modules\Admin\Model\Dao\Db\Menu
 * @description 一级主菜单表
 */
class Main extends \Modules\Admin\Model\Dao\Db\Db {

    private $table;

    public function __construct() {
        $this->table = self::table('menu_main');
    }

    /**
     * 获取所有菜单信息
     *
     * @return array
     */
    public function getList() {
        return self::db()->query($this->table, array(), array(), array('order' => 'ASC'));
    }

    /**
     * 获取一条菜单信息
     *
     * @param integer $id 菜单id
     *
     * @return array
     */
    public function getInfoById($id) {
        return self::db()->queryone($this->table, array('mid' => $id));
    }

    /**
     * 保存菜单信息
     *
     * @param array        $info 菜单数组
     * @param integer|null $id   菜单id
     *
     * @return boolean
     */
    public function save($info, $id = null) {
        if ($id) {
            return self::db()->update($this->table, $info, array('mid' => $id));
        } else {
            return self::db()->insert($this->table, $info);
        }
    }

    /**
     * 删除菜单及子菜单信息
     *
     * @param integer $id 菜单id
     *
     * @return boolean
     */
    public function delById($id) {
        return self::db()->delete($this->table, array('mid' => $id));
    }

}
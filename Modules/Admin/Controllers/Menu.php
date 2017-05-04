<?php
namespace Modules\Admin\Controllers;

use S\Response;
use Base\Exception\Controller as Exception;
use Modules\Admin\Model\Data\Menu as DataMenu;
use Modules\Admin\Model\Data\Acl as DataAcl;

/**
 * @name 访问控制管理
 */
class Menu extends Base {

    protected $sys_view = true;

	/**
	 * 列出所有控制器和方法，用于权限配置
	 *
	 * @name 菜单列表
	 * @format html
	 */
	public function indexAction() {
		$action_list	= DataAcl::getControllerFileList();
        $this->response['actions_json'] = json_encode((array)$action_list);

        $data_menu = new DataMenu();
        $this->response['menus'] = $data_menu->getMainMenuList();
        $this->response['control'] = $data_menu->getMenuControlAll();

		$id = intval(trim($this->getParams('id')));
		if ($id) {
            $this->response['menu'] = $data_menu->getMenuControllerById($id);
		}
	}

    /**
     * @name 编辑菜单（弹窗）
     *
     * @return void
     */
    public function editMenuPopAction() {
        $id = intval(trim($this->getParams('id')));

        $params = array();
        if($id) {
            $params = (new DataMenu())->getMenuById($id);
        }

        $this->setResponseFormat(Response::FORMAT_PLAIN);
        $this->response = $this->getRenderView("", $params);
    }

	/**
	 * @name 保存菜单
	 * @throws Exception
	 */
	public function saveMenuAction() {
        $id = $this->getParams('mid');
		$data = array(
			'mname'			=> trim($this->getParams('mname')),
			'description'	=> trim($this->getParams('description')),
			'order'			=> trim($this->getParams('order'))
		);
		if (!$data['mname']) {
            throw new Exception('error.admin.menu_is_empty');
		}
		if (!$data['order'] || !is_numeric($data['order'])) {
			throw new Exception('error.admin.order_is_empty');
		}

        (new DataMenu())->saveMenuInfo($data, $id);
        $this->response['msg'] = '保存成功';
		$this->updateSession();
	}

	/**
	 * @name 删除菜单及子菜单信息
	 * @throws Exception
	 */
	public function delMenuAction() {
        $id = intval($this->getParams('id'));
		if (!$id) {
			throw new Exception('error.admin.menu_id_error');
		}

        (new DataMenu())->delMainMenuById($id);
        $this->response['msg'] = '删除成功';
		$this->updateSession();
	}

	/**
	 * @name 保存菜单及控制器
	 * @throws Exception
	 */
	public function saveMenuControlAction() {
        $id = $this->getParams('id');
        
		$data = $this->getSaveData();
        (new DataMenu())->saveMenuControlInfo($data, $id);
		$this->updateSession();
	}

	/**
	 * @name 删除子菜单(控制器)
	 * @throws Exception
	 */
	public function delMenuControlAction() {
        $id = intval($this->getParams('id'));
		if (!$id) {
			throw new Exception('error.admin.child_menu_id');
		}

        (new DataMenu())->delSubMenuById($id);
		$this->updateSession();
        $this->response['msg'] = '删除成功';
	}

	/**
	 * @name 更新session
	 */
	private function updateSession() {
		$_SESSION['actionlist'] = (new DataAcl())->getUserAclData($_SESSION['uid']);
	}

	/**
	 * 获取保存菜单需要的数据
	 * @return array
	 * @throws Exception
	 */
	private function getSaveData() {
		$data = array(
			'mid'			=> $this->getParams('mid'),
            'uname'         => $this->getParams('uname'),
			'controller'	=> $this->getParams('controller'),
			'action'		=> $this->getParams('action'),
			'order'			=> $this->getParams('order'),
		);
		$data = array_map('trim',$data);

		if (empty($data['mid']) || !is_numeric($data['mid'])) {
			throw new Exception('error.admin.select_menu_id');
		}

		if (empty($data['uname'])) {
			throw new Exception('error.admin.select_menu');
		}

		if (empty($data['controller']) || !preg_match('/[0-9a-zA-Z_]+/', $data['controller'])) {
			throw new Exception('error.admin.controller_is_empty');
		}

		if (empty($data['action']) || !preg_match('/[0-9a-zA-Z_]+/', $data['action'])) {
			throw new Exception('error.admin.action_is_empty');
		}

		if (empty($data['order']) || !preg_match('/[0-9]{1,5}/', $data['order'])) {
			throw new Exception('error.admin.order_is_empty');
		}

		return $data;
	}
}

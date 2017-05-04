<?php
namespace Modules\Admin\Controllers;

use Modules\Admin\Model\Data\User as DataUser;
use Base\Exception\Controller as Exception;

/**
 * @name 用户组管理
 */
class Group extends Base {

    protected $sys_view = true;

    /**
     * @name 用户组管理
     */
    public function indexAction() {
    }

    public function queryGroupListAction() {
        $gname = $this->getParams('name');

        $this->response['data'] = (new DataUser())->getGroupList($gname);
    }

    /**
     * @name 编辑组 (弹窗)
     *
     * @return void
     */
    public function editPopAction() {
        $id        = intval(trim($this->getParams('gid')));

        $params    = array();
        if ($id) {
            $data_user = new DataUser();
            $params = $data_user->getGroupOneById($id);
        }

        $this->setResponseFormat(\S\Response::FORMAT_PLAIN);
        $this->response = $this->getRenderView("", $params);
    }

    /**
     * @name 用户成员列表
     */
    public function userListAction() {
        $gid = intval($this->getParams('gid'));

        $data_user = new DataUser();

        $members        = $data_user->getUserByGid($gid);
        $member_id_list = array_column($members, 'uid');

        $user_list = $data_user->getUserList();
        $user_list = array_filter($user_list, function ($item) use ($member_id_list) {
            return !in_array($item['uid'], $member_id_list);
        });

        $this->response['group']     = $data_user->getGroupOneById($gid);
        $this->response['member']    = $members;
        $this->response['user_list'] = $user_list;
    }

    /**
     * 添加成员
     *
     * @throws Exception
     */
    public function addMembersAction() {
        $gid      = \S\Request::post('gid');
        $add_uids = \S\Request::post('uids');

        if (!$gid) {
            throw new Exception('无效组id');
        }

        if ($add_uids) {
            $data_user = new DataUser();

            $curr_members     = $data_user->getUserByGid($gid);
            $curr_member_uids = array_column($curr_members, 'uid');

            $add_uids = array_diff($add_uids, $curr_member_uids);
            if ($add_uids) {
                foreach ($add_uids as $uid) {
                    $data_user->addUserGroupRel($gid, $uid);
                }
            }
        }
    }

    /**
     * 移除成员
     *
     * @throws Exception
     */
    public function delMemberAction() {
        $gid = \S\Request::post('gid');
        $uid = \S\Request::post('uid');

        if (!$gid) {
            throw new Exception('无效组id');
        }
        if (!$uid) {
            throw new Exception('请选择待移除用户id');
        }

        (new DataUser())->delUserGroupRel($gid, $uid);
    }

    /**
     * @name 删除用户组
     * @throws Exception
     */
    public function delAction() {
        $gid = intval($this->getParams('gid'));
        (new DataUser())->delGroupOneById($gid);
    }

    /**
     * @name 检查用户组是否存在
     * @throws Exception
     */
    public function existAction() {
        $name = $this->getParams('gname');
        if (!$name || addslashes($name) !== $name) {
            throw new Exception('error.admin.user_group_name_error');
        }

        $data_user = new DataUser();
        if ($data_user->isExistsByGroupName($name)) {
            throw new Exception('error.admin.user_group_exist');
        }
    }

    /**
     * @name 保存组信息
     * @throws Exception
     */
    public function saveGroupAction() {
        $gid         = intval($this->getParams('gid'));
        $name        = trim($this->getParams('gname'));
        $description = htmlspecialchars(trim($this->getParams('description')));

        if (!$name || $name != addslashes($name)) {
            throw new Exception('error.admin.user_group_name_error');
        }

        $data_user = new DataUser();
        if (!$gid && $data_user->isExistsByGroupName($name)) {
            throw new Exception('error.admin.user_group_exist');
        }
        $data_user->saveGroupInfo(array('gname' => $name, 'description' => $description), $gid);
    }

}

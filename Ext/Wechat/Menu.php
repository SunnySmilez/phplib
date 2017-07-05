<?php
namespace Wechat;

class Menu {

    const PATH_MENU_CREATE = 'menu/create';

    private $_access_token;
    public function __construct($access_token) {
        $this->_access_token = $access_token;
    }

    /**
     * 设置微信菜单
     *
     * @param $buttons
     * @return bool
     */
    public function setMenu(array $buttons) {
        $data = json_encode(array(
            'button' => $buttons
        ), JSON_UNESCAPED_UNICODE);

        Util::request(self::PATH_MENU_CREATE, $data, $this->_access_token, \S\Http::METHOD_POST);

        return true;
    }
}
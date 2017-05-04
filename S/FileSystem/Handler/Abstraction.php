<?php
namespace S\FileSystem\Handler;

use \S\Config;
use \S\Exception;

abstract class Abstraction implements StoreInterface {

    /**
     * 配置信息
     *
     * @var array
     */
    protected $config = array();

    public function __construct($type) {
        $this->config = Config::confServer('filesystem.' . $type);

        if (!$this->config) {
            throw new Exception('无法找到存储配置: ' . $type);
        }

        $this->init();
    }

    /**
     * 检查space名字是否设置
     *
     * @param $space
     *
     * @return bool
     * @throws Exception
     */
    protected function checkSpaceName($space) {
        $spaces = Config::confServer('filesystem.space');
        if (is_array($spaces) && in_array($space, $spaces)) {
            return true;
        } else {
            throw new Exception(get_class($this) . " space $space is not set");
        }
    }

    /**
     * 初始化工作
     *
     * @return bool true-成功 false-失败
     */
    public abstract function init();

}
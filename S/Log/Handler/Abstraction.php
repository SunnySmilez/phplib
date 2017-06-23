<?php
namespace S\Log\Handler;

abstract class Abstraction {

    public function handle($level, $message, $to_path) {
        $path = $this->getPath($level, $to_path);
        $this->write($path, $message);

        return true;
    }

    /**
     * 获取存储的关键key
     * 在文件存储里就是文件名
     *
     * @param $level
     * @param $to_path
     *
     * @return string
     */
    protected function getPath($level, $to_path) {
        $level = strtolower($level);

        if ($to_path) {
            $key = $to_path;
        } else {
            if (\Core\Env::isCli()) {
                $module = strtolower(\S\Request::server('argv', array())[1]);
                $cli_classname = strtolower(\S\Request::server('argv', array())[2]);
                $action = strtolower(\S\Request::server('argv', array())[3]);

                $key = ('index' == strtolower($module) ? '' : $module . '/') . str_replace('_', '/', $cli_classname) . '/' . $action;
            } else {
                $key = trim(\S\Request::server('PATH_INFO'), '/');
            }
        }
        $key .= "/" . date("Ym") . "/" . $level . "." . date("Ymd") . ".log";

        return $key;
    }

    /**
     * 写日志具体方法
     *
     * @param $key
     * @param $message
     *
     * @return mixed
     */
    abstract protected function write($key, $message);

}

<?php
namespace Modules\Admin\Model\Util;

use S\Exception;

class Scan {
    /**
     * 存储类和方法列表
     * @var array[]
     */
    protected static $list = array();

    /**
     * 按照 rule 遍历目录中的类和方法
     *
     * @todo 相同的dir，不同的rule的处理，速度很慢，需要做cache
     *
     * @param $dir
     * @param array $rule
     * @return array
     * @throws Exception
     */
    public static function classes($dir, array $rule=array(), $deeplimit=1) {
        $dir = self::filter($dir);

        $key = md5(implode('|', $dir));

        if (isset(self::$list[$key])) {
            return self::$list[$key];
        }

        self::$list[$key] = array();
        // 目录的文件列表
        $ls = array();
        foreach ($dir as $d) {
            if ($rule[Scan\Reflecation::USE_AUTOLOAD]) {
                $ls = array_merge($ls, Scan\File::filelist($d, $deeplimit));
            } else {
                $ls = array_merge($ls, Scan\File::filelist($d, $deeplimit, 0, '', '/'));
            }
        }
        foreach($ls as $v) {
            $tmp = Scan\Reflecation::classes($v, $rule);
            // 过滤非控制器
            if (is_array($tmp)) {
                self::$list[$key] += $tmp;
            }
        }

        return self::$list[$key];
    }

    /**
     * @param $dir
     * @return array
     * @throws Exception
     */
    private static function filter($dir) {
        $dirs = array();
        if (is_array($dir)) {
            foreach ($dir as $d) {
                if (!is_dir($d)) {
                    throw new Exception('Not a directory: '.$d, -1);
                }
                $dirs[] = $d;
            }
        } else {
            if (!is_dir($dir)) {
                throw new Exception('Not a directory: '.$dir, -1);
            }
            $dirs[] = $dir;
        }

        sort($dirs);

        return $dirs;
    }
}

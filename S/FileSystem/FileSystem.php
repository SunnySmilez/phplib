<?php
namespace S\FileSystem;

/**
 * 文件系统类
 *
 * 当文件是私有的，不希望对外公开 请使用filesystem类上传文件
 * 私有的文件无法通过url来访问访问，需要通过提供的get方法来获得文件内容
 *
 * <demo>
 *
 * //存储文件 将文件/tmp/1.png(绝对路径)存储在文件系统的space空间中 存储目录为test 存储的文件名为1.png
 * $url ＝ \S\FileSystem\FileSystem::getInstance()->put('space', 'test/1.png', '/tmp/1.png');
 *
 * @return           $url 文件的链接地址(如http://oss-cn-beijing.aliyuncs.com/test/1.png);
 *
 *
 * //获取文件 获取文件系统的space空间中 test目录下的1.png文件
 * $file ＝ \S\FileSystem\FileSystem::getInstance()->get('space', 'test/1.png');
 * @return           $file 文件内容
 *
 *
 * //删除文件 删除文件系统的space空间中 test目录下的1.png文件
 * $ret ＝ \S\FileSystem\FileSystem::getInstance()->delete('space', 'test/1.png');
 * @return           $ret bool 成功/失败
 * </demo>
 *
 * <code>
 *
 * 存储文件
 * $url = \S\FileSystem\FileSystem::getInstance()->put($space, $remote_file_name, $local_file_name);
 * @space            文件空间 即存储文件的目录名 对应云存储上的bucket名
 * @remote_file_name 存储在文件系统里的文件名 如有目录则采用'test/1.png'的格式
 * @local_file_name  上传的本地文件 使用绝对路径 ('/home/root/1.png')
 * return $url 文件的url地址(http://oss-cn-beijing.aliyuncs.com/space/test/1.png)或绝对路径(/tmp/filestorage/test/1.png)
 *
 * 获取文件
 * $file = \S\FileSystem\FileSystem::getInstance()->get($space, $remote_file_name);
 * @space            文件空间 即存储文件的目录名 对应云存储上的bucket名
 * @remote_file_name 存储在文件系统里的文件名 有目录则采用'test/1.png'的格式(可以使用通过put方法返回的$url来获取文件)
 * return $file 整个文件的数据
 *
 * 删除文件
 * $ret = \S\FileSystem\FileSystem::getInstance()->delete($space, $remote_file_name);
 * @space            文件空间 即存储文件的目录名 对应云存储上的bucket名
 * @remote_file_name 存储在文件系统里的文件名 有目录则采用'test/1.png'的格式(可以使用通过put方法返回的$url来删除文件)
 * return $ret bool(删除的文件不存在时,也会返回true)
 * </code>
 */
class FileSystem {

    const TYPE_AZURE = 'azure';
    const TYPE_LOCAL = 'local';
    const TYPE_OSS = 'oss';
    const DEFAULT_TYPE = self::TYPE_AZURE;

    /**
     * @var \S\FileSystem\Handler\Abstraction
     */
    private static $_handler;

    /**
     * 获取存储驱动实例
     *
     * @param string $type 存储类型 包括: azure|local|oss
     *                     注: 开发环境仅使用本地存储
     *
     * @return \S\FileSystem\Handler\Abstraction
     */
    public static function getInstance() {
        if (!self::$_handler) {
            $type           = self::getHandlerName();
            $handler        = __NAMESPACE__ . "\\Handler\\" . ucfirst($type);
            self::$_handler = new $handler($type);
        }

        return self::$_handler;
    }

    /**
     * 根据环境得到驱动名
     *
     * @return string
     */
    protected static function getHandlerName() {
        if (\Core\Env::isProductEnv()) {
            //判断服务商得到type
            //默认线上去驱动为azure
            $type = self::DEFAULT_TYPE;
        } else {
            // 开发环境使用本地存储
            $type = self::TYPE_LOCAL;
        }

        return $type;
    }

}
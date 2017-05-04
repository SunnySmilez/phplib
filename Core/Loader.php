<?php
/**
 * 解决除了框架外的加载问题
 */
namespace Core;

class Loader {
    public static $local_namespaces = array();

    public static function init(){
        $namespace_loader = new \Core\Loader\Namespaces();
        $classmap_loader = new \Core\Loader\Classmaps();

        //邮件
        $classmap_loader->addClassMap('PHPMailer', PHPLIB. "/Ext/PHPMailer/class.phpmailer.php");
        $classmap_loader->addClassMap('POP3', PHPLIB. "/Ext/PHPMailer/class.pop3.php");
        $classmap_loader->addClassMap('SMTP', PHPLIB. "/Ext/PHPMailer/class.smtp.php");
        $classmap_loader->addClassMap('phpmailerException', PHPLIB. "/Ext/PHPMailer/class.phpmailer.php");

        //phpword
        $namespace_loader->addNamespace('PhpOffice\\PhpWord\\', PHPLIB. "/Ext/PHPWord/");

        //Everyman (neo4j api)
        $namespace_loader->addNamespace('Everyman\\', PHPLIB. "/Ext/Everyman/");

        //phpexcel
        $classmap_loader->addClassMap('PHPExcel', PHPLIB. "/Ext/PHPExcel/PHPExcel.php");

        //二维码生成
        $classmap_loader->addClassMap('QRcode', PHPLIB. "/Ext/Qrcode/phpqrcode.php");

        //OSS
        $namespace_loader->addNamespace('OSS', PHPLIB. "/Ext/Aliyun/OSS");

        //MNS
        $namespace_loader->addNamespace('MNS', PHPLIB. "/Ext/Aliyun/MNS");

        //OTS
        $namespace_loader->addNamespace('OTS', PHPLIB. "/Ext/Aliyun/OTS/");

        //GuzzleHttp
        $namespace_loader->addNamespace('GuzzleHttp', PHPLIB. "/Ext/GuzzleHttp/");

        //Psr
        $namespace_loader->addNamespace('Psr', PHPLIB. "/Ext/Psr/");

        //Elastica
        $namespace_loader->addNamespace('Elastica', PHPLIB. "/Ext/Elastica/");

        //Thrift
        $namespace_loader->addNamespace('Thrift', PHPLIB. "/Ext/Thrift/");

        //Azure
        $namespace_loader->addNamespace('WindowsAzure', PHPLIB. "/Ext/Azure/src");

        //Wechat
        $namespace_loader->addNamespace('Wechat', PHPLIB. "/Ext/Wechat/");

        //register
        $classmap_loader->register();
        $namespace_loader->register();
    }

    /**
     * 注册当前类的autoload为自动载入方法
     *
     * @return boolean
     * @throws \S\Exception
     */
    public static function register_autoloader($local_namespaces) {
        self::$local_namespaces = $local_namespaces;
        if (!defined('APP_PATH')) {
            throw new \S\Exception('Please define APP_PATH for app dir');
        }
        return spl_autoload_register(array(__CLASS__, 'autoLoader'));
    }

    public static function autoLoader($class_name){
        // 验证类名的有效性
        if (trim($class_name, '_abcdefghijklmnopqrstuvwxyzQWERTYUIOPASDFGHJKLZXCVBNM123456789\\') !== '') {
            return false;
        }
        // 搜索路径中的类
        foreach(self::$local_namespaces as $local_namespace){
            $arr_namespace = explode('\\', $local_namespace);
            $last_namespace = array_pop($arr_namespace);
            $arr_classname = explode('\\', $class_name);
            $first_classname = array_shift($arr_classname);
            if($first_classname == $last_namespace) {
                $filename = str_replace('\\', DIRECTORY_SEPARATOR, $class_name) . '.php';
                $namespace = implode(DIRECTORY_SEPARATOR,$arr_namespace);
                $path = APP_PATH.DIRECTORY_SEPARATOR.lcfirst(trim($namespace.DIRECTORY_SEPARATOR.$filename,DIRECTORY_SEPARATOR));
                if (is_file($path)) {
                    // 包含app路径下的文件
                    require $path;
                } else if (is_file($filename)) {
                    // 处理include path
                    require $filename;
                }
                if (class_exists($class_name, false)) {
                    return true;
                }
            }
        }
        return false;
    }
}

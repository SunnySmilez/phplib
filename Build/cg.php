<?php
/**
 * 初始化脚手架
 */

//开始配置变量
if ($argc < 3) {
    die(<<<USAGE

*******************************************************************************************
Code Generator Version 3.0.0
*******************************************************************************************

使用说明:

1. 执行当前脚本需要root权限

2. 执行命令示例
   /usr/bin/php /phplib/Build/cg.php 项目名称 域名    模块列表(多个模块用英文逗号分割)
   /usr/bin/php /phplib/Build/cg.php demo    demo.com admin,wechat
   
3. 生成的项目代码目录位置 - 当前目录
   ./{项目名称}

*******************************************************************************************
\n
USAGE
    );
}
$app_name   = $argv[1];
$app_domain = $argv[2];
$app_path   = "./{$app_name}";

if (empty($argv[3])) {
    $modules = array();
} else {
    $modules = explode(',', $argv[3]);
}
define('INPUT_DIR', dirname(__FILE__) . '/templates');
define('MODULES_DIR', dirname(__FILE__) . '/modules');

if (file_exists($app_path)) {
    rename($app_path, $app_path . "." . date('YmdHis', time()));
}
$phplib_path  = dirname(dirname(dirname(__FILE__)));
$db_root_name = 'root';
$db_root_pwd  = 'Root1.pwd';  // MySQL5.7对于密码要求: 至少8个字符, 至少包括一个大写拉丁字符、一个小写拉丁字符、数字和特殊字符
//结束配置变量

//需要替换的值
$replace = array(
    "@appname@"      => $app_name,
    "@appdomain@"    => $app_domain,
    "@phplib_path@"  => $phplib_path,
    "@db_root_name@" => $db_root_name,
    "@db_root_pwd@"  => $db_root_pwd,
);

//写入文件
$arrFiles = getFilesInDir(INPUT_DIR);
foreach ($arrFiles as $file) {
    $app_file = str_replace(INPUT_DIR, $app_path, $file);
    if (!file_exists(dirname($app_file))) {
        mkdir(dirname($app_file), 0777, true);
    }
    $content = file_get_contents($file);
    foreach ($replace as $key => $value) {
        $content = str_replace($key, $value, $content);
    }
    file_put_contents($app_file, $content);
}
//cp docker nginx配置
file_put_contents("/etc/nginx/vhosts/" . $app_domain . ".conf", str_replace("@appdomain@", $app_domain, file_get_contents(dirname(__FILE__) . "/build/nginx/vhosts/template.conf")));
exec("service nginx reload");
//执行命令 创建数据库
exec("mysql -h127.0.0.1 -u{$db_root_name} -p{$db_root_pwd} < $app_path/build.sql");
//创建日志文件夹  改变日志文件夹及静态资源文件夹的权限
exec("mkdir -p /data1/logs/{$app_name} ;chmod 777 /data1/logs/{$app_name}");
exec("mkdir -m 0777 $app_path/public/static");
//shell目录下的脚本加上执行权限
exec("chmod -R 0777 $app_path/bin/");
exec("rm $app_path/build.sql");

//处理模块
foreach ($modules as $module) {
    $arrFiles = getFilesInDir(MODULES_DIR . "/" . $module);
    foreach ($arrFiles as $file) {
        $app_file = str_replace(MODULES_DIR . "/" . $module, $app_path, $file);
        if (!file_exists(dirname($app_file))) {
            mkdir(dirname($app_file), 0777, true);
        }
        $content = file_get_contents($file);
        foreach ($replace as $key => $value) {
            $content = str_replace($key, $value, $content);
        }
        file_put_contents($app_file, $content);
    }
    //执行程序和sql
    require MODULES_DIR . "/" . $module . "/install.php";
}

echo PHP_EOL . "项目生成完毕..." . PHP_EOL . PHP_EOL;

//获取所有的代码模板文件
function getFilesInDir($dir) {
    $filelist = array();
    $files    = scandir($dir);
    foreach ($files as $file) {
        if ($file == "." || $file == ".." || $file == '.svn') {
            continue;
        }
        if (is_dir($dir . '/' . $file)) {
            $filelist = array_merge(getFilesInDir($dir . '/' . $file), $filelist);
            continue;
        }
        if (is_file($dir . '/' . $file)) {
            $filelist[] = $dir . "/" . $file;
            continue;
        }
    }

    return $filelist;
}
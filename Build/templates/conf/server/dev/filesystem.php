<?php
/**
 * @service 存储服务提供方的配置
 */
return array(

    /**
     * azure
     */
    'azure' => array(
        'name' => 'DEMO_ID',
        'key' => 'DEMO_KEY',
        'hostname' => 'blob.core.chinacloudapi.cn',
    ),

    /**
     * 阿里云存储格式
     */
    'oss' => array(
        'access_id' => 'DEMO_ACCESS_ID',
        'access_key' => 'DEMO_ACCESS_KEY',
        'hostname' => 'oss-cn-beijing.aliyuncs.com',
    ),

    /**
     * 本地存储格式
     * @path 存储路径 请确保目录的权限为777
     */
    'local' => array(
        'path'      => '/tmp'
    ),

    /*
     * 放置文件的空间名称列表
     * 空间命名规范 建议使用 private-appname-suffix 的形式  suffix可任意
     */
    'space' => array(
        'private-company-demo',
    )
);
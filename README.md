# PHP框架

## 思路

web框架（如yaf）解决了请求流转和分发的问题，将请求生命周期进行抽象和约束，形成标准流程，使得处理过程更加顺滑并且合乎规范；与此同时，我们也期待将处理web流程的模式套用过来，按照类似的方式处理cli命令执行以及长进程，使得这一切尽量按照统一的标准运作。

> 可以快速开发部署的PHP业务框架，与整体研发架构相配合
> 
> [整体思路](think.md)

## 依赖

1. PHP7.1+

2. yaf/yac/redis/phpunit 稳定版

3. 编译安装 

   ```shell
   ./configure --prefix=/usr/local/php --with-mysqli=mysqlnd --with-pdo-mysql=mysqlnd --with-gd --with-jpeg-dir=/usr/lib64/ --with-iconv --with-openssl --with-curl --enable-pcntl --with-zlib --enable-bcmath --enable-json --enable-fpm --enable-mbstring --enable-soap --enable-opcache
   ```

## 使用

> 使用镜像 https://github.com/ifintech/rdbuild/tree/master/docker/php

#### 使用docker快速构建一个新的项目

> app名为demo 域名为demo.com 使用admin模块

```shell
docker run -itd --name demo -p 80:80 -v /home/phplib:/data1/htdocs/phplib -v /home/demo:/data1/htdocs/demo php
docker exec -it demo /usr/local/php/bin/php /data1/htdocs/phplib/Build/cg.php demo demo.com admin
```

访问

```shell
curl -v http://127.0.0.1/ -H 'Host:demo.com'
```

#### 使用docker快速构建一个已有项目


```shell
docker run -itd --name test -p 80:80 -v /home/phplib:/data1/htdocs/phplib -v /home/demo:/data1/htdocs/demo php
docker exec -it demo /data1/htdocs/demo/build/build.sh
```

访问

```shell
curl -v http://127.0.0.1/ -H 'Host:demo.com'
```



## 服务

假如容器是demo，项目名为demo，则运行

```shell
docker exec -it demo /data1/htdocs/demo/bin/service.sh start|stop|reload|status

介绍: 服务 启动|停止|重启|状态
用法: sh /data1/htdocs/demo/shell/service.sh [start|stop|reload|status]
```

1. 会启动或者重启 FPM
2. 会平滑启动或者重启长进程



## 其他





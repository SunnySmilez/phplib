# PHP框架

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



## 代码结构介绍

### Base  
#### 简介  
业务流程抽象基础组件，实现公共流程封装，将复杂的业务逻辑抽象分离，聚集具有共同特性的模块，实现低耦合高复用。
#### 结构
* Controller (控制器基类)   

| 类名 | 用途 | 使用场景 | 
| - | - | - |
| Action | 实现了yaf默认路由模式的控制器基类 | Admin模块的控制器基类 |
| Common  | 主模块控制器基类 | 主模块中app/controllers目录下所有控制器的基类 |
| Error | 程序中异常和错误的统一处理控制器基类 | 默认情况下直接继承，参考[Yaf的异常和错误](http://www.laruence.com/manual/yaf.catcherror.exception.html) |
| JsException | Js异常和错误的统一处理控制器基类 | 默认情况下直接继承，仅供前端调用，用来接收Js代码的异常信息并记录错误日志 |
  
* Dao (数据访问层基类)

| 类名 | 用途 | 使用场景 |
| - | - | - |
| BackupDb | 备库基类 | 需要使用备库读的场景，如Admin模块下的大部分查询和统计类操作，尽量避免对主业务场景的影响 |
| Cache | 缓存服务基类 | 针对命中率高或需要频繁访问的数据需要进行缓存 | 
| Db | 数据库访问层基类 | 关系型数据库的增删改查等操作，默认已开启读写分离 | 
| Ots | 阿里云Ots服务基类 |
| Queue | 队列服务基类 | 可用于：1. 解耦合 2. 削峰值，控制流量 |

* Exception (异常基类)

| 类名 | 用途 | 使用场景 |
| - | - | - |
| Abstraction | 业务异常基类 | Dao、Data、Service、Controller等异常的基类，默认情况下无需直接使用 |
| Controller | 控制器异常 | 仅在Controller中使用 | 
| Dao | 数据访问层异常 | 仅在Dao层使用 |
| Data | 数据逻辑层异常 | 仅在Data层使用 |
| Service | 业务逻辑层异常 | 仅在Service层使用 |

* Jobs (CLI任务基类)

| 类名 | 用途 | 使用场景 |
| - | - | - |
| MapReduce\\* | 并行任务处理 | 针对大数据量的任务进行拆分和结果归集 |
| Job | 普通cli命令基类 | 一次性任务或crontab定时任务 |

* Plugin (插件基类)

| 类名 | 用途 | 使用场景 |
| - | - | - |
| Base | yaf plugin基类，定义不同请求阶段的任务逻辑 | 参考[Yaf插件使用](http://www.laruence.com/manual/yaf.plugin.html) |

* Test (测试用例基类)

| 类名 | 用途 | 使用场景 |
| - | - | - |
| TestCase | 测试用例基类 | 参考[PHPUnit手册](https://phpunit.de/manual/current/zh_cn/writing-tests-for-phpunit.html) |

* View (通用视图)

| 文件名 | 用途 | 使用场景 |
| - | - | - |
| DevError.phtml | 开发环境错误视图 | 开发环境下非api调用或ajax请求的异常展示页面，包括错误码、错误信息、错误栈等异常详情，以便开发调试 |
| Error.phtml | 生产环境错误视图 | 生存环境下非api调用或ajax请求的错误页面，提供用户友好的错误提示信息 |

* Bootstrap.php  
  初始化引导程序，进行全局的初始化工作。参考[Yaf Bootstrap](http://www.laruence.com/manual/ch06s02.html)
* JobBootstrap.php  
  cli命令使用的初始化引导程序，继承Bootstrap.php

### Build  
#### 简介  
代码构建服务，用来快速构建项目，节约准备工作时间。包括：项目构建脚本、主模块结构模版、子模块（admin等）结构模版。
#### 结构
* modules (子模块构建模版)  
  包括
  * admin (管理后台构建模版)
  * wechat (微信模块构建模版)
* templates (主模块构建模版）
* cg.php (项目构建的入口文件)

### Core  
#### 简介  
核心环境加载服务，提供类、命名空间加载以及常用环境变量设置和获取等功能。此目录下的类通常在业务流程入口和请求初始化部分被大量使用，项目中通常用来进行环境判断和环境变量获取等功能。
#### 结构
* Loader\\*、Loader.php (环境加载)  
  包括类、命名空间以及Ext目录下三方扩展的自动加载服务，在Bootstrap中被调用，进行初始化加载工作。通常情况下无需调用。
* Env.php (环境变量定义和操作类）  
  包括：当前环境名称、模块名称、控制器名称等变量的设置和获取等操作

### Ext  
#### 简介  
外部扩展组件，集成常用的由服务提供方官方给出或其他个人封装的sdk，包括面向对象或面向过程等各种风格的代码。通常情况下除了少部分必要的针对命名空间的调整和bug修复，不进行额外改造，便于版本升级和维护。

此目录下的类和方法在Core\Loader的init()方法中加载。

#### 结构
* Aliyun (阿里云服务)  
  包括：
  * MNS (消息队列服务)
  * OSS (对象存储服务)
  * OTS (非关系型数据存储服务)
* Azure (微软云存储服务）
* Elastica
* Everyman
* GuzzleHttp (Http请求组件)
* Hbase
* PHPExcel (Excel组件)
* PHPMailer (邮件发送组件)
* PHPWord (Word生成组件)
* Psr
* Qrcode (通用二维码生成组件)  
* Thrift
* Wechat (微信组件)

### Modules  
#### 简介  
模块组件，包括：管理后台模块组件和微信模块组件。实现了有完整逻辑的独立代码模块共享，独立入口独立加载，但有逻辑或者数据层面的交互，形成高质量稳定代码，大幅度提升开发效率。

#### 结构
* Admin (管理后台模块)  
  包括以下功能：
  * 用户管理
  * 菜单管理
  * 权限管理
  * 系统配置
* Wechat (微信模块)  
  包括以下功能：
  * 微信推送消息处理
  * 基础配置加载和更新
  * Oauth认证

### S  
#### 简介  
基础工具类库，包括：日志记录、守护进程、配置读取、HTTP请求、锁、加解密服务等常用功能的封装，实现公司级别类库共享，提取常用工具组件，避免重复造轮子。

#### 结构
* Cache  
  缓存操作细节的封装，包括以下几种缓存类型：
  * Ipc (进程内缓存)
  * Memcache
  * Memcached
  * Redis
  * Yac (本地进程间缓存)
* Captcha  
  验证码服务，提供验证码的生成和校验功能。包括以下验证码类型：
  * Sms 短信验证码
  * Mail 邮件验证码
  * Image 图像验证码
* Crypt  
  加密服务，提供常用加解密功能。包括以下加密算法：
  * AES
  * DES
  * RSA
* Db  
  数据库操作的封装工具，对上层屏蔽数据库交互的细节，并提供常用操作的便捷接口。包括以下类型的数据库：
  * Mysql
  * Redis
  * Hbase
  * Ots
* FileSystem  
  文件系统服务，提供文件的上传、下载等常用操作。支持以下类型文件系统：
  * Azure 微软云文件系统
  * OSS 阿里云文件系统
  * Local 本地文件系统
* Log  
  日志记录工具，开发环境下记录到本地文件，生产环境下通过Rsyslog推送到日志服务器。支持以下级别的日志：
  * debug
  * info
  * warning
  * error
* Mq  
  消息队列，支持以下消息队列：
  * Redis
  * Ons 阿里云Ons消息队列服务
* Msg  
  消息服务，包括：
  * Sms 短信服务
  * Mail 邮件服务（SMTP协议）
* Office  
  微软Office工具，支持Word和Excel格式文件的生产和导出
* Queue  
  队列服务，支持以下队列：
  * Redis 基于redis的lpush和rpop实现
  * MNS 阿里云队列服务
* Security  
  安全相关工具，包括：
  * Api 接口安全工具，可防止消息篡改、重放攻击，并确保请求发起方的合法性
  * Freq 频率限制器
  * Mask 敏感信息脱敏工具，包括：银行账号、姓名、身份证号码、手机号码等
  * Refer Referer校验工具
  * Token 生成表单校验用的token，防止CSRF攻击
* Strace  
  * Debug 用于开发环境输出调试信息（Base\View\DevError.phtml中使用）
  * Timelog 资源调用时长跟踪，用于记录和分析远程资源（http redis mysql等）调用时间和情况
* Thread  
  守护进程创建和管理工具，可以方便地创建守护进程，常用于生产-消费者模型。
* Util  
  常用工具，包括：
  * Genuid id生成器
  * Rand 随机数生成器
  * Str 字符串处理工具
* Validate  
  参数校验工具，用于\Base\Controller\Commeon::params()中进行参数校验。包括以下类型：
  * card 银行卡
  * date 日期
  * digit 数字
  * email 邮箱
  * identify 身份证
  * in 集合校验，用于判断请求参数项是否在给定的值域内，推荐使用
  * ip IP地址
  * money 货币
  * phone 手机号码
  * regx 正则表达式校验，用于判断请求参数项是否匹配给定正则表达式，推荐使用
  * Str 字符串
  * url url路径
* Config.php  
  配置获取工具，可用来获取conf目录下配置文件中的配置
* Exception.php  
  公共类库异常，只在S目录中的服务中使用，通常代表不可恢复的严重错误，例如：数据库连接失败、http错误、加密密钥未配置等异常
* Http.php  
  Http请求工具
* Lock.php  
  锁，包括阻塞锁和非阻塞锁
* Request.php  
  请求处理工具，包括获取请求参数和环境变量、获取和判断请求方式以及获取请求地址等常用方法
* Response.php  
  响应处理工具，包括响应报文格式化(json、文件)等常用方法

## 其他





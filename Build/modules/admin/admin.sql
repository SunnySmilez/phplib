drop table if exists admin_acl_all;
create table if not exists admin_acl_all (
  `controller` varchar(64) not null                                                       comment '控制器',
  `action`     varchar(64) not null                                                       comment '方法',
  `ctime`      timestamp   not null default current_timestamp                             comment '记录创建时间',
  `mtime`      timestamp   not null default current_timestamp on update current_timestamp comment '记录最近修改时间'

) engine=innodb default charset=utf8mb4 comment '默认权限表';

drop table if exists admin_acl_group;
create table if not exists admin_acl_group (
  `gid`        int unsigned not null,
  `controller` varchar(64)  not null                                                       comment '控制器',
  `action`     varchar(64)  not null                                                       comment '方法',
  `option`     varchar(10)  not null                                                       comment '操作 allow-允许 deny-拒绝',
  `ctime`      timestamp    not null default current_timestamp                             comment '记录创建时间',
  `mtime`      timestamp    not null default current_timestamp on update current_timestamp comment '记录最近修改时间'

) engine=innodb default charset=utf8mb4 comment '组权限表';

drop table if exists admin_user_groups;
create table if not exists admin_user_groups (
  `gid`         int unsigned not null auto_increment                                        comment '组id',
  `gname`       varchar(64)  not null                                                       comment '组名',
  `description` varchar(255) not null default ''                                            comment '备注',
  `ctime`       timestamp    not null default current_timestamp                             comment '记录创建时间',
  `mtime`       timestamp    not null default current_timestamp on update current_timestamp comment '记录最近修改时间',

  primary key (`gid`)
) engine=innodb default charset=utf8mb4 comment '组表';

drop table if exists admin_menu_main;
create table if not exists admin_menu_main (
  `mid`         int unsigned not null auto_increment                                        comment '菜单id',
  `mname`       varchar(64)  not null                                                       comment '菜单名称',
  `description` varchar(255) not null default ''                                            comment '备注',
  `order`       tinyint      not null                                                       comment '顺序',
  `ctime`       timestamp    not null default current_timestamp                             comment '记录创建时间',
  `mtime`       timestamp    not null default current_timestamp on update current_timestamp comment '记录最近修改时间',

  primary key (`mid`)
) engine=innodb default charset=utf8mb4 comment '菜单表';

drop table if exists admin_menu_sub;
create table if not exists admin_menu_sub (
  `id`         int         unsigned not null auto_increment                               comment '子菜单id',
  `mid`        int         unsigned not null                                              comment '一级菜单id',
  `uname`      varchar(64) not null                                                       comment '子菜单名称',
  `controller` varchar(64) not null                                                       comment '控制器类名',
  `action`     varchar(64) not null                                                       comment '控制器下的action方法',
  `order`      tinyint     not null default 0                                             comment '顺序',
  `ctime`      timestamp   not null default current_timestamp                             comment '记录创建时间',
  `mtime`      timestamp   not null default current_timestamp on update current_timestamp comment '记录最近修改时间',

  primary key (`id`),
  key `idx_mid` (`mid`)
) engine=innodb default charset=utf8mb4 comment '二级子菜单表';

drop table if exists admin_user_info;
create table if not exists admin_user_info (
  `uid`         int unsigned not null auto_increment                                        comment '用户id',
  `uname`       varchar(64)  not null                                                       comment '用户登录名',
  `nick`        varchar(64)  not null default ''                                            comment '昵称',
  `password`    varchar(40)  not null default ''                                            comment '登录密码',
  `salt`        varchar(40)  not null default ''                                            comment '登录密码对应盐值, 用于计算hash',
  `phone`       varchar(20)  not null default ''                                            comment '手机号码',
  `email`       varchar(50)  not null default ''                                            comment '电子邮箱',
  `description` varchar(255) not null default ''                                            comment '描述',
  `isadmin`     tinyint      not null default 0                                             comment '是否管理员 0-否 1-是',
  `status`      tinyint      not null default 0                                             comment '状态 0-生效 1-禁用',
  `otp_secret`  char(16)     not null default ''                                            comment 'opt二步认证密钥',
  `ctime`       timestamp    not null default current_timestamp                             comment '记录创建时间',
  `mtime`       timestamp    not null default current_timestamp on update current_timestamp comment '记录最近修改时间',

  primary key (uid),
  unique uniq_uname(uname)
) engine=innodb default charset=utf8mb4 comment '管理后台用户信息表';

drop table if exists admin_acl_user;
create table if not exists admin_acl_user (
  `uid`        int unsigned not null                                                       comment '用户id',
  `controller` varchar(64)  not null                                                       comment '控制器类名',
  `action`     varchar(64)  not null                                                       comment '控制器下的action方法',
  `option`     varchar(10)  not null                                                       comment '是否允许访问 allow-允许 deny-不允许',
  `ctime`      timestamp    not null default current_timestamp                             comment '记录创建时间',
  `mtime`      timestamp    not null default current_timestamp on update current_timestamp comment '记录最近修改时间'

) engine=innodb default charset=utf8mb4 comment '用户权限表';

drop table if exists admin_user_group;
create table if not exists admin_user_group (
  `uid`        int unsigned not null                                                       comment '用户id',
  `gid`        int unsigned not null                                                       comment '组id',
  `ctime`      timestamp    not null default current_timestamp                             comment '记录创建时间',
  `mtime`      timestamp    not null default current_timestamp on update current_timestamp comment '记录最近修改时间',

  unique key uniq_uid_gid (`uid`, `gid`)
) engine=innodb default charset=utf8mb4 comment '组成员表';

drop table if exists admin_sysconfig_visitlog;
create table if not exists admin_sysconfig_visitlog (
  `id`           int unsigned not null auto_increment                                        comment '记录id',
  `uri`          varchar(64)  not null default ''                                            comment '请求URI',
  `uname`        varchar(64)  not null default ''                                            comment '管理员uname',
  `session_info` text         not null                                                       comment '当前的session数据, json格式',
  `server_info`  text         not null                                                       comment '$_SERVER信息, json格式',
  `request_info` text         not null                                                       comment '请求信息 加密存储',
  `ctime`        timestamp    not null default current_timestamp                             comment '记录创建时间',
  `mtime`        timestamp    not null default current_timestamp on update current_timestamp comment '记录最近修改时间',

  PRIMARY KEY (id),
  key `idx_ctime_uname_uri` (`ctime`, `uname`, `uri`)
) engine=innodb default charset=utf8mb4 comment '访问日志';

drop table if exists admin_sysconfig_ip;
create table if not exists admin_sysconfig_ip (
  `id`          int unsigned not null auto_increment                                        comment '记录id',
  `ip`          varchar(16)  not null                                                       comment 'ip地址',
  `description` text         not null                                                       comment '描述',
  `status`      tinyint      not null default 1                                             comment '状态 1-生效 2-禁用',
  `ctime`       timestamp    not null default current_timestamp                             comment '记录创建时间',
  `mtime`       timestamp    not null default current_timestamp on update current_timestamp comment '记录最近修改时间',

  primary key (`id`),
  unique uniq_ip(ip)
) engine=innodb default charset=utf8mb4 comment 'IP白名单表';

insert into `admin_user_info` (`uid`, `uname`, `nick`, `password`, `salt`, `phone`, `description`, `isadmin`, `status`) values
  (1, 'admin', '管理员', 'e788155ae2ad3c9016fece4f7218f324a4158246', '54cae36200d2b', '', '', 1, 0);

insert into `admin_menu_main` (`mid`, `mname`, `description`, `order`) values
  (1, '系统管理', '系统管理', 101),
  (2, '示例菜单', '示例菜单', 102);

insert into `admin_menu_sub` (mid, `uname`, `controller`, `action`, `order`) values
  (1, '用户管理',       'Controller_User',      'indexAction',         1),
  (1, '用户组管理',     'Controller_Group',     'indexAction',         2),
  (1, '权限管理',       'Controller_Acl',       'indexAction',         3),
  (1, '菜单管理',       'Controller_Menu',      'indexAction',         4),
  (1, '系统配置',       'Controller_Sysconfig', 'indexAction',      5),

  (2, 'dataTable示例', 'Controller_Demo',     'dataTableIndexAction', 1),
  (2, '图表示范',       'Controller_Demo',     'chartIndexAction',     2);

insert into `admin_user_groups` (`gid`, `gname`, `description`) values
  (1, '普通', '普通用户组, 权限包括: 登录、登出、编辑用户信息');
insert into `admin_acl_group` (`gid`, `controller`, `action`, `option`) values
  (1, 'Controller_Login', '', 'allow'),
  (1, 'Controller_Login', 'indexAction', 'allow'),
  (1, 'Controller_Login', 'loginAction', 'allow'),
  (1, 'Controller_Login', 'logoutAction', 'allow'),
  (1, 'Controller_User', 'editUserInfoAction', 'allow'),
  (1, 'Controller_User', 'checkNameExistAction', 'allow'),
  (1, 'Controller_User', 'saveUserInfoAction', 'allow'),
  (1, 'Controller_Error', '', 'allow'),
  (1, 'Controller_Error', 'errorAction', 'allow');

insert into `admin_acl_all` (`controller`, `action`) values
  ('controller_login',   ''),
  ('controller_login',   'checkaction'),
  ('controller_login',   'indexaction'),
  ('controller_login',   'logoutaction'),
  ('controller_welcome', ''),
  ('controller_welcome', 'indexaction');

#ip白名单
INSERT INTO `admin_sysconfig_ip` (`ip`, `description`) VALUES ('124.65.192.206', '公司ip地址');
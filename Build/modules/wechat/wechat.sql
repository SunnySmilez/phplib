drop table if exists wechat_log;
create table if not exists wechat_log(
  id      int unsigned not null auto_increment                                        comment '日志记录id',
  appid   varchar(28)  not null                                                       comment '服务号appid',
  openid  char(28)     not null                                                       comment '用户openid',
  msgtype varchar(10)  not null                                                       comment '消息类型',
  detail  varchar(300) not null                                                       comment '消息内容，json格式存储',
  ctime   timestamp    not null default current_timestamp                             comment '记录创建时间',
  mtime   timestamp    not null default current_timestamp on update current_timestamp comment '记录最近修改时间',

  primary key(id),
  index idx_appid_openid(appid,openid)
) engine=innodb default charset=utf8mb4 comment='微信推送消息日志表';

drop table if exists wechat_user;
create table if not exists wechat_user (
  id             int unsigned     not null auto_increment                                        comment '自增id',
  appid          varchar(28)      not null                                                       comment '服务号appid',
  openid         char(28)         not null                                                       comment '用户openid',
  unionid        char(32)         not null default ''                                            comment '用户的UnionID：在所有绑定到一起的official_account中唯一。',
  subscribe      tinyint unsigned not null default 0                                             comment '用户是否订阅该公众号标识：值为0时，代表此用户没有关注该公众号，拉取不到其余信息。',
  subscribe_time timestamp        not null                                                       comment '用户关注时间的时间戳：如果用户曾多次关注，则取最近一次关注时间。',
  groupid        int unsigned     not null default 0                                             comment '用户所在的分组ID',
  sex            tinyint unsigned not null default 0                                             comment '用户的性别：值为1时是男性，值为2时是女性，值为0时是未知。',
  language       char(5)          not null default 'zh_CN'                                       comment '用户的语言：简体中文为zh_CN，繁体中文为zh_TW，英国话为en。',
  nickname       varchar(45)      not null default ''                                            comment '用户的昵称',
  city           varchar(10)      not null default ''                                            comment '用户所在城市',
  province       varchar(10)      not null default ''                                            comment '用户所在省份',
  country        varchar(10)      not null default ''                                            comment '用户所在国家',
  headimgurl     varchar(500)     not null default ''                                            comment '用户头像：最后一个数值代表正方形头像大小（有0、46、64、96、132数值可选，0代表640*640正方形头像），用户没有头像时该项为空。若用户更换头像，原有头像URL将失效。',
  remark         varchar(300)     not null default ''                                            comment '公众号运营者对粉丝的备注：公众号运营者可在微信公众平台用户管理界面对粉丝添加备注。',
  privilege      varchar(300)     not null default '[]'                                          comment '用户特权信息，json 数组，如微信沃卡用户为（chinaunicom）',
  tagid_list     varchar(300)     not null default '[]'                                          comment '用户被打上的标签ID列表',
  ctime          timestamp        not null default current_timestamp                             comment '记录创建时间',
  mtime          timestamp        not null default current_timestamp on update current_timestamp comment '记录最近修改时间',

  primary key(id),
  unique uniq_appid_openid(appid,openid)
) engine=innodb default charset=utf8mb4 comment='微信公众号用户表';
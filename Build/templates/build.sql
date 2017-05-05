CREATE DATABASE IF NOT EXISTS `@appname@` DEFAULT CHARSET utf8mb4;
USE `@appname@`;
CREATE TABLE IF NOT EXISTS `@appname@_demo` (
  `id` int unsigned not null auto_increment,
  `name` varchar(32) not null,
  primary key(`id`)
) engine=innoDB charset=utf8mb4;
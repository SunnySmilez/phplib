############################################################
# 创建rdcenter
# Based on php
############################################################
# Set the base image to centos6.8
FROM php

# File Author / Maintainer
MAINTAINER lvyalin lvyalin.yl@gmail.com

# 配置nginx
ADD build/nginx/vhosts/@appname@.conf /etc/nginx/vhosts/@appname@.conf

# 初始化数据结构
ADD build/build.sql /tmp/build.sql
RUN mysql -h127.0.0.1 -uroot -pRoot1.pwd < /tmp/build.sql

# 创建日志文件夹  改变日志文件夹
RUN mkdir -p /data1/logs/@appname@ && \
 chmod 777 /data1/logs/@appname@

CMD ["/etc/rc.local"]
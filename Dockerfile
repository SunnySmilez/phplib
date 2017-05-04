############################################################
# 创建开发环境的dockerfile
# Based on Centos 7
############################################################
# Set the base image to Ubuntu
FROM daocloud.io/centos:7
# File Author / Maintainer
MAINTAINER zhangce zhangce5413@gmail.com
##################### 常用基础软件 START #####################
RUN yum install -y ftp vim wget crontabs gcc make openssh-server git && \
 ssh-keygen -q -N "" -t rsa -f /etc/ssh/ssh_host_rsa_key && \
 ssh-keygen -q -t ecdsa -f /etc/ssh/ssh_host_ecdsa_key -N '' && \
 ssh-keygen -t dsa -f /etc/ssh/ssh_host_ed25519_key  -N '' && \
 echo "root:Root1.pwd" | chpasswd
##################### 常用基础软件 END #####################

###################### redis START #####################
ADD conf/redis/redis /etc/init.d/redis
RUN chmod +x /etc/init.d/redis && \
 yum -y install epel-release && \
 yum -y install redis && \
 sed -i "s/daemonize[ ]*no/daemonize yes/g" /etc/redis.conf
###################### redis END #####################

###################### mysql START #####################
RUN rpm -Uvh https://dev.mysql.com/get/mysql57-community-release-el6-9.noarch.rpm && \
 yum -y install mysql-community-server
###################### mysql END #####################

################## openresty start ######################
###安装依赖
ADD conf/nginx/nginx /etc/init.d/nginx
RUN yum -y install readline-devel pcre-devel openssl-devel && \
### 安装软件
 yum-config-manager --add-repo https://openresty.org/yum/cn/centos/OpenResty.repo && \
 yum -y install openresty openresty-resty perl-Digest-MD5 && \
### 添加用户组
 /usr/sbin/groupadd -f www && \
 /usr/sbin/useradd -g www www && \
### 创建文件夹
 mkdir -p /var/run/nginx && \
 mkdir -p /data2/logs/nginx/ && \
### 添加配置软连接
 ln -s /usr/local/openresty/nginx/conf /etc/nginx && \
 mkdir /etc/nginx/vhosts && \
 chmod +x /etc/init.d/nginx
###添加配置
ADD conf/nginx/* /etc/nginx/
ADD conf/nginx/vhosts/* /etc/nginx/vhosts/
##################### openresty END #####################

###################### php7 START #####################
RUN cd /usr/src && \
 yum -y install libxml2-devel curl-devel libpng-devel autoconf && \
 wget http://cn2.php.net/get/php-7.1.2.tar.xz/from/this/mirror && \
 mv mirror php-7.1.2.tar.xz && \
 xz -d php-7.1.2.tar.xz && tar xvf php-7.1.2.tar && \
 cd php-7.1.2 && \
 ./configure --prefix=/usr/local/php --with-mysqli=mysqlnd --with-pdo-mysql=mysqlnd --with-gd  --with-iconv --with-openssl --with-curl --enable-pcntl --enable-bcmath --enable-json --enable-fpm --enable-mbstring --enable-soap --enable-opcache && \
 make && make install && \
 cp /usr/src/php-7.1.2/sapi/fpm/init.d.php-fpm /etc/init.d/php-fpm && \
 chmod +x /etc/init.d/php-fpm && \
 mkdir -p /data2/logs/php && \
 rm -rf /usr/src/php-7.1.2.tar && \
 echo -e 'export PATH=/usr/local/php/bin:usr/local/php/sbin:$PATH' >> ~/.bashrc && \
 source ~/.bashrc
 ###安装扩展
 ###yaf
 RUN source ~/.bashrc && \
 cd /tmp/ && \
 wget http://pecl.php.net/get/yaf-3.0.4.tgz && \
 tar zxvf yaf-3.0.4.tgz && \
 cd yaf-3.0.4 && phpize && \
 ./configure && make && make install && \
 ### yac
 cd /tmp/ && \
 wget http://pecl.php.net/get/yac-2.0.1.tgz && \
 tar zxvf yac-2.0.1.tgz && \
 cd yac-2.0.1 && phpize && \
 ./configure && make && make install && \
 ###redis
 cd /tmp/ && \
 wget http://pecl.php.net/get/redis-3.1.1.tgz && \
 tar zxvf redis-3.1.1.tgz && \
 cd redis-3.1.1 && phpize && \
 ./configure && make && make install && \
 cd / && rm -rf /tmp/*
ADD conf/php/php.ini /usr/local/php/lib/
ADD conf/php/php-fpm.conf /usr/local/php/etc/
ADD conf/php/www.conf /usr/local/php/etc/php-fpm.d/
###################### php7 END #####################

ADD rc.local /etc/rc.local
RUN chmod +x /etc/rc.local
CMD ["/etc/rc.local"]
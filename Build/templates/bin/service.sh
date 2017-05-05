#!/bin/bash
#############################################################################
# 使用帮助
if [ "-h" = "$1" ] || [ "--help" = "$1" ] || [ -z $1 ]
then
    echo
    echo "介绍: 任务管理 启动|停止|重启|状态"
    echo "用法: sh /data1/htdocs/loans/bin/service.sh [start|stop|reload|status]"
    exit
fi

if [ "start" = "$1" ]
then
    #启动daemon
    nohup /usr/bin/php /data1/htdocs/loans/jobs/job.php Jobs_Daemon_Master >> /tmp/nohup.loans.Daemon.log 2>&1 &
    #启动PHP
    /usr/local/php/sbin/php-fpm

    echo "succ"
fi

if [ "stop" = "$1" ]
then
    #回收daemon
    kill `cat /var/run/PHP_THREAD_MASTER_PID.loans`
    while true; do
        sleep 3
        if [[ `cat /var/run/PHP_THREAD_MASTER_PID.loans` = "" ]];
        then
            break
        fi
    done
    #停止PHP
    kill `cat /usr/local/php/var/run/php-fpm.pid`

    echo "succ"
fi

if [ "reload" = "$1" ]
then
    #重启daemon
    nohup /usr/bin/php /data1/htdocs/loans/jobs/job.php Jobs_Daemon_Master >> /tmp/nohup.loans.Daemon.log 2>&1 &
    #重启phpfpm
    kill -URS2 `cat /usr/local/php/var/run/php-fpm.pid`

    echo "succ"
fi

#返回running or stopped
if [ "status" = "$1" ]
then
    if [ `cat /usr/local/php/var/run/php-fpm.pid` ]
    then
        echo "running"
    fi
fi

<?php
namespace Jobs\Job\Wechat;

/**
 * 此脚本用于为微信服务号更新基本配置，包括：
 *
 *   更新access token
 *   更新jsapi_ticket
 *
 * 安装crontab示例：
 * 0 * * * * /usr/bin/php /data1/htdocs/@appname@/jobs/job.php Jobs_Job_Wechat_Config 2>&1 >> /dev/nul
 */
class Config extends \Modules\Wechat\Job\Config {}
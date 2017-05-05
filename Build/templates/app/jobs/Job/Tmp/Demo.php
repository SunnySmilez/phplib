<?php
namespace Jobs\Job\Tmp;
/**
 * Class Demo
 *
 * @package     Jobs\Job\Tmp
 * @description 临时脚本示范
 *
 *              备注:
 *              1. 开发环境执行命令示范(生产环境请使用sa系统操作): /usr/bin/php /data1/htdocs/demo.com/jobs/job.php Jobs_Job_Tmp_Demo;
 *              2. 注意内存分配情况;
 *              3. 关键信息添加日志记录或输出, 以追踪脚本执行情况;
 */
class Demo extends \Base\Jobs\Job {

    public function action($argv = array()) {
        var_dump('Bonjour, le monde!');
    }

}
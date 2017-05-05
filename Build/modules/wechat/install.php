<?php
$execs =  array(
    "mysql -h127.0.0.1 -u{$db_root_name} -p{$db_root_pwd} {$app_name} < {$app_path}/wechat.sql",
    "rm ".$app_path."/wechat.sql",
    "rm ".$app_path."/install.php",
);
foreach($execs as $exec){
    exec($exec);
}
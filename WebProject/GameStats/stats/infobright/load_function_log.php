<?php
/**
 * Created by PhpStorm.
 * User: shushenglin
 * Date: 16/3/25
 * Time: 10:42
 */

defined('IB_ROOT') || define('IB_ROOT', __DIR__);
require_once IB_ROOT.'/ib.inc.php';

define('MODULE',basename(__FILE__, '.php'));

write_pid_file(MODULE);

$log_file_base = '/data/applog/function/';

$stat_date = $_REQUEST['stat_date'];
if(empty($stat_date)){
    $stat_time = strtotime("-1 day");
}else{
    $stat_time = strtotime($stat_date);
}


if(!empty($_REQUEST['log_file'])){
    $log_file = $_REQUEST['log_file'];
    if($log_file[0] != '/'){
        $log_file = $log_file_base . '/' . $log_file;
    }
}else{
    $log_file = $log_file_base . 'function-' . date('Y-m-d',$stat_time) . '_00000';
}

$table_suffix = $_REQUEST['table'];
if(empty($table_suffix)){
    $table_suffix = date('Ym',$stat_time);
}

$table = 'function_log_' . $table_suffix;
$database = 'coklog_function';
$dump_config = array();
$dump_config['import_fields'] = 'server_id,userid,timeStamp,date,category,type,int_data1,int_data2,int_data3,int_data4,int_data5,int_data6,var_data1,var_data2,var_data3,var_data4,var_data5,var_data6';

$table_exist = check_stat_table_exist($database, $table);
echo "check table exist $database:$table=$table_exist   ",PHP_EOL;
if($table_exist === null){
    writeRunLog("check table exist for $database $table fail, abort run script");
    remove_pid_file(MODULE);
    return;
}
if($table_exist === false){
    // create the table
    $table_sql = "CREATE TABLE IF NOT EXISTS `$table` (
  `server_id` smallint(4) NOT NULL,
  `userid` bigint(20) NOT NULL,
  `timeStamp` bigint(20) NOT NULL,
  `date` date NOT NULL,
  `category` smallint(5) NOT NULL,
  `type` smallint(11) NOT NULL DEFAULT '0',
  `int_data1` int(11) DEFAULT NULL,
  `int_data2` int(11) DEFAULT NULL,
  `int_data3` int(11) DEFAULT NULL,
  `int_data4` int(11) DEFAULT NULL,
  `int_data5` int(11) DEFAULT NULL,
  `int_data6` int(11) DEFAULT NULL,
  `var_data1` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `var_data2` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `var_data3` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `var_data4` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `var_data5` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `var_data6` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=BRIGHTHOUSE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
    $db_info = $stats_db;
    $db_info['dbname'] = $database;
    $re = query_game_db($db_info,$table_sql);
    if($re == false){
        writeRunLog("create table $table fail");
    }
}

writeRunLog("start to load data for table:$table log file: $log_file");
$cmd = build_mysql_cmd(
    'stats_db',
    vsprintf("LOAD DATA LOCAL INFILE '%s' INTO TABLE %s CHARACTER SET utf8 FIELDS TERMINATED BY ',' LINES TERMINATED BY '\\n' (%s)", array(
        $log_file,
        $database . ".$table",
        $dump_config['import_fields']
    ))
);
$re = system($cmd, $ret_val);
writeRunLog('execute command:'.$cmd.', result:'.$ret_val);
if($re === false || $ret_val == 1){
    writeRunLog("run script $cmd fail");
}
writeRunLog("finished load data for table:$table");

remove_pid_file(MODULE);

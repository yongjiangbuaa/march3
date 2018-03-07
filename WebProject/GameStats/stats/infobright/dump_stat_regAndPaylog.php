<?php
// php dump_db.php sid=1
defined('IB_ROOT') || define('IB_ROOT', __DIR__);
require_once IB_ROOT.'/ib.inc.php';

function get_last_record_time($file_name, $time_col_num, $field_delimiter='<>'){
	$line = `tail -n1 $file_name`;
	$k = explode($field_delimiter, $line);
	return $k[$time_col_num - 1];
}
ini_set('memory_limit', '2048M');

define('MODULE',basename(__FILE__, '.php'));
if(!write_pid_file(MODULE)){
	return;
}

$stat_cron_start = time();

//$last_processed = get_process_maxtime('paylog', IB_DB_NAME_SNAPSHOT); //跑到中间断开,导致再次查询没有paylog don't exit
//$start_date_ts = $last_processed;
$end_date_ts = time()*1000;
include_once IB_ROOT.'/load/dump_db_paylog.php';
writeRunLog("================finish cron dump of dump_db_paylog '".SERVER_ID."' ===============");

//$last_processed = get_process_maxtime('stat_reg', IB_DB_NAME_SNAPSHOT);
//$start_date_ts = $last_processed;
$end_date_ts = time()*1000;
include_once IB_ROOT.'/load/dump_db_stat_reg.php';

writeRunLog("================ended cron dump of stat_regAndPaylog '".SERVER_ID."', takes ".(time() - $stat_cron_start)."===============");
remove_pid_file(MODULE);

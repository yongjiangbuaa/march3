<?php
// php dump_db.php sid=1
defined('IB_ROOT') || define('IB_ROOT', __DIR__);
require_once IB_ROOT.'/ib.inc.php';

function get_last_record_time($file_name, $time_col_num, $field_delimiter='<>'){
	$line = `tail -n1 $file_name`;
	$k = explode($field_delimiter, $line);
	return $k[$time_col_num - 1];
}

define('MODULE',basename(__FILE__, '.php'));
if(!write_pid_file(MODULE)){
    return;
}

$stat_cron_start = time();
writeRunLog("================start cron dump of '".SERVER_ID."' ===============");

//$last_processed = get_process_maxtime('hot_goods_cost_record', IB_DB_NAME_SNAPSHOT,'buyTime');
//$start_date_ts = $last_processed;
//$end_date_ts = time()*1000;
//include_once IB_ROOT.'/load/dump_db_hot_goods_cost_record.php';

$last_processed = get_process_maxtime('stat_phone', IB_DB_NAME_SNAPSHOT);
$start_date_ts = $last_processed;
$end_date_ts = time()*1000;
include_once IB_ROOT.'/load/dump_db_stat_phone.php';

$last_processed = get_process_maxtime('logrecord', IB_DB_NAME_SNAPSHOT,'timeStamp');
$start_date_ts = $last_processed;
$end_date_ts = time()*1000;
include_once IB_ROOT.'/load/dump_db_logrecord.php';

$last_processed = get_process_maxtime('logrecord_alliance', IB_DB_NAME_SNAPSHOT,'timeStamp');
$start_date_ts = $last_processed;
$end_date_ts = time()*1000;
include_once IB_ROOT.'/load/dump_db_logrecord_alliance.php';

$last_processed = get_process_maxtime('hot_info_before_refresh', IB_DB_NAME_SNAPSHOT,'refreshTime');
$start_date_ts = $last_processed;
$end_date_ts = time()*1000;
include_once IB_ROOT.'/load/dump_db_hot_info_before_refresh.php';

writeRunLog("================ended cron dump of '".SERVER_ID."', takes ".(time() - $stat_cron_start)."===============");
remove_pid_file(MODULE);

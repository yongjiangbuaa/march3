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

// default: export yestoday data
// $start_date = empty($_REQUEST['date']) ? date('Y-m-d',strtotime('-1 day')) : $_REQUEST['date'];
// $start_date_ts = strtotime($start_date);

// $end_date = empty($_REQUEST['date_end']) ? $start_date : $_REQUEST['date_end'];
// $end_date_ts = strtotime($end_date) + 86400;

// $start_date_ts *= 1000;
// $end_date_ts *= 1000;

$stat_cron_start = time();
writeRunLog("================start cron dump of '".SERVER_ID."' ===============");

$last_processed = get_process_maxtime('stat_login', IB_DB_NAME_SNAPSHOT);
$start_date_ts = $last_processed;
$end_date_ts = time()*1000;
include_once IB_ROOT.'/load/dump_db_stat_login.php';

$last_processed = get_process_maxtime('stat_login_full', IB_DB_NAME_SNAPSHOT);
$start_date_ts = $last_processed;
$end_date_ts = time()*1000;
include_once IB_ROOT.'/load/dump_db_stat_login_month.php';

//$last_processed = get_process_maxtime('stat_reg', IB_DB_NAME_SNAPSHOT);
//$start_date_ts = $last_processed;
//$end_date_ts = time()*1000;
//include_once IB_ROOT.'/load/dump_db_stat_reg.php';

//$last_processed = get_process_maxtime('userprofile2', IB_DB_NAME_SNAPSHOT,'lastOnlineTime');
//$start_date_ts = $last_processed;
//$end_date_ts = time()*1000;
//include_once IB_ROOT.'/load/dump_db_user_userprofile2.php';


//$last_processed = get_process_maxtime('paylog', IB_DB_NAME_SNAPSHOT);
//$start_date_ts = $last_processed;
//$end_date_ts = time()*1000;
//include_once IB_ROOT.'/load/dump_db_paylog.php';

// $last_processed = get_process_maxtime('gold_cost_record', IB_DB_NAME_SNAPSHOT);
// $start_date_ts = $last_processed;
// $end_date_ts = time()*1000;
// include_once IB_ROOT.'/load/dump_db_gold_cost_record.php';

$last_processed = get_process_maxtime('gold_cost_record_full', IB_DB_NAME_SNAPSHOT);
$start_date_ts = $last_processed;
$end_date_ts = time()*1000;
include_once IB_ROOT.'/load/dump_db_gold_cost_record_full.php';

writeRunLog("================ended cron dump of '".SERVER_ID."', takes ".(time() - $stat_cron_start)."===============");
remove_pid_file(MODULE);

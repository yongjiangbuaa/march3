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
writeRunLog("================start cron dump userprofile of '".SERVER_ID."' ===============");

$last_processed = get_process_maxtime('userprofile', IB_DB_NAME_SNAPSHOT,'regTime');
$start_date_ts = $last_processed;
$end_date_ts = time()*1000;
include_once IB_ROOT.'/load/dump_db_user_userprofile.php';

include_once IB_ROOT.'/load/dump_db_user_userprofile_full.php';

include_once IB_ROOT.'/load/dump_db_user_reg.php';

//include_once IB_ROOT.'/load/dump_db_account_new.php';//global库里

writeRunLog("================ended cron dump userprofile of '".SERVER_ID."', takes ".(time() - $stat_cron_start)."===============");
remove_pid_file(MODULE);
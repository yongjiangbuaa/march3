<?php
// php dump_db.php sid=1
defined('IB_ROOT') || define('IB_ROOT', __DIR__);
require_once IB_ROOT.'/ib.inc.php';

function get_last_record_time($file_name, $time_col_num, $field_delimiter='<>'){
	$line = `tail -n1 $file_name`;
	$k = explode($field_delimiter, $line);
	return $k[$time_col_num - 1];
}

// default: export yestoday data
// $start_date = empty($_REQUEST['date']) ? date('Y-m-d',strtotime('-1 day')) : $_REQUEST['date'];
// $start_date_ts = strtotime($start_date);

// $end_date = empty($_REQUEST['date_end']) ? $start_date : $_REQUEST['date_end'];
// $end_date_ts = strtotime($end_date) + 86400;

// $start_date_ts *= 1000;
// $end_date_ts *= 1000;


//writeRunLog("================start cron stat of 'cokdb_global' ===============");

/*test start*/
$stat_cron_start = time();
writeRunLog("================start cron dump of '".SERVER_ID."' ===============");


//$last_processed = get_process_maxtime('userprofile2', IB_DB_NAME_SNAPSHOT,'lastOnlineTime');
//$start_date_ts = $last_processed;
//$end_date_ts = time()*1000;
//include_once IB_ROOT.'/load/dump_db_user_userprofile2.php';

//include_once IB_ROOT.'/load/dump_db_user_userprofile_full.php';

//include_once IB_ROOT.'/load/dump_db_user_reg.php';

//$last_processed = get_process_maxtime('stat_tutorial', IB_DB_NAME_SNAPSHOT);
//$start_date_ts = $last_processed;
//$end_date_ts = time()*1000;
//include_once IB_ROOT.'/load/dump_db_stat_tutorial.php';

//$last_processed = get_process_maxtime('userprofile', IB_DB_NAME_SNAPSHOT,'regTime');
//$start_date_ts = $last_processed;
//$end_date_ts = time()*1000;
//include_once IB_ROOT.'/load/dump_db_user_userprofile.php';

//$last_processed = get_process_maxtime('stat_login', IB_DB_NAME_SNAPSHOT);
//$start_date_ts = $last_processed;
//$end_date_ts = time()*1000;
//include_once IB_ROOT.'/load/dump_db_stat_login.php';

//$last_processed = get_process_maxtime('stat_login_full', IB_DB_NAME_SNAPSHOT);
//$start_date_ts = $last_processed;
//$end_date_ts = time()*1000;
//include_once IB_ROOT.'/load/dump_db_stat_login_month.php';

//$last_processed = get_process_maxtime('stat_reg', IB_DB_NAME_SNAPSHOT);
//$start_date_ts = $last_processed;
//$end_date_ts = time()*1000;
//include_once IB_ROOT.'/load/dump_db_stat_reg.php';

//$last_processed = get_process_maxtime('stat_phone', IB_DB_NAME_SNAPSHOT,'buyTime');
//$start_date_ts = $last_processed;
//$end_date_ts = time()*1000;
//include_once IB_ROOT.'/load/dump_db_stat_phone.php';

//$last_processed = get_process_maxtime('logrecord', IB_DB_NAME_SNAPSHOT,'timeStamp');
//$start_date_ts = $last_processed;
//$end_date_ts = time()*1000;
//include_once IB_ROOT.'/load/dump_db_logrecord.php';

//$last_processed = get_process_maxtime('logrecord_alliance', IB_DB_NAME_SNAPSHOT,'timeStamp');
//$start_date_ts = $last_processed;
//$end_date_ts = time()*1000;
//include_once IB_ROOT.'/load/dump_db_logrecord_alliance.php';

//$last_processed = get_process_maxtime('hot_info_before_refresh', IB_DB_NAME_SNAPSHOT,'refreshTime');
//$start_date_ts = $last_processed;
//$end_date_ts = time()*1000;
//include_once IB_ROOT.'/load/dump_db_hot_info_before_refresh.php';

//$last_processed = get_process_maxtime('sign_in_feed', 'snapshot_global');
//$start_date_ts = $last_processed;
//$end_date_ts = time()*1000;
//include_once IB_ROOT.'/load/dump_db_global_sign_in_feed.php';

//$last_processed = get_process_maxtime('user_14day_login', 'IB_DB_NAME_SNAPSHOT','lastRewardTime');
//$start_date_ts = $last_processed;
//$end_date_ts = time()*1000;
//include_once IB_ROOT.'/load/dump_db_user_14day_login.php';

//$last_processed = get_process_maxtime('hot_goods_cost_record', IB_DB_NAME_SNAPSHOT,'buyTime');
//$start_date_ts = $last_processed;
//$end_date_ts = time()*1000;
//include_once IB_ROOT.'/load/dump_db_hot_goods_cost_record.php';

//$last_processed = get_process_maxtime('paylog', IB_DB_NAME_SNAPSHOT);
//$start_date_ts = $last_processed;
//$end_date_ts = time()*1000;
include_once IB_ROOT.'/load/dump_db_paylog.php';
/*test end*/

// $last_processed = get_process_maxtime('stat_reg', IB_DB_NAME_SNAPSHOT);
// $start_date_ts = $last_processed;
// $end_date_ts = time()*1000;
// include_once IB_ROOT.'/load/dump_db_stat_reg.php';

//include_once IB_ROOT.'/load/dump_db_account_new.php';

//$last_processed = get_process_maxtime('activation', 'snapshot_global');
//$start_date_ts = $last_processed;
//$end_date_ts = time()*1000;
//include_once IB_ROOT.'/load/dump_db_activation.php';



//$last_processed = get_process_maxtime('paylog', IB_DB_NAME_SNAPSHOT);
//$start_date_ts = $last_processed;
//$end_date_ts = time()*1000;
//include_once IB_ROOT.'/load/dump_db_paylog.php';


//$last_processed = get_process_maxtime('goods_cost_record', IB_DB_NAME_SNAPSHOT);
//$start_date_ts = $last_processed;
//$end_date_ts = time()*1000;
//include_once IB_ROOT.'/load/dump_db_goods_cost_record.php';

//$last_processed = get_process_maxtime('logrecord_war', IB_DB_NAME_SNAPSHOT,'timeStamp');
//$start_date_ts = $last_processed;
//$end_date_ts = time()*1000;
//include_once IB_ROOT.'/load/dump_db_logrecord_war.php';

//writeRunLog("================ended cron stat of 'cokdb_global', takes ".(time() - $stat_cron_start)."===============");

writeRunLog("================ended cron stat of '".SERVER_ID."', takes ".(time() - $stat_cron_start)."===============");

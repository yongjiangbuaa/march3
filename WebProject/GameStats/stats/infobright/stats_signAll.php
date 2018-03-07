<?php
defined('IB_ROOT') || define('IB_ROOT', __DIR__);
require_once IB_ROOT.'/ib.inc.php';

function buildUpdateSql($kv){
	$all = array();
	foreach ($kv as $key => $value) {
		$all[] = "$key=$value";
	}
	return implode(',', $all);
}
define('MODULE',basename(__FILE__, '.php'));
if(!write_pid_file(MODULE)){
	return;
}

$stat_cron_start = time();
writeRunLog("================start cron stat_sign_allServer of '".SERVER_ID."' ===============");

//writeRunLog("stat_sign_allServer");//每日签到,cok才有
//include IB_ROOT.'/stat/stat_sign_allServer.php';

//writeRunLog("stat_roi_pf_country_v2");  //不用了 stat_roi_pf_country_v2,v3数据不准 ,每次重跑会叠加数据!
//require IB_ROOT.'/stat/stat_roi_pf_country_v2.php';

//writeRunLog("stat_roi_pf_country_reg");
//require IB_ROOT.'/stat/stat_roi_pf_country_reg.php'; //目前没用上,可以

//writeRunLog("stat_dressUp");
//require IB_ROOT.'/stat/stat_dressUp.php';

writeRunLog("stat_exchange_pf_country_send");
require IB_ROOT.'/stat/stat_exchange_pf_country_send.php';

writeRunLog("stat_log_rbi_dailyGoodsCost");
include IB_ROOT.'/stat/stat_log_rbi_dailyGoodsCost.php';

//writeRunLog("stat_recharge_cumulative");
//require IB_ROOT.'/stat/stat_recharge_cumulative.php';

writeRunLog("================ended cron stat_sign_allServer of '".SERVER_ID."', takes ".(time() - $stat_cron_start)."===============");
remove_pid_file(MODULE);
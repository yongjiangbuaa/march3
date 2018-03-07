<?php
defined('IB_ROOT') || define('IB_ROOT', __DIR__);
require_once IB_ROOT.'/ib.inc.php';

ini_set('memory_limit', '3072M');

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

//writeRunLog("pay_goldStatistics_daily");
//include IB_ROOT.'/stat/pay_goldStatistics_daily.php';

writeRunLog("stat_lost_payUsers");
include IB_ROOT.'/stat/stat_lost_payUsers.php';

writeRunLog("stat_recharge_cumulative");
include IB_ROOT.'/stat/stat_recharge_cumulative.php';

writeRunLog("pay_payAnalyze_7day");
include IB_ROOT.'/stat/pay_payAnalyze_7day.php';

writeRunLog("pay_analyze_pf_country");
include IB_ROOT.'/stat/pay_analyze_pf_country.php';

writeRunLog("pay_analyze_pf_country_week");
include IB_ROOT.'/stat/pay_analyze_pf_country_week.php';

//writeRunLog("pay_analyze_pf_country_referrer"); //弃用,用下边的
//include IB_ROOT.'/stat/pay_analyze_pf_country_referrer.php';

writeRunLog("pay_analyze_pf_country_referrer_new");
include IB_ROOT.'/stat/pay_analyze_pf_country_referrer_new.php';

writeRunLog("================ended cron stat of '".SERVER_ID."', takes ".(time() - $stat_cron_start)."===============");
remove_pid_file(MODULE);
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

//writeRunLog("stat_retention_daily_pf_country"); //弃用
//include IB_ROOT.'/stat/stat_retention_daily_pf_country.php';

writeRunLog("stat_retention_daily_pf_country_new");
include IB_ROOT.'/stat/stat_retention_daily_pf_country_new.php';

writeRunLog("stat_retention_daily_pf_country_referrer_appVersion");
include IB_ROOT.'/stat/stat_retention_daily_pf_country_referrer_appVersion.php';

writeRunLog("stat_retention_daily_pf_country_version");
include IB_ROOT.'/stat/stat_retention_daily_pf_country_version.php';

remove_pid_file(MODULE);
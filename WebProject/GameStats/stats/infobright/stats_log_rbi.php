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


writeRunLog("stat_log_rbi_dailyActive");
include IB_ROOT.'/stat/stat_log_rbi_dailyActive.php';

remove_pid_file(MODULE);
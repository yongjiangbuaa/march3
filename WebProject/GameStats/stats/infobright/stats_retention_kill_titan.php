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
//参数只要name
if(!write_pid_file(MODULE)){
	return;
}

$stat_cron_start = time();


writeRunLog("stat_retention_kill_titan");
include IB_ROOT.'/stat/stat_retention_kill_titan.php';

remove_pid_file(MODULE);

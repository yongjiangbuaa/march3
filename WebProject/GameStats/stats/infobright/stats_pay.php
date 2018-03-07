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
writeRunLog("================start cron pay_payTotle_pf_country of '".SERVER_ID."' ===============");
//付费 统计, 后台管理--支付总额 要用
writeRunLog("pay_payTotle_pf_country");
include IB_ROOT.'/stat/pay_payTotle_pf_country.php';

writeRunLog("================ended cron pay_payTotle_pf_country of '".SERVER_ID."', takes ".(time() - $stat_cron_start)."===============");
remove_pid_file(MODULE);
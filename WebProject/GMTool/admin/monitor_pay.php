<?php
define('IN_ADMIN',true);
define('APP_ROOT', realpath(dirname(__FILE__) . '/../gameengine/test'));
define('ADMIN_ROOT', realpath(dirname(__FILE__) . '/'));

define('TIMES', 10);
define('CHECK_SPAN', 3600);

ini_set('mbstring.internal_encoding','UTF-8');
include ADMIN_ROOT.'/config.inc.php';
include ADMIN_ROOT.'/admins.php';
include ADMIN_ROOT.'/menu_config.php';
include_once ADMIN_ROOT.'/servers.php';
date_default_timezone_set('UTC');

$page = new BasePage();

foreach ( $argv as $arg ) {
	$kv = explode ( '=', $arg, 2 );
	$_REQUEST [$kv [0]] = $kv [1];
}

if (isset($_REQUEST['pf'])) {
	$pf = $_REQUEST['pf'];//'facebook'
}else{
	$pf = 'all';
}

if (!in_array($pf, array('all','google','ios','nstore','facebook','tstore','amazon','cafebazaar'))) {
	exit('forbidden.');
}

if (isset($_REQUEST['server'])) {
	$servers = array($_REQUEST['server']=>1);
	$calc_all_server = false;
}else{
	if ($pf == 'facebook') {
		$servers=array('s152','s188','s191','s194','s197','s199','s236','s263','s281','s289','s305');
		for ($i = 0; $i < 10; $i++) {
			$sid = rand(316, 340);
			$servers[] = "s$sid";
		}
	}else{
// 		$servers = array_keys($GLOBALS['servers']);
		for ($i = 0; $i < 10; $i++) {
			$sid = rand(1, 340);
			$servers[] = "s$sid";
		}
	}
	$calc_all_server = true;
}

//当前
if (isset($_REQUEST['detecttime'])) {
	$nowTime = $_REQUEST['detecttime'];
}else{
	$nowTime = time();
}
$startTime = $nowTime - CHECK_SPAN;

//环比
$huanbi_end = $startTime;
$huanbi_start = $huanbi_end - CHECK_SPAN;

//同比
$tongbi_end = $nowTime - 86400;
$tongbi_start = $tongbi_end - CHECK_SPAN;

$startTime *= 1000;
$nowTime *= 1000;
$huanbi_end *= 1000;
$huanbi_start *= 1000;
$tongbi_end *= 1000;
$tongbi_start *= 1000;

$g_isNG = false;
$errInfo = array();

// echo $startTime,' ',$nowTime,' ',$huanbi_start,' ',$huanbi_end,' ',$tongbi_start,' ',$tongbi_end."\n";
$all_paysum = array();
$all_paysum['amt']['now'] = 0;
$all_paysum['cnt']['now'] = 0;
$all_paysum['amt']['huanbi'] = 0;
$all_paysum['cnt']['huanbi'] = 0;
$all_paysum['amt']['tongbi'] = 0;
$all_paysum['cnt']['tongbi'] = 0;

foreach ($servers as $server){
    $paysum = array();
    $paysum['amt']['now'] = 0;
    $paysum['cnt']['now'] = 0;
    $paysum['amt']['huanbi'] = 0;
    $paysum['cnt']['huanbi'] = 0;
    $paysum['amt']['tongbi'] = 0;
    $paysum['cnt']['tongbi'] = 0;
    $res = array();
    $isNG1 = $isNG2 = false;
     //echo $server."\n";
    
	if ($pf != 'all') {
	    $sql = "SELECT spend,pf,productId,uid,time from paylog where time BETWEEN $tongbi_start and $nowTime and pf='$pf'";
	}else{
		$sql = "SELECT spend,pf,productId,uid,time from paylog where time BETWEEN $tongbi_start and $nowTime";
	}
	
    $ret = $page->executeServer($server, $sql, 3, true);
    $result = $ret['ret']['data'];
    
    foreach ($result as $row) {
    	$paytime = $row['time'];
    	if ($paytime >= $startTime && $paytime < $nowTime) {
    		$paysum['amt']['now'] += $row['spend'];
    		$paysum['cnt']['now'] += 1;
    		$all_paysum['amt']['now'] += $row['spend'];
    		$all_paysum['cnt']['now'] += 1;
    	}
    	if ($paytime >= $huanbi_start && $paytime < $huanbi_end) {
    		$paysum['amt']['huanbi'] += $row['spend'];
    		$paysum['cnt']['huanbi'] += 1;
    		$all_paysum['amt']['huanbi'] += $row['spend'];
    		$all_paysum['cnt']['huanbi'] += 1;
    	}
    	if ($paytime >= $tongbi_start && $paytime < $tongbi_end) {
    		$paysum['amt']['tongbi'] += $row['spend'];
    		$paysum['cnt']['tongbi'] += 1;
    		$all_paysum['amt']['tongbi'] += $row['spend'];
    		$all_paysum['cnt']['tongbi'] += 1;
    	}
    }
    
//     print_r($paysum);
    
    // compare pay count
    $isNG1 = decide_status($paysum['cnt']['now'], $paysum['cnt']['huanbi']);
	$isNG2 = decide_status($paysum['cnt']['now'], $paysum['cnt']['tongbi']);
    
    if ($isNG1 && $isNG2) {
    	$res[] = "hour-on-hour currHour={$paysum['cnt']['now']} lastHour={$paysum['cnt']['huanbi']}";
    	$res[] = "day-on-day currHour={$paysum['cnt']['now']} yestodayHour={$paysum['cnt']['tongbi']}";
    	$errInfo['pay_count'][$server] = $res;
//     	$g_isNG = true;//TODO 单服信息
    }
}

// print_r($all_paysum);

// ALL compare pay count
$res = array();
$isNG1 = decide_status($all_paysum['cnt']['now'], $all_paysum['cnt']['huanbi']);
$isNG2 = decide_status($all_paysum['cnt']['now'], $all_paysum['cnt']['tongbi']);
if ($isNG1 && $isNG2) {
	$res[] = "hour-on-hour currHour={$all_paysum['cnt']['now']} lastHour={$all_paysum['cnt']['huanbi']}";
	$res[] = "day-on-day currHour={$all_paysum['cnt']['now']} yestodayHour={$all_paysum['cnt']['tongbi']}";
	$errInfo['pay_count']['allserver'] = $res;
	$g_isNG = true;
}

// ALL compare pay pay_amount
$res = array();
$isNG1 = decide_status($all_paysum['amt']['now'], $all_paysum['amt']['huanbi']);
$isNG2 = decide_status($all_paysum['amt']['now'], $all_paysum['amt']['tongbi']);
if ($isNG1 && $isNG2) {
	$res[] = "hour-on-hour currHour={$all_paysum['amt']['now']} lastHour={$all_paysum['amt']['huanbi']}";
	$res[] = "day-on-day currHour={$all_paysum['amt']['now']} yestodayHour={$all_paysum['amt']['tongbi']}";
	$errInfo['pay_amount']['allserver'] = $res;
	$g_isNG = true;
}

if($g_isNG){
// 	print_r($errInfo);
	exit(json_encode($errInfo));
}
else{
	exit('zhengchang'); 
}

function decide_status($curr, $compare) {
 	if ($compare < 10) {
 		return false;
 	}
	if ($curr == 0) {
		return true;
	}
	if ( $compare / $curr > TIMES ){
		return true;
	}
	return false;
}

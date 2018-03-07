<?php
define('IN_ADMIN',true);
define('APP_ROOT', realpath(dirname(__FILE__) . '/../gameengine/test'));
define('ADMIN_ROOT', realpath(dirname(__FILE__) . '/'));
ini_set('mbstring.internal_encoding','UTF-8');
include ADMIN_ROOT.'/config.inc.php';
include ADMIN_ROOT.'/admins.php';
include_once ADMIN_ROOT.'/servers.php';
date_default_timezone_set('UTC');
set_time_limit(0);

global $servers;
$page = new BasePage();
$bool = false;
$errInfo = array();

if (!empty($argv)) {
	foreach ( $argv as $arg ) {
		$kv = explode ( '=', $arg, 2 );
		$_REQUEST [$kv [0]] = $kv [1];
	}
}

if (isset($_REQUEST['date'])) {
	$date_stat = $_REQUEST['date'];
}else{
	$date_stat = date('Y-m-d', time()-300);
}

$time_start = strtotime($date_stat);
$time_end = $time_start + 86400;

$time_start *= 1000;
$time_end *= 1000;

foreach ($servers as $server=>$info){
	$startTime = time();
    if($server ==  'global'){
        continue;
    }
    $result = array();
    $dausql = "SELECT date_format(from_unixtime(`time`/1000),'%Y-%m-%d') date ,count(distinct uid) dau from stat_login where time>=$time_start and time<$time_end group by date";
    $paysql = "SELECT date_format(from_unixtime(`time`/1000),'%Y-%m-%d') date ,sum(spend) pay from paylog where time>=$time_start and time<$time_end group by date";
    $regsql = "SELECT date_format(from_unixtime(`time`/1000),'%Y-%m-%d') date ,count(distinct uid) reg from stat_reg where time>=$time_start and time<$time_end group by date";
// 	echo $dausql,"\n",$paysql,"\n",$regsql,"\n";
	
	//dau    
    $ret = $page->executeServer($server, $dausql, 2);
    foreach( $ret['ret']['data'] as $value){
        $result[$value['date']]['date'] = "'".$value['date']."'";
        $result[$value['date']]['dau'] = $value['dau'];
    };
    //pay
    $payret = array();
    $ret = $page->executeServer($server, $paysql, 2);
    foreach( $ret['ret']['data'] as $key=>$value){
        $payret[$value['date']] = $value['pay'];
    };
    $regret = array();
    //reg
    $ret = $page->executeServer($server, $regsql, 2);
    foreach( $ret['ret']['data'] as $value){
        $regret[$value['date']] = $value['reg'];
    };
    
    foreach ($result as $date=>$value){
        $result[$date]['pay'] = $payret[$date] ? $payret[$date] : 0;
        $result[$date]['reg'] = $regret[$date] ? $regret[$date] : 0;
    }
    
    foreach ($result as $date=>$fieldvalue){
        $str = join(',', $fieldvalue);
        $insertSql = "INSERT into stat_date VALUES "." ({$str}) ";
        $updKv = buildUpdateSql($fieldvalue);
        $ondup = 'ON DUPLICATE KEY UPDATE '.$updKv;
        $insertSql .= " $ondup;";
	    echo date('[Y-m-d H:i:s]')." $date_stat $date $server [$insertSql] CostTime=".(time() - $startTime)."s\n";
        if ($date != $date_stat) {
    		continue;
    	}
        $ret = $page->executeServer($server, $insertSql, 2);
// 	    print_r($ret);
    }
}

function buildUpdateSql($kv){
	$all = array();
	foreach ($kv as $key => $value) {
		$all[] = "$key=$value";
	}
	return implode(',', $all);
}
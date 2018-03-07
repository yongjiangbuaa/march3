<?php
define('IN_ADMIN',true);
define('APP_ROOT', realpath(dirname(__FILE__) . '/../gameengine/test'));
define('ADMIN_ROOT', realpath(dirname(__FILE__) . '/'));
define('TIMES', 1.5);
ini_set('mbstring.internal_encoding','UTF-8');
include ADMIN_ROOT.'/config.inc.php';
include ADMIN_ROOT.'/admins.php';
include ADMIN_ROOT.'/menu_config.php';
include_once ADMIN_ROOT.'/servers.php';
$page = new BasePage();

foreach ( $argv as $arg ) {
	$kv = explode ( '=', $arg, 2 );
	$_REQUEST [$kv [0]] = $kv [1];
}

if (isset($_REQUEST['detecttime'])) {
	$checkTime = $_REQUEST['detecttime'];
}else{
	$checkTime = $_SERVER['REQUEST_TIME'];
}
$startTime = $checkTime - 700;
$isNG = false;
$errInfo = array();

if (isset($_REQUEST['server'])) {
	$servers = array($_REQUEST['server']=>1);
}else{
	$servers = $GLOBALS['servers'];
}
if (isset($_REQUEST['type'])) {
	$type = $_REQUEST['type'];
}else{
	$type = 'ALL';
}

foreach ($servers as $server=>$info){

$rand = rand(1,100);
if($rand < 60){
continue;
}

$sid = substr($server,1);
//if($sid>10){continue;}
if($_REQUEST['test']==1){
echo $server."\n";
}
/*
	if (in_array($type, array('ALL', 'five_online'))) {
	    $sql = "SELECT timeStamp,count from fiveonlinedata where timeStamp BETWEEN  $startTime and $checkTime ORDER BY timeStamp DESC limit 2";
	    $ret = $page->executeServer($server, $sql, 3);
	    $result = $ret['ret']['data'];
	    $losedata = false;
	    if (count($result) < 2) {
	    	$losedata = true;
	    	$desc = '';
	    }
	    $firstNum = $result[0]['count'];
	    $lastNum = $result[1]['count'];
	    $di = $result[0]['timeStamp'] - $result[1]['timeStamp'];//lose data.
	    $res = array('data'=>$result, 'desc'=>$desc);
	    if($losedata || $lastNum==0 || ($firstNum + $lastNum) / min($firstNum,$lastNum) > (1+TIMES) || $di>360){
	        $isNG = true;
	        //5分钟在线
	        $errInfo['five_online'][$server] = $res;
	    }
	}
*/	
	if (in_array($type, array('ALL', 'world_point'))) {
		$sql = "select count(*) count from user_world where pointId = 1";
if($sid ==19 || $sid==29 ){
	    $ret = $page->executeServer($server, $sql, 3);
}else{
	    $ret = $page->executeServer($server, $sql, 3);
}
		$result = $ret ['ret'] ['data'] [0] ['count'];
		$res = array('data'=>$result, 'desc'=>'');
		if ($result > 10) {
			$isNG = true;
			// 世界分配点
			$errInfo ['world_point'] [$server] = $res;
		}
	}
}

if($isNG){
   exit(json_encode($errInfo));
}
else{
   exit('zhengchang'); 
}

<?php
define('IN_ADMIN',true);
define('APP_ROOT', realpath(dirname(__DIR__) . '/../gameengine/test'));
define('ADMIN_ROOT', realpath(dirname(__DIR__) . '/'));
ini_set('mbstring.internal_encoding','UTF-8');
include ADMIN_ROOT.'/config.inc.php';
include ADMIN_ROOT.'/admins.php';
include_once ADMIN_ROOT.'/servers.php';
$page = new BasePage();
date_default_timezone_set('UTC');
set_time_limit(0);
ini_set('memory_limit', '1024M');

global $servers;
$eventAll = array();



$uids = array(
's48,107654210000118',
's276,1085645996000170',
's145,1036187643000118',
's211,1076596677000118',
's118,1025032899000263',
's275,717245611000263',
's170,841696531000263',
's271,979725774000263',
's274,961229328000263',
's278,1027548284000112',
's239,1083535029000170',
's239,1083478000000170',
's271,837682387000263',
's273,1074816336000170',
);

foreach ($uids as $su) {
	$aa = explode(",", $su);
	$server = $aa[0];
	$uid = $aa[1];
	
// 	$accall = cobar_getAccountInfoByGameuids($uid);
// 	$acc = $accall[0];
	
// 	if ($acc['server'] != substr($server,1)) {
// 		echo "'s{$acc['server']},$uid',\n";
// 	}
	
	
// 	continue;
	
	
// 	$ret = $page->webRequest('kickuser',array('uid'=>$uid));
// 	if($ret == 'ok'){
		$sql = "update userprofile set banTime=9223372036854775807 where uid ='$uid'";
		$re = $page->executeServer($server,$sql,2);
		if($uid){
			cobar_query_global_db_cobar("update account_new set active = 1 where gameUid = '{$uid}'");
		}
		
		$sql = "select pointid from user_world where uid='$uid'";
		$result = $page->executeServer($server,$sql, 3,true);
		//print_r($result);
		//exit();
		$pointid = $result['ret']['data'][0]['pointid'];
		$currserver = $server;
		$serverinfo = $servers[$currserver];
		$ip = $serverinfo['ip_inner'];
		$rediskey = 'world'.substr($currserver, 1);
		$redis = new Redis();
		$redis->connect($ip,6379);
		$rd_json = $redis->hDel($rediskey, $pointid);
		$sql="update worldpoint set pointType=1 where id=$pointid;";
		$re = $page->executeServer($server,$sql,2);
		
		$accall = cobar_getAccountInfoByGameuids($uid);
		$acc = $accall[0];
		print_r($acc);
		$facebookAccount = $acc['facebookAccount'];
		if ($facebookAccount) {
			cobar_delUserMapping('facebook',$facebookAccount,$uid);
		}
		$googleAccount = $acc['googleAccount'];
		if ($googleAccount) {
			cobar_delUserMapping('google',$googleAccount,$uid);
		}
		$deviceId = $acc['deviceId'];
		if ($deviceId) {
			cobar_delUserMapping('device',$deviceId,$uid);
		}
// 	}
}



// s239,1083478000000170
// Array
// (
// [gameUid] => 1083478000000170
// [server] => 239
// [uuid] => 355098063841022-848edf4f1e641429366307990
// [deviceId] => 355098063841022-848edf4f1e64
// [gaid] => 0f93cc48-24be-42aa-9dbc-ca7e1fd5eeb7
// [country] =>
// [emailAccount] =>
// [googleAccount] => 101059047352140933634
// [googleAccountName] => zxcv56687@gmail.com
// [facebookAccount] => 536238913185528
// [facebookAccountName] => 賴俊偉
// [pf] => market_global
// [pfId] =>
// [gameUserName] => Wei_鄞
// [gameUserLevel] => 25
// [lastTime] => 1429703587580
// [emailConfirm] => 0
// [passmd5] =>
// [active] => 1
// )

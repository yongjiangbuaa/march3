<?php
!defined('IN_ADMIN') && exit('Access Denied');
$showData = false;
$afterData = false;
$type = $_REQUEST['action'];
if($_REQUEST['deviceId'])
	$deviceId = $_REQUEST['deviceId'];
if($_REQUEST['currentGameUid'])
	$currentGameUid = $_REQUEST['currentGameUid'];
if($_REQUEST['wantGameUid'])
	$wantGameUid = $_REQUEST['wantGameUid'];
$indexArray = array(
				'gameUid' =>'游戏Uid',
				'server' =>'服务器',
				'deviceId' =>'设备Id',
				'country' =>'国家',
				'gameUserName' =>'用户名',
				'gameUserLevel' =>'游戏等级',
				'lastTime' =>'上次登录时间',
				'active' =>'正常(0)|未激活(1)'
);
if($type=='view'){
// 	$currentsql="select gameUid,server,deviceId,country,gameUserName,gameUserLevel,lastTime,active from cokdb_global.account_new where deviceId='$deviceId' and gameUid='$currentGameUid';";
// 	$wantsql="select gameUid,server,deviceId,country,gameUserName,gameUserLevel,lastTime,active from cokdb_global.account_new where deviceId='$deviceId' and gameUid='$wantGameUid';";
// 	$currentresult = $page->globalExecute($currentsql, 3);
	$currentresult['ret']['data'] = cobar_getAllAccountList('device', $deviceId, $currentGameUid);
	$currentData = $currentresult['ret']['data'][0];
// 	$wantresult = $page->globalExecute($wantsql, 3);
	$wantresult['ret']['data'] = cobar_getAllAccountList('device', $deviceId, $wantGameUid);
	$wantData = $wantresult['ret']['data'][0];
	
	$showData = true;
	$afterData =false;
}
if ($type=='find') {
// 	$serverSql = "select server from cokdb_global.account_new where gameUid='$currentGameUid';";
// 	$serverResult = $page->globalExecute($serverSql, 3);
	$serverResult['ret']['data'] = cobar_getAccountInfoByGameuids($currentGameUid);
	$serverResult = $serverResult['ret']['data'][0];
	$server = $serverResult['server'];
	$server ='s'.$server;
	$host = gethostbyname(gethostname());
	if ($host == 'IPIPIP') {
		$server = 'localhost';
	}elseif ($host == 'IPIPIP'){
		$server = 'test';
	}
	$updateCurSql = "update userprofile set banTime= 9223372036854775807 where uid = '$currentGameUid';";
	$result = $page->executeServer($server,$updateCurSql,2);
	
	$time = (time()+7200)*1000;
	$updateWantSql = "update cokdb_global.account_new set active = 0, lastTime = $time where gameUid = '$wantGameUid';";
// 	$result = $page->globalExecute($updateWantSql, 2);  
	cobar_query_global_db_cobar($updateWantSql);
	
// 	$serverSql = "select server from cokdb_global.account_new where gameUid='$wantGameUid';";
// 	$serverResult = $page->globalExecute($serverSql, 3);
	$serverResult['ret']['data'] = cobar_getAccountInfoByGameuids($wantGameUid);
	$serverResult = $serverResult['ret']['data'][0];
	$server = $serverResult['server'];
	$server ='s'.$server;
	$host = gethostbyname(gethostname());
	if ($host == 'IPIPIP') {
		$server = 'localhost';
	}elseif ($host == 'IPIPIP'){
		$server = 'test';
	}
	$updateCurSql = "update userprofile set banTime=0 where uid = '$wantGameUid';";
	$result = $page->executeServer($server,$updateCurSql,2);
	
// 	$currentsql="select gameUid,server,deviceId,country,gameUserName,gameUserLevel,lastTime,active from cokdb_global.account_new where deviceId='$deviceId' and gameUid='$currentGameUid';";
// 	$wantsql="select gameUid,server,deviceId,country,gameUserName,gameUserLevel,lastTime,active from cokdb_global.account_new where deviceId='$deviceId' and gameUid='$wantGameUid';";
// 	$currentresult = $page->globalExecute($currentsql, 3);
	$currentresult['ret']['data'] = cobar_getAllAccountList('device', $deviceId, $currentGameUid);
	$currentData = $currentresult['ret']['data'][0];
// 	$wantresult = $page->globalExecute($wantsql, 3);
	$wantresult['ret']['data'] = cobar_getAllAccountList('device', $deviceId, $wantGameUid);
	$wantData = $wantresult['ret']['data'][0];
    adminLogUser($adminid,$wantGameUid,$server,array('switch_account'=>array('from'=>$currentGameUid,'to'=>$wantGameUid)));
	$showData = false;
	$afterData =true;
}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>
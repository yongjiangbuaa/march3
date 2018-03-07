<?php
!defined('IN_ADMIN') && exit('Access Denied');
$showData = false;
$afterData = false;
$alertHeader='';
$type = $_REQUEST['action'];
if($_REQUEST['newDeviceId'])
	$newDeviceId = trim($_REQUEST['newDeviceId']);
if($_REQUEST['newUid'])
	$newUid = trim($_REQUEST['newUid']);
if($_REQUEST['oldDeviceId'])
	$oldDeviceId = trim($_REQUEST['oldDeviceId']);
if($_REQUEST['oldUid'])
	$oldUid = trim($_REQUEST['oldUid']);
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
if ($type=='turn') {
	file_put_contents('log/user_switchAccount.log', date('Y-m-d H:i:s ').json_encode($_REQUEST)." $adminid\n", FILE_APPEND);
// 	$serverSql = "select server from cokdb_global.account_new where gameUid='$newUid';";
// 	$serverResult = $page->globalExecute($serverSql, 3);

	$newRet=cobar_getAllAccountList('device',$newDeviceId,$newUid);
	$oldRet=cobar_getAllAccountList('device',$oldDeviceId,$oldUid);
	
	$selSql="select uid,banTime from userprofile where uid='".$oldRet[0]['gameUid']."';";
	$oldSer='s'.$oldRet[0]['server'];
	echo 'uid:'.$oldRet[0]['gameUid'].',server:'.$oldSer;
	$selResult=$page->executeServer($oldSer, $selSql, 3);
	$oldUidBantime=$selResult['ret']['data'][0]['banTime'];
	
	echo $selSql.'时间'.$oldUidBantime;
	if (!$newRet[0] || !$oldRet[0] || ($oldUidBantime>time()*1000)){
		if (!$newRet[0]){
			$alertHeader='新UID以及对应的新设备Id不存在!';
		}
		if (!$oldRet[0]){
			$alertHeader='旧UID以及对应的旧设备Id不存在!';
		}
		if ($oldUidBantime>time()*1000){
			$alertHeader='旧UID处于封号中,不能转!';
		}
	}else {
		$serverResult['ret']['data'] = cobar_getAccountInfoByGameuids($newUid);
		$serverResult = $serverResult['ret']['data'][0];
		$server = $serverResult['server'];
		$server ='s'.$server;
		$host = gethostbyname(gethostname());
		if ($host == 'IPIPIP') {
			$server = 'localhost';
		}elseif ($host == 'IPIPIP'){
			$server = 'test';
		}
		$updateCurSql = "update userprofile set banTime= 9223372036854775807 where uid = '$newUid';";
		$result = $page->executeServer($server,$updateCurSql,2);
		
		$time = (time()+7200)*1000;
		$sql_update_old = "update cokdb_global.account_new set deviceId='$newDeviceId', lastTime = $time where gameUid='$oldUid';";
	// 	$updateresult = $page->globalExecute($sql_update_old, 2);
		cobar_query_global_db_cobar($sql_update_old);
		
		cobar_delUserMapping('device',$oldDeviceId,$oldUid);// 删除旧的mapping，以防止2个deviceid被hash到一个表，更新gameuid时主键冲突。
		$sql_update_usermapping = "update cokdb_global.usermapping set gameUid='$oldUid' where mappingtype='device' and mappingvalue='$newDeviceId' and gameUid='$newUid';";
		cobar_query_global_db_cobar($sql_update_usermapping);
		
	// 	$sql_switch_result = "select gameUid,server,deviceId,country,gameUserName,gameUserLevel,lastTime,active from cokdb_global.account_new where deviceId='$newDeviceId' and gameUid='$oldUid';";
	// 	$result =  $page->globalExecute($sql_switch_result, 3);
		$result['ret']['data'] = cobar_getAllAccountList('device',$newDeviceId,$oldUid);
		$resultNewDevice = $result['ret']['data'][0];
		
	// 	$newsql="select gameUid,server,deviceId,country,gameUserName,gameUserLevel,lastTime,active from cokdb_global.account_new where deviceId='$newDeviceId' and gameUid='$newUid';";
	// 	$result = $page->globalExecute($newsql, 3);
		$result['ret']['data'] = cobar_getAllAccountList('device',$oldDeviceId,$oldUid);
		$resultOldDevice = $result['ret']['data'][0];
		$afterData =true;
		$showData = false;
	    adminLogUser($adminid,$oldUid,$server,array('switch_account'=>array('from'=>$newUid,'to'=>$oldUid)));
	}
	
}
if($type=='view'){
	file_put_contents('log/user_switchAccount.log', date('Y-m-d H:i:s ').json_encode($_REQUEST)." $adminid\n", FILE_APPEND);
// 	$newsql="select gameUid,server,deviceId,country,gameUserName,gameUserLevel,lastTime,active from cokdb_global.account_new where deviceId='$newDeviceId' and gameUid='$newUid';";
// 	$oldsql="select gameUid,server,deviceId,country,gameUserName,gameUserLevel,lastTime,active from cokdb_global.account_new where deviceId='$oldDeviceId' and gameUid='$oldUid';";
// 	$newresult = $page->globalExecute($newsql, 3);
	$newresult['ret']['data'] = cobar_getAllAccountList('device', $newDeviceId, $newUid);
	$newData = $newresult['ret']['data'][0];
// 	$oldresult = $page->globalExecute($oldsql, 3);
	$oldresult['ret']['data'] = cobar_getAllAccountList('device', $oldDeviceId, $oldUid);
	$oldData = $oldresult['ret']['data'][0];
	file_put_contents('log/user_switchAccount.log', date('Y-m-d H:i:s ').json_encode($oldData).' '.json_encode($newData)." $adminid\n", FILE_APPEND);
	$showData = true;
	$afterData =false;
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
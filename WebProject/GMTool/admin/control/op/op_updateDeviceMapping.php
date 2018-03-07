<?php
!defined('IN_ADMIN') && exit('Access Denied');

if ($_REQUEST['type']=='save') {
	$uid = $_REQUEST['uid'];
	$deviceId = $_REQUEST['deviceId'];
	if (!$uid){
		exit('uid不能为空!');
	}
	if (!$deviceId){
		exit('设备ID不能为空!');
	}
	$account_list = cobar_getAccountInfoByGameuids($uid);
	$server = 's'.$account_list[0]['server'];
	$time=time()*1000;
	$oldDeviceId = $account_list[0]['deviceId'];
	if($oldDeviceId == $deviceId){
		exit('同一个设备！');
	}
	$ret = cobar_query_global_db_cobar("select * from account_device_mapping where gameUid='$uid' and deviceId='$oldDeviceId' and type=1;");
	if($ret == null || $ret[0] == null){
		$sql = "insert into account_device_mapping(gameUid, deviceId, type) values ('$uid', '$oldDeviceId', 1);";
		$ret=cobar_query_global_db_cobar($sql);
	}
	$sql = "insert into account_device_mapping(gameUid, deviceId, type, time) values('$uid','$deviceId',4,$time);";//在account_device_mapping表中插入
	$ret=cobar_query_global_db_cobar($sql);
	$sql = "update usermapping set mappingValue='$deviceId' where gameUid='$uid' and mappingType='device';";
	$ret=cobar_query_global_db_cobar($sql);
	$sql = "update account_new set deviceId='$deviceId', lastTime=$time where gameUid='$uid';";
	$ret=cobar_query_global_db_cobar($sql);
	adminLogUser($adminid,$uid,$server,array('deviceId'=>$deviceId,'执行结果'=>$sql));
	exit('OK');
	
}

if ($_REQUEST['type']=='view') {
	$uid = $_REQUEST['uid'];
	$account_list = cobar_getAccountInfoByGameuids($uid);
	$server = 's'.$account_list[0]['server'];
	if (!$uid){
		exit('uid不能为空!');
	}
	$time=time()*1000;
	$sql="select gameUid,deviceId from account_device_mapping where gameUid='$uid';";
	$ret=cobar_query_global_db_cobar($sql);
	
	$html='<input class="btn js-btn btn-primary" type="button" value="盗号者清除serverList" id="btn_removeServ" name="btn_edit" onclick="removeServ('."'$uid'".')" />';
	$html.='<br>';
	$html.='<table class="table table-striped" style="TABLE-LAYOUT:fixed;WORD-WRAP:break-word;word-break:break-all;width:1000px;">';
	$html.='<tr><th>UID</th><th>设备Id</th><th>编辑</th></tr>';
	foreach ($ret as $row){
		$html.='<tr><td>'.$row['gameUid'].'</td><td>'.$row['deviceId'].'</td><td><a href="javascript:void(delVal('."'".$row['gameUid']."','".$row['deviceId']."'".'))">删除</a></td></tr>';
	}
	
	echo $html;
	exit();
}

if ($_REQUEST['type']=='del') {
	$uid = $_REQUEST['uid'];
	$deviceId = $_REQUEST['deviceId'];
	if (!$uid){
		exit('uid不能为空!');
	}
	if (!$deviceId){
		exit('设备ID不能为空!');
	}
	$oldAccountMapping=cobar_query_global_db_cobar("select * from account_device_mapping where gameUid='$uid' and type=1;");
	if($oldAccountMapping == null || $oldAccountMapping[0] == null){
		exit('this mapping can not delete!');
	}
	$originalDeviceId = $oldAccountMapping[0]['deviceId'];
	if($originalDeviceId == $deviceId){
		exit("不能删除注册时的mapping关系");
	}
	$account_list = cobar_getAccountInfoByGameuids($uid);
	$server = 's'.$account_list[0]['server'];
	$sql="delete from account_device_mapping where gameUid='$uid' and deviceId='$deviceId';";
	$ret=cobar_query_global_db_cobar($sql);
	$sql = "update usermapping set mappingValue='$originalDeviceId' where gameUid='$uid' and mappingType='device';";
	$ret=cobar_query_global_db_cobar($sql);
	$sql = "update account_new set deviceId='$originalDeviceId' where gameUid='$uid';";

	$ret=cobar_query_global_db_cobar($sql);

	adminLogUser($adminid,$uid,$server,array('deviceId'=>$deviceId,'执行结果'=>$sql));
	exit('OK');

}
if ($_REQUEST['type']=='removeSer') {
	global $servers;
	$uid = $_REQUEST['uid'];
	if (!$uid){
		exit('uid不能为空!');
	}
	$account_list = cobar_getAccountInfoByGameuids($uid);
	$server = 's'.$account_list[0]['server'];
	
	$redis=new Redis();
	$host = gethostbyname(gethostname());
	if ($host == 'IPIPIP' || $host == 'IPIPIP' || $host == 'URLIP') {
		$redis->connect('URLIP',6379);
	}elseif ($host == 'IPIPIP'){
		$redis->connect('10.142.9.80',6379);
	}else {
		$currentIP = $servers[$server]['ip_inner'];
		$redis->connect($currentIP,6379);
	}
	$ret=$redis->sAdd('steal_sufferer_set',$uid);
	
	$redis->close();

	adminLogUser($adminid,$uid,$server,array('执行结果'=>$ret));
	exit('OK');

}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>
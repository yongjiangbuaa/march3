<?php
!defined('IN_ADMIN') && exit('Access Denied');
$showData = false;
$alertHeader='';
global $servers;
$type = $_REQUEST['action'];

if($type=='view'){
	
	$redis = new Redis();
	$redis->connect(GLOBAL_REDIS_SERVER_IP,6379);
	$ret=$redis->hGetAll('h_flood_cheat_banned');
	$data=array();
	foreach ($ret as $uidKey=>$timeValue){
		$data[$uidKey]=$timeValue;
		
		/*if ($_COOKIE['u']=='yaoduo'){
			
			$accall = cobar_getAccountInfoByGameuids($uidKey);
			$acc = $accall[0];
			$opeDate=$timeValue;
			$time=strtotime($timeValue)*1000;
			$serverId=$acc['server'];
			$uuid=md5($serverId.$uidKey.$time);
			$sql="insert into ban_record(uuid,serverId,uid,time,operator,reason,opDate) values('$uuid',$serverId,'$uidKey',$time,'系统','行军作弊','$opeDate')";
			$page->globalExecute($sql, 2);
			
		}*/
	}
	$payData=array();
	$ret=$redis->hGetAll('h_pay_cheat_banned');
	foreach ($ret as $uidKey=>$timeValue){
		$payData[$uidKey]=$timeValue;
		
		/*if ($_COOKIE['u']=='yaoduo'){
				
			$accall = cobar_getAccountInfoByGameuids($uidKey);
			$acc = $accall[0];
			$opeDate=$timeValue;
			$time=strtotime($timeValue)*1000;
			$serverId=$acc['server'];
			$uuid=md5($serverId.$uidKey.$time);
			$sql="insert into ban_record(uuid,serverId,uid,time,operator,reason,opDate) values('$uuid',$serverId,'$uidKey',$time,'系统','充值作弊','$opeDate')";
			$page->globalExecute($sql, 2);
				
		}*/
	}
	
	if (isset($data) || isset($payData)){
		$showData=true;
	}else {
		$alertHeader="没有查到相关数据";
	}
}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>
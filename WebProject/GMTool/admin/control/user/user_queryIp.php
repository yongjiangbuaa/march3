<?php
!defined('IN_ADMIN') && exit('Access Denied');
$showData = false;
$type = $_REQUEST['action'];
if(!$_REQUEST['startDate']){
	$startDate = date("Y-m-d",time()-86400*7);
}
if(!$_REQUEST['endDate'])
	$endDate = date("Y-m-d",time());
if($_REQUEST['username'])
	$username = $_REQUEST['username'];
if($_REQUEST['useruid'])
	$useruid = $_REQUEST['useruid'];
$headLine = "查看玩家登录的IP信息";
$headAlert = "";
if ($type=='view') {
	
	$startDate = substr($_REQUEST['startDate'],0,10);
	$sDdate = date('Ymd',strtotime($startDate));
	$startTime = strtotime($sDdate)*1000;
	$endDate = substr($_REQUEST['endDate'],0,10);
	$eDate = date('Ymd',strtotime($endDate)+86400);
	$endTime = strtotime($eDate)*1000;
	
	$yearMonth=array();
	if(date('Ym',strtotime($sDdate))==date('Ym',strtotime($eDate))){
		$yearMonth[]=date('Y',strtotime($sDdate)).'_'.(date('m',strtotime($sDdate))-1);
	}else {
		$yearMonth[]=date('Y',strtotime($sDdate)).'_'.(date('m',strtotime($sDdate))-1);
		$yearMonth[]=date('Y',strtotime($eDate)).'_'.(date('m',strtotime($eDate))-1);
	}
	
	$server=$currentServer;
	$data=array();
	$i=1;
	foreach ($yearMonth as $ym){
// 		if($username){
// 			$sql = "select u.uid,u.name,count(distinct l.ip) ipCount from userprofile u inner join stat_login_$ym l on u.uid=l.uid where u.name='$username' and l.time>=$startTime and l.time<$endTime group by u.uid;";
// 		}else {
// 			$sql = "select u.uid,u.name,count(distinct l.ip) ipCount from userprofile u inner join stat_login_$ym l on u.uid=l.uid where u.uid='$useruid' and l.time>=$startTime and l.time<$endTime group by u.uid;";
// 		}
		
		if($username){
			$account_list = cobar_getValidAccountList('name', $username);
			$useruid = $account_list[0]['gameUid'];
		}
		$sql = "select u.uid,u.name,l.ip,count(l.ip) ipCount from userprofile u inner join stat_login_$ym l on u.uid=l.uid where u.uid='$useruid' and l.time>=$startTime and l.time<$endTime group by u.uid,name,ip;";
		$result = $page->execute($sql, 3);
		foreach ($result['ret']['data'] as $row){
			$targetUid=$row['uid'];
			$targetName=$row['name'];
			$targetIpCount[$row['ip']]+=$row['ipCount'];
		}
// 		if($username){
// 			$sql = "select u.uid,u.name,l.ip,l.time from userprofile u inner join stat_login_$ym l on u.uid=l.uid where u.name='$username' and l.time>=$startTime and l.time<$endTime;";
// 		}else {
// 			$sql = "select u.uid,u.name,l.ip,l.time from userprofile u inner join stat_login_$ym l on u.uid=l.uid where u.uid='$useruid' and l.time>=$startTime and l.time<$endTime;";
// 		}

		$sql = "select u.uid,u.name,l.ip,l.time from userprofile u inner join stat_login_$ym l on u.uid=l.uid where u.uid='$useruid' and l.time>=$startTime and l.time<$endTime;";
		
		$result = $page->execute($sql, 3);
		foreach ($result['ret']['data'] as $row){
			$temp=array();
			$temp['uid']=$row['uid'];
			$temp['name']=$row['name'];
			$temp['ip']=$row['ip'];
			$temp['time']=date('Y-m-d H:i:s',$row['time']/1000);
			$data[$i]=$temp;
			$i++;
		}
	}
	if($targetUid||$targetName||$data){
		$showData = true;
	}else {
		$headAlert = '没有查到相关数据';
	}
}


include( renderTemplate("{$module}/{$module}_{$action}") );
?>
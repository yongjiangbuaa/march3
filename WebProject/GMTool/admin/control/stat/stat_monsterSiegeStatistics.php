<?php
!defined('IN_ADMIN') && exit('Access Denied');
$showData=false;
$alertHeader='';
if(!$_REQUEST['startDate']){
	$startDate = date("Y-m-d",time()-86400*7);
}
if(!$_REQUEST['endDate'])
	$endDate = date("Y-m-d",time());
global $servers;

$sttt = $_REQUEST['selectServer'];
$serverDiv=loadDiv($sttt);
$erversAndSidsArr=getSelectServersAndSids($sttt);
$selectServer=$erversAndSidsArr['withS'];
$selectServerids=$erversAndSidsArr['onlyNum'];

if($_REQUEST['analyze']=='user'){
	$sids = implode(',', $selectServerids);
	//$whereSql=" where sid in ($sids) ";
	$startDate = substr($_REQUEST['startDate'],0,10);
	$sDate= date('Ymd',strtotime($startDate));
	$startTime=strtotime($sDate)*1000;
	$endDate = substr($_REQUEST['endDate'],0,10);
	$eDate =date('Ymd',strtotime($endDate)+86400);
	$endTime=strtotime($eDate)*1000;
	//$whereSql .= " and date >=$sDate and date <= $eDate ";
	$sql="select alliancename,siegeRound,killCount,time from monster_siege_top_history where time >=$startTime and time<$endTime order by killCount desc;";
	$data=array();
	$dates=array();
	$allianceName=array();
	foreach ($selectServer as $server=>$serInfo){
		$result = $page->executeServer($server,$sql,3);
		$log=array();
		$i=0;
		foreach ($result['ret']['data'] as $row){
			$logItem=array();
			$activeTime=date('Y-m-d',$row['time']/1000);
			if (!in_array($activeTime, $dates)){
				$dates[]=$activeTime;
			}
			//$logItem['date']=$activeTime;
			$logItem['alliancename']=$row['alliancename'];
			$logItem['siegeRound']=$row['siegeRound'];
			$logItem['killCount']=$row['killCount'];
			$log[$activeTime][$i]=$logItem;
			$i++;
		}
		$data[$server]=$log;
	}
	if($data){
		$showData=true;
		rsort($dates);
	}else {
		$alertHeader="没有查到相关数据";
	}
}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>
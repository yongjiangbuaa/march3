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
$selectId=$erversAndSidsArr['onlyNum'];

if($_REQUEST['analyze']=='user'){
	$startDate = substr($_REQUEST['startDate'],0,10);
	$sDate= date('Ymd',strtotime($startDate));
	$startTime=strtotime($sDate)*1000;
	$endDate = substr($_REQUEST['endDate'],0,10);
	$eDate =date('Ymd',strtotime($endDate)+86400);
	$endTime=strtotime($eDate)*1000;
	$data=array();
	foreach ($selectServer as $server=>$serverInfo){
		$sql="select time,allianceCount,activityAllianceCount,activity30AllianceCount,activity40AllianceCount,activity50AllianceCount,validMemberCount,count(allianceId) allianceNum from monster_siege_top_history where siegeRound > 0 and time >=$startTime and time<$endTime group by time;;";
		//echo $sql."\n";
		$result = $page->executeServer($server,$sql,3);
		foreach ($result['ret']['data'] as $row){
			$activeTime=date('Y-m-d H:i:s',$row['time']/1000);
			$data[$activeTime][$server]['allianceCount']=$row['allianceCount'];
			$data[$activeTime][$server]['activityAllianceCount']=$row['activityAllianceCount'];
			$data[$activeTime][$server]['activity30AllianceCount']=$row['activity30AllianceCount'];
			$data[$activeTime][$server]['activity40AllianceCount']=$row['activity40AllianceCount'];
			$data[$activeTime][$server]['activity50AllianceCount']=$row['activity50AllianceCount'];
			$data[$activeTime][$server]['validMemberCount']=$row['validMemberCount'];
			$data[$activeTime][$server]['allianceNum']=$row['allianceNum'];
		}
	}
	if($data){
		$showData=true;
	}else {
		$alertHeader="没有查到相关数据";
	}
	
}


include( renderTemplate("{$module}/{$module}_{$action}") );
?>

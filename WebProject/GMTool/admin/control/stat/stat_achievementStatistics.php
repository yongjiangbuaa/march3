<?php
!defined('IN_ADMIN') && exit('Access Denied');
$showData=false;
$allServerFlag=false;
$headerAlert='';
if(!$_REQUEST['startDate']){
	$startDate = date("Y-m-d",time()-86400*7);
}
if(!$_REQUEST['endDate'])
	$endDate = date("Y-m-d",time());
global $servers;
$allServerFlag=false;

$sttt = $_REQUEST['selectServer'];
$serverDiv=loadDiv($sttt);
$erversAndSidsArr=getSelectServersAndSids($sttt);
$selectServer=$erversAndSidsArr['withS'];
$selectServerids=$erversAndSidsArr['onlyNum'];

if($_REQUEST['allServers']){
	$allServerFlag =true;
}
if($_REQUEST['analyze']=='user'){
	$sids = implode(',', $selectServerids);
	$whereSql=" where sid in ($sids) ";
	$startDate = substr($_REQUEST['startDate'],0,10);
	$sDdate= date('Ymd',strtotime($startDate));
	$endDate = substr($_REQUEST['endDate'],0,10);
	$eDate =date('Ymd',strtotime($endDate)+86400);
	$whereSql .= " and date >=$sDdate and date <= $eDate ";
	$sql="select sid,date,achieveId,users from stat_allserver.stat_achievement $whereSql order by date desc,sid,achieveId;";
	$result = query_infobright($sql);
	$dates=array();
	$achieveIdArray=array();
	$data=array();
	$total=array();
	foreach ($result['ret']['data'] as $curRow){
		$server='s'.$curRow['sid'];
		$yIndex = $curRow['date'];
		if(!in_array($yIndex, $dates)){
			$dates[]=$yIndex;
		}
		$achieveId = $curRow['achieveId'];
		if(!in_array($achieveId, $achieveIdArray)){
			$achieveIdArray[]=$achieveId;
		}
		$data[$yIndex][$server][$achieveId]+=$curRow['users'];
	}
	foreach ($data as $dKey=>$value1){
		foreach ($value1 as $sKey=>$value2){
			foreach ($value2 as $aKey=>$value){
				$data[$dKey]['allSum'][$aKey]+=$value;
			}
		}
	}
	if($data){
		$showData=true;
	}else {
		$headerAlert='数据查询失败!';
	}
}
	
	
	
	
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
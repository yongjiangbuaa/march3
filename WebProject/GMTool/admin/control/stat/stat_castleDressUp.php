<?php
!defined('IN_ADMIN') && exit('Access Denied');
$showData=false;
$alertHeader='';
if(!$_REQUEST['startDate']){
	$startDate = date("Y-m-d",time()-86400*7);
}
if(!$_REQUEST['endDate'])
	$endDate = date("Y-m-d",time());

$sttt = $_REQUEST['selectServer'];
$serverDiv=loadDiv($sttt);
$erversAndSidsArr=getSelectServersAndSids($sttt);
$selectServer=$erversAndSidsArr['withS'];
$selectServerids=$erversAndSidsArr['onlyNum'];

$castleArray=array('50050'=>'日本幕府','50051'=>'德式古堡');

if($_REQUEST['analyze']=='user'){
	
	$sids = implode(',', $selectServerids);
	$whereSql=" where sid in ($sids) ";
	$startDate = substr($_REQUEST['startDate'],0,10);
	$sDdate= date('Ymd',strtotime($startDate));
	$endDate = substr($_REQUEST['endDate'],0,10);
	$eDate =date('Ymd',strtotime($endDate)+86400);
	$whereSql .= " and date >=$sDdate and date <= $eDate ";
	$sql= "select sid,date,gType,userCount from stat_allserver.stat_dressUp $whereSql order by sid,date desc;";
	$data = array();
	$dates = array();
	$result = query_infobright($sql);
	foreach ($result['ret']['data'] as $curRow){
		$data['s'.$curRow['sid']][$curRow['date']][$curRow['gType']]+=$curRow['userCount'];
		$data['total'][$curRow['date']][$curRow['gType']]+=$curRow['userCount'];
		
		$data['s'.$curRow['sid']][$curRow['date']]['all']+=$curRow['userCount'];
		$data['total'][$curRow['date']]['all']+=$curRow['userCount'];
		
		if (!in_array($curRow['date'], $dates)){
			$dates[]=$curRow['date'];
		}
	}
	if ($data){
		$showData=true;
	}else {
		$alertHeader="没有查到相关数据";
	}
		
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
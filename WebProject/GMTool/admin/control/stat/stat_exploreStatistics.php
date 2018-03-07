<?php
!defined('IN_ADMIN') && exit('Access Denied');
$showData = false;
$alertHeader = '';
if(!$_REQUEST['startDate']){
	$startDate = date("Y-m-d",time()-86400*7);
}
if(!$_REQUEST['endDate'])
	$endDate = date("Y-m-d",time());

$exploreType=array(1=>'探险1',2=>'探险2',3=>'探险3',4=>'探险4');

$sttt = $_REQUEST['selectServer'];
$serverDiv=loadDiv($sttt);
if($_REQUEST['analyze']=='user'){
	$erversAndSidsArr=getSelectServersAndSids($sttt);
	$selectServer=$erversAndSidsArr['withS'];
	$selectServerids=$erversAndSidsArr['onlyNum'];

	$sids = implode(',', $selectServerids);
	$whereSql=" where sid in ($sids) ";
	$startDate = substr($_REQUEST['startDate'],0,10);
	$sDdate= date('Ymd',strtotime($startDate));
	$endDate = substr($_REQUEST['endDate'],0,10);
	$eDate =date('Ymd',strtotime($endDate)+86400);
	$whereSql .= " and date >=$sDdate and date <= $eDate ";
	$sql="select sid,date,param,users,times from stat_allserver.stat_exploreUsersAndTimes $whereSql;";
	$result = query_infobright($sql);
	$dates = array();
	$data = array();
	$total = array();
	foreach ($result['ret']['data'] as $curRow){
		$server='s'.$curRow['sid'];
		$yIndex = $curRow['date'];
		if(!in_array($yIndex, $dates)){
			$dates[]=$yIndex;
		}
		$param=$curRow['param'];
	
		$data[$server][$yIndex][$param]['users']+=$curRow['users'];
	
		$total[$yIndex][$param]['users']+=$curRow['users'];
	
		$data[$server][$yIndex][$param]['times']+=$curRow['times'];
	
		$total[$yIndex][$param]['times']+=$curRow['times'];
	}
	rsort($dates);
	if($data){
		$showData = true;
	}else {
		$alertHeader = '没有查到数据';
	}

}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>
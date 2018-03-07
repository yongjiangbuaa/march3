<?php
!defined('IN_ADMIN') && exit('Access Denied');
$showData=false;
$headerAlert='';
if(!$_REQUEST['startDate']){
	$startDate = date("Y-m-d",time()-86400*7);
}
if(!$_REQUEST['endDate'])
	$endDate = date("Y-m-d",time());

if($_REQUEST['analyze']=='user'){
	$startDate = substr($_REQUEST['startDate'],0,10);
	$sDdate= date('Ymd',strtotime($startDate));
	$endDate = substr($_REQUEST['endDate'],0,10);
	$eDate =date('Ymd',strtotime($endDate)+86400);
	$sql="select date,type,sum(pushCount) pushCount,sum(entryUsers) entryUsers,sum(entryTimes) entryTimes,sum(10entryUsers) 10entryUsers,sum(10entryTimes) 10entryTimes from stat_allserver.stat_pushInfo where date >=$sDdate and date <= $eDate group by date,type order by date desc,type;";
	$result = query_infobright($sql);
	$data=array();
	foreach ($result['ret']['data'] as $curRow){
		$data[$curRow['date']][$curRow['type']]['pushCount']=$curRow['pushCount'];
		$data[$curRow['date']][$curRow['type']]['entryUsers']=$curRow['entryUsers'];
		$data[$curRow['date']][$curRow['type']]['entryTimes']=$curRow['entryTimes'];
		$data[$curRow['date']][$curRow['type']]['10entryUsers']=$curRow['10entryUsers'];
		$data[$curRow['date']][$curRow['type']]['10entryTimes']=$curRow['10entryTimes'];
	}
	if($data){
		$showData=true;
	}else{
		$headerAlert="没有查到相应的数据信息";
	}
}


include( renderTemplate("{$module}/{$module}_{$action}") );
?>
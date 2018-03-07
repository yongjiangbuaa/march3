<?php
!defined('IN_ADMIN') && exit('Access Denied');
$showData=false;
$alertHeader='';
if(!$_REQUEST['startDate']){
	$startDate = date("Y-m-d",time()-86400*7);
}
if(!$_REQUEST['endDate']){
	$endDate = date("Y-m-d",time());
}

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
	$data=array();
	$partServerArray=array();
	$serverIdArray=array();
	$sids=implode(',', $selectServerids);
	$sql="select sid,startTime,round,serverId,partUsers,permissionUsers from stat_allserver.stat_cross_fight_users where sid in($sids) and startTime>=$startTime and startTime<$endTime order by sid,startTime,round,serverId;";
	$result = query_infobright($sql);
	foreach ($result['ret']['data'] as $row){
		if (!in_array($row['serverId'], $serverIdArray)){
			$serverIdArray[]=$row['serverId'];
		}
		$data[$row['sid']][$row['startTime']][$row['round']][$row['serverId']]['partUsers']=$row['partUsers'];
		$data[$row['sid']][$row['startTime']][$row['round']][$row['serverId']]['permissionUsers']=$row['permissionUsers'];
		$data[$row['sid']][$row['startTime']][$row['round']][$row['serverId']]['rate']=number_format($row['partUsers']/$row['permissionUsers'],2);
	}
	
	$lang = loadLanguage();
	$clientXml = loadXml('goods','goods');
	$eventNames = $goldLink;
	$goodsArray=array();
	$goldArray=array();
	foreach ($selectServer as $server=>$serInfo){
		$sql="select itemId,count(uid) goodsCount from goods_cost_record where time>=$startTime and time< $endTime and type=1 group by itemId;";
		$result=$page->executeServer($server, $sql, 3);
		foreach ($result['ret']['data'] as $row){
			$goodsArray[$lang[(int)$clientXml[$row['itemId']]['name']]] = $row['goodsCount'];
		}
		
		;
		$sql="select type,count(uid) goldCount,sum(cost) goldSum from gold_cost_record where time>=$startTime and time< $endTime and cost<0 group by type;";
		$result=$page->executeServer($server, $sql, 3);
		foreach ($result['ret']['data'] as $row){
			$goldArray[$eventNames[$row['type']]]['goldCount'] = $row['goldCount'];
			$goldArray[$eventNames[$row['type']]]['goldSum'] = -$row['goldSum'];
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
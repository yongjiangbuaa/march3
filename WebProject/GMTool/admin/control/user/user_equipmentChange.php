<?php
!defined('IN_ADMIN') && exit('Access Denied');
$showData = false;
$type = $_REQUEST['action'];
if(!$_REQUEST['start_time'])
	$start = date("Y-m-d 00:00",time()-86400*6);
if(!$_REQUEST['end_time'])
	$end = date("Y-m-d 23:59",time());
if($_REQUEST['userId'])
	$userId = $_REQUEST['userId'];
if($_REQUEST['itemId'])
	$itemId = $_REQUEST['itemId'];

$lang = loadLanguage();
$clintXml = loadXml('equipment_new','equipment_new');

if ($type=='view') {
	$startTime = $_REQUEST['start_time']?strtotime($_REQUEST['start_time'])*1000:0;
	$endTime = $_REQUEST['end_time']?strtotime($_REQUEST['end_time'])*1000:strtotime($end)*1000;
	//logstat
	//装备变化明细 type 23-新增装备   24-去除装备
	//param1 装备ID
	// 	param2
	// 	-----0   锻造消耗
	// 	-----1   分解消耗
	
	$whereSql='';
	if (!empty($itemId)){
		$whereSql=" and param1=$itemId ";
	}
	
	$sql = "select user,timeStamp,type,param1,param2,data1 from logstat where user='$userId' $whereSql and (type=23 or type=24) and timeStamp between $startTime and $endTime;";
	$result = $page->execute($sql, 3);
	$data=array();
	foreach ($result['ret']['data'] as $curRow){
		$one=array();
		$one['user']=$curRow['user'];
		$one['time']=$curRow['timeStamp']?date("Y-m-d H:i:s",$curRow['timeStamp']/1000):0;
		$equipmentName=$lang[(int)$clintXml[$curRow['param1']]['name']];
		$one['name']=$equipmentName;
		if($curRow['type']==23){
			if($curRow['param2']==0){
				$one['change']='锻造获得装备';
			}
			if($curRow['param2']==1){
				$one['change']='赠送获得装备';
			}
		}
		if($curRow['type']==24){
			if($curRow['param2']==0){
				$one['change']='锻造消耗';
			}
			if($curRow['param2']==1){
				$one['change']='分解消耗';
			}
		}
		$one['num']=$curRow['data1'];
		$data[]=$one;
	}

	if($data){
		$showData=true;
	}else {
		$headAlert="装备变化查询失败";
	}
}


include( renderTemplate("{$module}/{$module}_{$action}") );
?>
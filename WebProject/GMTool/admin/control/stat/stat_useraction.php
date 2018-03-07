<?php
!defined('IN_ADMIN') && exit('Access Denied');
$dateMax = date("Y-m-d 23:59:59",time());
$dateMin = date("Y-m-d 00:00:00",time()-7*86400);
if (isset($_REQUEST['getData'])) {
	$levelMin = $_REQUEST['levelMin'];
	$levelMax = $_REQUEST['levelMax'];
	$dateMin = strtotime($_REQUEST['dateMin'])*1000;
	$dateMax = strtotime($_REQUEST['dateMax'])*1000;
	// param1 = 3 时 求和
	$sql = "select case param1 when 3 then SUM(IFNULL(data1, 1)) else count(IFNULL(data1, 1)) end sum,"
		."param1 as func,date_format(from_unixtime(timestamp/1000),'%Y-%m-%d') as date from (select l.* from logstat l inner join userprofile u on l.user = u.uid where `timestamp` >= $dateMin and `timestamp` < $dateMax and type = 0 and u.level >= $levelMin and u.level <= $levelMax) a group by date,param1";
	$ret = $page->execute($sql,3);
	$sqlDatas = $result['ret'];
	//DAU
	$DAUSql = "select count(distinct uid) as count,date_format(from_unixtime(`time`/1000),'%Y-%m-%d') as date from stat_login where time BETWEEN $dateMin and $dateMax group by date order by time desc";
	$DAUret = $page->execute($DAUSql,3);
	$dauArr = $DAUret['ret']['data'];
	
	$nameLink['func'] = '功能';
	$nameLinkSort = array_keys($nameLink);
	array_unshift($funcList, 'DAU','点击创建邮件按钮','打开邮件列表界面');
	foreach ($funcList as $key=>$func) {
		$eventAll[$key]['func'] = $func;
	}
	foreach ($ret['ret']['data'] as $curRow){
		$xindex = $curRow['date'];
		$nameLink[$xindex] = $xindex;
		$nameLinkSort[$dateMax/1000-strtotime($xindex)] = $xindex;
		$yIndex = $curRow['func'] + 3;
		$eventAll[$yIndex][$xindex] = $curRow['sum'];
	}
	//邮件面板点击-总人数
	$sql = "SELECT sum(data1) click ,COUNT(1) times, date_format(from_unixtime(`timeStamp`/1000),'%Y-%m-%d')  date
	from logstat where type =2 and param1=8 and `timeStamp`>= $dateMin and  `timeStamp` <= $dateMax group by date";
	$result = $page->execute($sql,3);
	foreach ($result['ret']['data'] as $value){
		$eventAll[1][$value['date']] = $value['click'];
		$eventAll[2][$value['date']] = $value['times'];
	}
	//dau
	foreach ($dauArr as $value){
		$eventAll[0][$value['date']] = $value['count'];
	}
	
	printStat($eventAll,$nameLink,$nameLinkSort,$hightLight);
	exit();
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
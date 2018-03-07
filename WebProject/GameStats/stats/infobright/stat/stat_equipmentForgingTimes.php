<?php
if(isset($_REQUEST['fixdate'])){
	$req_date_end = $_REQUEST['fixdate'];
}else{
	$req_date_end = date('Ymd',time());
}
$req_date_end = str_replace('-', '', $req_date_end);


$beforeDay=date('Ymd',strtotime($req_date_end)-86400);
$endTime=strtotime($req_date_end)*1000;
$startTime=$endTime-86400000;

$yearMonth=array();
if(date('Ym',$startTime/1000)==date('Ym',$endTime/1000)){
	$yearMonth[]=date('Y',$startTime/1000).'_'.(date('m',$startTime/1000)-1);
}else {
	$yearMonth[]=date('Y',$startTime/1000).'_'.(date('m',$startTime/1000)-1);
	$yearMonth[]=date('Y',$endTime/1000).'_'.(date('m',$endTime/1000)-1);
}


$statdb_allserver = IB_DB_NAME_STAT_ALLSERVER;

$server_list = get_db_list();
$slave_db_list = array();
foreach ($server_list as $one) {
	$slave_db_list[$one['db_id']] =$one;
}
$st=time();
$link = mysqli_connect($slave_db_list[SERVER_ID]['slave_ip_inner'],'root','t9qUzJh1uICZkA',$slave_db_list[SERVER_ID]['dbname'],$slave_db_list[SERVER_ID]['port']);
//dau
$dauData=array();
foreach ($yearMonth as $ym){
	$sql="select DATE_FORMAT(FROM_UNIXTIME(l.`time`/1000),'%Y%m%d') date, ub.level blevel,count(distinct l.uid) dau from stat_login_$ym l inner join user_building ub on l.uid=ub.uid where ub.itemId=400000 and l.time>=$startTime and l.time<$endTime group by date,blevel;";
	//echo $sql."\n";
	$res = mysqli_query($link,$sql);
	while($row = mysqli_fetch_assoc($res)){
		if($row['date']!=$beforeDay){
			continue;
		}
		$dauData[$row['date']][$row['blevel']]+=$row['dau'];
	}
}

/* $steelCostData=array();
$cdCostData=array();
$materialCostData=array();
//$sql="select DATE_FORMAT(FROM_UNIXTIME(gcr.`time`/1000),'%Y%m%d') date, ub.level blevel,gcr.type type,gcr.param1 param1,sum(gcr.cost) sumCost from (select time,userId,cost,type,param1 from gold_cost_record where time>=$startTime and time<$endTime and (type=55 and param1=4) or type=66 or (type=12 and param1=200603)) gcr inner join user_building ub on gcr.userId=ub.uid where ub.itemId=400000 group by date,blevel,type,param1;";
$sql="select DATE_FORMAT(FROM_UNIXTIME(gcr.`time`/1000),'%Y%m%d') date, ub.level blevel,gcr.type type,gcr.param1 param1,sum(gcr.cost) sumCost from (select time,userId,cost,type,param1 from gold_cost_record where type=55 and param1=4 and time>=$startTime and time<$endTime union select time,userId,cost,type,param1 from gold_cost_record where type=66 and time>=$startTime and time<$endTime union select time,userId,cost,type,param1 from gold_cost_record where type=12 and param1=200603 and time>=$startTime and time<$endTime) gcr inner join user_building ub on gcr.userId=ub.uid where ub.itemId=400000 group by date,blevel;";
$res = mysqli_query($link,$sql);
echo $sql;
while($row = mysqli_fetch_assoc($res)){
	if($row['type']=='55' && $row['param1']=='4'){
		$steelCostData[$row['date']][$row['blevel']]+=$row['sumCost'];
	}
	if($row['type']=='66'){
		$cdCostData[$row['date']][$row['blevel']]+=$row['sumCost'];
	}
	if($row['type']=='12' && $row['param1']=='200603'){
		$materialCostData[$row['date']][$row['blevel']]+=$row['sumCost'];
	}
} */

//祭祀刚才金币消耗
$steelCostData=array();
$sql="select DATE_FORMAT(FROM_UNIXTIME(gcr.`time`/1000),'%Y%m%d') date, ub.level blevel,sum(gcr.cost) steelCost from (select time,userId,cost from gold_cost_record where type=55 and param1=4 and time>=$startTime and time<$endTime) gcr inner join user_building ub on gcr.userId=ub.uid where ub.itemId=400000 group by date,blevel;";

$res = mysqli_query($link,$sql);
while($row = mysqli_fetch_assoc($res)){
	$steelCostData[$row['date']][$row['blevel']]+=$row['steelCost'];
}
//秒锻造CD金币消耗
$cdCostData=array();
$sql="select DATE_FORMAT(FROM_UNIXTIME(gcr.`time`/1000),'%Y%m%d') date, ub.level blevel,sum(gcr.cost) cdCost from (select time,userId,cost from gold_cost_record where type=66 and time>=$startTime and time<$endTime) gcr inner join user_building ub on gcr.userId=ub.uid where ub.itemId=400000 group by date,blevel;";
$res = mysqli_query($link,$sql);
while($row = mysqli_fetch_assoc($res)){
	$cdCostData[$row['date']][$row['blevel']]+=$row['cdCost'];
}
//购买小材料宝箱金币消耗
$materialCostData=array();
$sql="select DATE_FORMAT(FROM_UNIXTIME(gcr.`time`/1000),'%Y%m%d') date, ub.level blevel,sum(gcr.cost) materialCost from (select time,userId,cost from gold_cost_record where type=12 and param1=200603 and time>=$startTime and time<$endTime) gcr inner join user_building ub on gcr.userId=ub.uid where ub.itemId=400000 group by date,blevel;";
$res = mysqli_query($link,$sql);
while($row = mysqli_fetch_assoc($res)){
	$materialCostData[$row['date']][$row['blevel']]+=$row['materialCost'];
}

$sql="select DATE_FORMAT(FROM_UNIXTIME(lg.`timeStamp`/1000),'%Y%m%d') date, ub.level blevel,count(distinct lg.user) forgingUsers,count(lg.user) forgingTimes from user_building ub inner join (select user,timeStamp from logstat where type=23 and timeStamp>=$startTime and timeStamp<$endTime) lg on ub.uid=lg.user where ub.itemId=400000 group by date,blevel;";
$res = mysqli_query($link,$sql);
$usersArray=array();
$timesArray=array();
while($row = mysqli_fetch_assoc($res)){
	$usersArray[$row['date']][$row['blevel']]+=$row['forgingUsers'];
	$timesArray[$row['date']][$row['blevel']]+=$row['forgingTimes'];
}

$records = array();
foreach ($dauData as $dateKey=>$blevelValue){
	foreach ($blevelValue as $blevelKey=>$value){
		$one=array();
		$one['date']=$dateKey;
		$one['blevel']=$blevelKey;
		$one['dau']=$value;
		$one['forgingUsers']=intval($usersArray[$dateKey][$blevelKey]);
		$one['forgingTimes']=intval($timesArray[$dateKey][$blevelKey]);
		$one['steelCost']=intval($steelCostData[$dateKey][$blevelKey]);
		$one['cdCost']=intval($cdCostData[$dateKey][$blevelKey]);
		$one['materialCost']=intval($materialCostData[$dateKey][$blevelKey]);
		$records[] = $one;
	}
}
foreach ($records as $fieldvalue) {
	$keys = array_keys($fieldvalue);
	$updKv = buildUpdateSql($fieldvalue);
	$f = join(',', $keys);
	$str = join(',', $fieldvalue);
	$f = 'sid,'.$f;
	$str = SERVER_ID.','.$str;

	$insertSql = "INSERT into %s ($f) VALUES "." ($str) ";
	$ondup = 'ON DUPLICATE KEY UPDATE '.$updKv;
	$insertSql .= " $ondup;";

	$db_tbl = "$statdb_allserver.stat_equipmentForgingTimes";
	$sql = sprintf($insertSql, $db_tbl);
	//echo $sql."\n";
	query_infobright($sql);
}
$en=time();
echo ($en-$st);

<?php
if(isset($_REQUEST['fixdate'])){
	$req_date_end = $_REQUEST['fixdate'];
}else{
	$req_date_end = date('Ymd',time());
}
$req_date_end = str_replace('-', '', $req_date_end);

$endTime=strtotime($req_date_end)*1000;
$startTime=$endTime-86400000;
$beforeDay=date('Ymd',$startTime/1000);
$before3Day=date('Ymd',($endTime/1000-86400*3));

$statdb_allserver = IB_DB_NAME_STAT_ALLSERVER;

$server_list = get_db_list();
$slave_db_list = array();
foreach ($server_list as $one) {
	$slave_db_list[$one['db_id']] =$one;
}
$records = array();
$link = mysqli_connect($slave_db_list[SERVER_ID]['slave_ip_inner'],'root','t9qUzJh1uICZkA',$slave_db_list[SERVER_ID]['dbname'],$slave_db_list[SERVER_ID]['port']);
//点击进入次数、人数和10分钟之内进入次数、人数
$sql="select DATE_FORMAT(FROM_UNIXTIME(`time`/1000),'%Y%m%d') date,type,count(distinct(uid)) entryUsers, count(uid) entryTimes,status from push_record where DATE_FORMAT(FROM_UNIXTIME(`time`/1000),'%Y%m%d')>= $before3Day and DATE_FORMAT(FROM_UNIXTIME(`time`/1000),'%Y%m%d')<= $beforeDay group by date,type,status;";
$res = mysqli_query($link,$sql);
$entryArray=array();
while($row = mysqli_fetch_assoc($res)){
	if($row['status']==1){
		$entryArray[$row['date']][$row['type']]['entryUsers']+=$row['entryUsers'];
		$entryArray[$row['date']][$row['type']]['entryTimes']+=$row['entryTimes'];
	}elseif ($row['status']==2){
		$entryArray[$row['date']][$row['type']]['10entryUsers']+=$row['entryUsers'];
		$entryArray[$row['date']][$row['type']]['10entryTimes']+=$row['entryTimes'];
	}
}

//推送数量
$sql="select DATE_FORMAT(FROM_UNIXTIME(`time`/1000),'%Y%m%d') date,type,count(uid) pushCount from push_record where DATE_FORMAT(FROM_UNIXTIME(`time`/1000),'%Y%m%d')>= $before3Day and DATE_FORMAT(FROM_UNIXTIME(`time`/1000),'%Y%m%d')<= $beforeDay group by date,type;";
$res = mysqli_query($link,$sql);
while($row = mysqli_fetch_assoc($res)){
	$one=array();
	$one['date']=$row['date'];
	$one['type']=$row['type'];
	$one['pushCount']=$row['pushCount'];
	$one['entryUsers']=intval($entryArray[$row['date']][$row['type']]['entryUsers']);
	$one['entryTimes']=intval($entryArray[$row['date']][$row['type']]['entryTimes']);
	$one['10entryUsers']=intval($entryArray[$row['date']][$row['type']]['10entryUsers']);
	$one['10entryTimes']=intval($entryArray[$row['date']][$row['type']]['10entryTimes']);
	$records[] = $one;
}
$client = getInfobrightConnect('stat_pushInfo.php');
if(!$client){
	echo 'mysql error stat_pushInfo.php'.PHP_EOL;
	return;
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

	$db_tbl = "$statdb_allserver.stat_pushInfo";
	$sql = sprintf($insertSql, $db_tbl);
	//echo $sql."\n";
	query_infobright_new($client,$sql);
}

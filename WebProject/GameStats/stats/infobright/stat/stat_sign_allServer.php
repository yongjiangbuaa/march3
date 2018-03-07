<?php
if(isset($_REQUEST['fixdate'])){
	$req_date_end = $_REQUEST['fixdate'];
}else{
	$req_date_end = date('Ymd',(time()-86400));
}
$req_date_end = str_replace('-', '', $req_date_end);

//$endTime=date('Ymd',(strtotime($req_date_end)-86400));
$endTime=$req_date_end;
$startTime=$endTime;

$snapshotdb = IB_DB_NAME_SNAPSHOT;
$statdb = IB_DB_NAME_STAT;
$statdb_allserver = IB_DB_NAME_STAT_ALLSERVER;

$client = getInfobrightConnect('stat_sign_allServer.php');
if(!$client){
	echo 'mysql error stat_sign_allServer.php'.PHP_EOL;
	return;
}

//$sql="select sif.date date,u14l.day day,count(distinct sif.uid) requestCount from snapshot_global.sign_in_feed sif inner join $snapshotdb.user_14day_login u14l on sif.uid=u14l.uid where sif.type=1 and u14l.type=1 and sif.date=$endTime group by date,day order by date desc,day;";
$sql="select sif.date date,u14l.day day,count(distinct sif.uid) requestCount from snapshot_global.sign_in_feed sif inner join $snapshotdb.user_14day_login u14l on sif.uid=u14l.uid where sif.type=1 and u14l.type=1 and sif.date=$endTime group by date,day order by date desc,day;";
$ret = query_infobright_new($client,$sql);
$requestArray=array();
foreach( $ret as $value){
	$requestArray[$value['date']][$value['day']]=$value['requestCount'];
}
$signArray=array();
$sql="select date,day,count(distinct uid) signCount from $snapshotdb.user_14day_login where type=1 and date=$endTime group by date ,day order by date,day;";
$ret = query_infobright_new($client,$sql);
foreach( $ret as $value){
	$signArray[$value['date']][$value['day']]=$value['signCount'];
}


$sql="select l.date date,u14l.day day,count(distinct l.uid) dau from $snapshotdb.user_14day_login u14l inner join $snapshotdb.stat_login l on u14l.uid=l.uid where u14l.type=1 and l.date=$endTime group by date,day order by date desc,day;";
$ret = query_infobright_new($client,$sql);
foreach( $ret as $value){
	$one=array();
	$one['date']=$value['date'];
	$one['day']=$value['day'];
	$one['request']=intval($requestArray[$value['date']][$value['day']]);
	$one['signCount']=intval($signArray[$value['date']][$value['day']]);
	$one['dau']=$value['dau'];;
	$records[] = $one;
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

	$db_tbl = "$statdb_allserver.stat_sign";
	$sql = sprintf($insertSql, $db_tbl);
	query_infobright_new($client,$sql);
}

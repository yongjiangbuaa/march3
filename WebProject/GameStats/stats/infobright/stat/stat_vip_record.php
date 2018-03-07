<?php 
//日期	平台	国家		新注册	日活跃  付费dau
//date	pf	country	        reg		dau     paid_dau

if(isset($_REQUEST['fixdate'])){
	$req_date_end = $_REQUEST['fixdate'];
}else{
	$req_date_end = date('Ymd');
}
$req_date_end = str_replace('-', '', $req_date_end);

$endTime=date('Ymd',strtotime($req_date_end)-86400);
$startTime=$endTime;

$snapshotdb = IB_DB_NAME_SNAPSHOT;
$statdb = IB_DB_NAME_STAT;
$statdb_allserver = IB_DB_NAME_STAT_ALLSERVER;

$s = microtime(true);
$client = getInfobrightConnect('stat_vip_record.php');
if(!$client){
	echo 'mysql error stat_vip_record.php'.PHP_EOL;
	return;
}

$sql="select count(distinct l.uid) untlogin,l.date loginDate,lr.param1 vipLevel from $snapshotdb.stat_login l inner join $snapshotdb.logrecord lr on l.uid=lr.user and lr.category=14 where l.date=$endTime and lr.date=$endTime group by loginDate,vipLevel;";
$ret = query_infobright_new($client,$sql);
$vipLevelArray=array();
foreach( $ret as $value){
	$vipLevelArray[$value['loginDate']][$value['vipLevel']]=$value['untlogin'];
}

$sql="select count(distinct l.uid) untActive,l.date loginDate,lr.param1 vipLevel from $snapshotdb.stat_login l inner join $snapshotdb.logrecord lr on l.uid=lr.user and lr.category=14 and lr.type=0 where l.date=$endTime and lr.date=$endTime  group by loginDate,vipLevel;";
$ret = query_infobright_new($client,$sql);
$records = array();
foreach( $ret as $value){
	$one=array();
	$one['date']=$value['loginDate'];
	$one['vipLevel']=$value['vipLevel'];
	$one['untlogin']=intval($vipLevelArray[$value['loginDate']][$value['vipLevel']]);
	$one['untActive']=$value['untActive'];
	$records[] = $one;
}


//print_r($records);
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
	
	$db_tbl = "$statdb_allserver.stat_vip_record";
	$sql = sprintf($insertSql, $db_tbl);
	query_infobright_new($client,$sql);
}





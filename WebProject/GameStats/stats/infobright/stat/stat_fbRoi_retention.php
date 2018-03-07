<?php 

$span = 30;
if(isset($_REQUEST['fixdate'])){
	$req_date_end = $_REQUEST['fixdate'];
}else{
	$req_date_end = date('Ymd',time());
}
$req_date_end = str_replace('-', '', $req_date_end);
$req_date_start = date("Ymd", strtotime("-$span day",strtotime($req_date_end)));

$snapshotdb = IB_DB_NAME_SNAPSHOT;
$statdb = IB_DB_NAME_STAT;
$statdb_allserver = IB_DB_NAME_STAT_ALLSERVER;

//
$s = microtime(true);
$records=array();//注意初始化位置

//$sql="select fa.src adsrc,DATE_FORMAT(FROM_UNIXTIME(fa.`time`),'%Y%m%d') regDateTime,lf.date loginDate,count(distinct fa.uid) countUsers from snapshot_global.fb_ad fa left join $snapshotdb.stat_login_full lf on fa.uid=lf.deviceId where lf.pf='facebook' and lf.date >=$req_date_start and lf.date <= $req_date_end and DATE_FORMAT(FROM_UNIXTIME(fa.`time`),'%Y%m%d') >=$req_date_start and DATE_FORMAT(FROM_UNIXTIME(fa.`time`),'%Y%m%d')<= $req_date_end group by adsrc,regDateTime,loginDate;";

//$sql="select fa.src adsrc,uf.date regDateTime,lf.date loginDate,count(distinct fa.uid) countUsers from snapshot_global.fb_ad fa inner join $snapshotdb.userprofile_full uf on fa.uid=uf.deviceId inner join $snapshotdb.stat_login_full lf on uf.uid=lf.uid where lf.date >=$req_date_start and lf.date <= $req_date_end and uf.date >=$req_date_start and uf.date<= $req_date_end group by adsrc,regDateTime,loginDate;";

$sql="select fa.src adsrc,DATE_FORMAT(FROM_UNIXTIME(fa.`time`),'%Y%m%d') regDateTime,lf.date loginDate,count(distinct fa.uid) countUsers from snapshot_global.fb_ad fa inner join $snapshotdb.account_new_full anf on fa.uid=anf.deviceId inner join $snapshotdb.stat_login_full lf on anf.uid=lf.uid where lf.date >=$req_date_start and lf.date <= $req_date_end and DATE_FORMAT(FROM_UNIXTIME(fa.`time`),'%Y%m%d') >=$req_date_start and DATE_FORMAT(FROM_UNIXTIME(fa.`time`),'%Y%m%d')<= $req_date_end group by adsrc,regDateTime,loginDate 
union select fa.src adsrc,DATE_FORMAT(FROM_UNIXTIME(fa.`time`),'%Y%m%d') regDateTime,lf.date loginDate,count(distinct fa.uid) countUsers from snapshot_global.fb_ad fa inner join $snapshotdb.account_new_full anf on fa.uid=anf.facebookAccount inner join $snapshotdb.stat_login_full lf on anf.uid=lf.uid where lf.date >=$req_date_start and lf.date <= $req_date_end and DATE_FORMAT(FROM_UNIXTIME(fa.`time`),'%Y%m%d') >=$req_date_start and DATE_FORMAT(FROM_UNIXTIME(fa.`time`),'%Y%m%d')<= $req_date_end group by adsrc,regDateTime,loginDate;";
//echo $sql."\n";

$result = query_infobright($sql);
$data=array();
foreach ($result as $curRow){
	$data[$curRow['adsrc']][$curRow['regDateTime']][$curRow['loginDate']]+=$curRow['countUsers'];
}
foreach ($data as $adKey=>$regAndLoginValue){
	foreach ($regAndLoginValue as $regDateKey=>$loginValue){
		$regdt=strtotime($regDateKey);
		$one=array();
		$one['adsrc']="'$adKey'";
		$one['regDate']=$regDateKey;
		$one['reg_all']=intval($loginValue[$regDateKey]);
		ksort($loginValue);
		foreach ($loginValue as $logDateKey=>$value){
			if($logDateKey == $regDateKey) continue;
			$logindt = strtotime($logDateKey);
			$days = round(($logindt - $regdt) / 86400);
			if($days<0 || !$value) continue;
			$one["r$days"] = $value;
		}
		$records[]=$one;
	}
}

//print_r($records);
//echo "\n".count($records)."\n";
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
	
	$db_tbl = "$statdb_allserver.stat_fbRoi_retention";
	$sql = sprintf($insertSql, $db_tbl);
	query_infobright($sql);
}


// $sql="select fa.src adsrc,p.date,sum(p.spend) paysum from snapshot_global.fb_ad fa left join $snapshotdb.userprofile u on fa.uid=u.deviceId left join $snapshotdb.paylog p on u.uid=p.uid where p.date between $req_date_start and $req_date_end and DATE_FORMAT(FROM_UNIXTIME(fa.`time`),'%Y%m%d') between >=$req_date_start and DATE_FORMAT(FROM_UNIXTIME(fa.`time`),'%Y%m%d')<= $req_date_end group by adsrc,date;";
// $result = query_infobright($sql);
// foreach ($result as $curRow){
// 	$paydata[$curRow['adsrc']]+=$curRow['paysum'];
// 	$totalPay+=$curRow['paysum'];
// }

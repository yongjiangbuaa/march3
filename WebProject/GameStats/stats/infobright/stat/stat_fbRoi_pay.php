<?php 

if(isset($_REQUEST['fixdate'])){
	$req_date_end = $_REQUEST['fixdate'];
	$span = 0;
}else{
	$req_date_end = date('Ymd',time());
	$span = 1;
}
$req_date_end = str_replace('-', '', $req_date_end);
$req_date_start = date("Ymd", strtotime("-$span day",strtotime($req_date_end)));

$snapshotdb = IB_DB_NAME_SNAPSHOT;
$statdb = IB_DB_NAME_STAT;
$statdb_allserver = IB_DB_NAME_STAT_ALLSERVER;

$records=array();//注意初始化位置

$s = microtime(true);

//$sql="select fa.src adsrc,DATE_FORMAT(FROM_UNIXTIME(fa.`time`),'%Y%m%d') regDateTime,p.date,sum(p.spend) paysum from snapshot_global.fb_ad fa left join $snapshotdb.userprofile u on fa.uid=u.deviceId left join $snapshotdb.paylog p on u.uid=p.uid left join $snapshotdb.stat_reg r on u.uid=r.uid where r.pf='facebook' and p.date between $req_date_start and $req_date_end group by adsrc,regDateTime,date;";

$sql="select fa.src adsrc,DATE_FORMAT(FROM_UNIXTIME(fa.`time`),'%Y%m%d') regDateTime,p.date,sum(p.spend) paysum from snapshot_global.fb_ad fa inner join $snapshotdb.account_new_full anf on fa.uid=anf.deviceId inner join $snapshotdb.paylog p on anf.uid=p.uid where p.date between $req_date_start and $req_date_end and p.pf='facebook' group by adsrc,regDateTime,date 
union select fa.src adsrc,DATE_FORMAT(FROM_UNIXTIME(fa.`time`),'%Y%m%d') regDateTime,p.date,sum(p.spend) paysum from snapshot_global.fb_ad fa inner join $snapshotdb.account_new_full anf on fa.uid=anf.facebookAccount inner join $snapshotdb.paylog p on anf.uid=p.uid where p.date between $req_date_start and $req_date_end and p.pf='facebook' group by adsrc,regDateTime,date;";
//echo $sql."\n";

$result = query_infobright($sql);
$paydata=array();
foreach ($result as $curRow){
	if($curRow['regDateTime']>$curRow['date']){
		continue;
	}
	$paydata[$curRow['adsrc']][$curRow['regDateTime']][$curRow['date']]+=$curRow['paysum'];
}

foreach ($paydata as $adKey=>$regAndLoginValue){
	foreach ($regAndLoginValue as $regDateKey=>$payValue){
		foreach ($payValue as $payDateKey=>$value){
			$one=array();
			$one['adsrc']="'$adKey'";
			$one['regDate']=$regDateKey;
			$one['payDate']=$payDateKey;
			$one['payNum']=$value;
			$records[]=$one;
		}
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
	
	$db_tbl = "$statdb_allserver.stat_fbRoi_pay";
	$sql = sprintf($insertSql, $db_tbl);
	query_infobright($sql);
}




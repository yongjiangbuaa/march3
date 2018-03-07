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
//
$s = microtime(true);
// **************
$records = array();

$sql="select p.date,p.productId,r.country,r.pf,count(p.productId) num from $snapshotdb.paylog p inner join $snapshotdb.stat_reg r on p.uid=r.uid where p.date between $req_date_start and $req_date_end GROUP BY date,productId,country,pf;";
$ret=query_infobright($sql);
$result=array();
foreach ($ret as $curRow){
	if($curRow['pf']==null){
		$pf='roll';
		$country='roll';
	}else{
		$pf=$curRow['pf'];
		if($curRow['country']==null){
			$country = "Unknown";
		}else{
			$country = $curRow['country'];
		}
	}
	$one = array();
	$one['date']=$curRow['date'];
	$one['productId']="'{$curRow['productId']}'";
	$one['pf']="'{$pf}'";
	$one['country']="'{$country}'";
	$one['num']=$curRow['num'];
	$records[] = $one;
	
}

//print_r($records);
/////////////

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

	$db_tbl = "$statdb_allserver.stat_exchange_pf_country";
	$sql = sprintf($insertSql, $db_tbl);
	query_infobright($sql);
}



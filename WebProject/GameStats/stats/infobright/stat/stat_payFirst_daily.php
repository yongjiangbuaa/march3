<?php 
//日期	平台	付费人数		付费次数		付费总额	首充人数	
//date	pf	uniquePay	totalPay	total	cnt

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

$s = microtime(true);

// ************** get countrys' DAU
$records = array();
$sql = "SELECT p.date, p.pf, count(DISTINCT(p.uid)) as uniquePay,count(p.uid) as totalPay,sum(p.spend) as total, count(fp.uid) as cnt from $snapshotdb.paylog p inner join (select uid, min(date) date from $snapshotdb.paylog group by uid) as fp on p.uid=fp.uid where p.date between $req_date_start and $req_date_end group by date,pf";
$ret = query_infobright($sql);
foreach( $ret as $value){
	$one = array();
	$one['date'] = $value['date'];
	$one['pf'] = "'{$value['pf']}'";
	$one['uniquePay'] = $value['uniquePay'];
	$one['totalPay'] = $value['totalPay'];
	$one['total'] =$value['total'];
	$one['cnt'] =$value['cnt'];
	$records[] = $one;
};

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
	
	$db_tbl = "$statdb_allserver.stat_payFirst_daily";
	$sql = sprintf($insertSql, $db_tbl);
	query_infobright($sql);
}





<?php 
//日期	日活跃	新注册数	有效注册	次日留存	3日留存	4日留存	5日留存	6日留存	7日留存
//date	dau	reg_all	reg_valid	r1	r2	r3	r4	r5	r6

$span = 30;
if(isset($_REQUEST['fixdate'])){
	$req_date_fix = $_REQUEST['fixdate'];
}else{
	$req_date_fix = date('Ymd',time());
}
$req_date_fix = str_replace('-', '', $req_date_fix);
$req_date_start = $req_date_fix;
$req_date_end = date("Ymd", strtotime("$span day",strtotime($req_date_start)));

$snapshotdb = IB_DB_NAME_SNAPSHOT;
$statdb = IB_DB_NAME_STAT;
$statdb_allserver = IB_DB_NAME_STAT_ALLSERVER;

// initialize
$loginArrDau = array();
$loginArrRegRet = array();
$date = $req_date_start;
while ($date <= $req_date_fix) {//only fix date
	$loginArrDau[$date] = 0;
	$loginArrRegRet[$date][$date] = 0;// new register
	$date2 = date('Ymd',strtotime("+1 day",strtotime($date)));
	while ($date2 <= $req_date_end) {
		$loginArrRegRet[$date][$date2] = 0; // retetion
		$date2 = date('Ymd',strtotime("+1 day",strtotime($date2)));
	}
	$date = date('Ymd',strtotime("+1 day",strtotime($date)));
}

//
$s = microtime(true);

// ************** get DAU
$sql = "
	select date, count(distinct uid) ucnt
	from $snapshotdb.stat_login 
	where date=$req_date_fix
	group by date
	order by date
";
$ret = query_infobright($sql);
foreach ($ret as $fields){
	$loginArrDau[$fields['date']] += $fields['ucnt'];
}
// print_r($loginArr);


// ************** get retention
$sql = "
	select r.date as regdate, l.date as relogindate, count(distinct(r.uid)) as ucnt from 
	(select date, uid from $snapshotdb.stat_reg where date=$req_date_fix) r 
	inner join 
	(select date, uid from $snapshotdb.stat_login where date >= $req_date_start and date <= $req_date_end ) l
	on r.uid = l.uid 
	group by regdate,relogindate 
	order by regdate,relogindate ;
";
$ret = query_infobright($sql);
foreach ($ret as $fields){
	$loginArrRegRet[$fields['regdate']][$fields['relogindate']] += $fields['ucnt'];
}
// print_r($loginArrRegRet);

/////////////
$records = array();
ksort($loginArrDau);
foreach ($loginArrDau as $date=>$dau) {
	$regdt = strtotime($date);
	$one = array();
	$one['date'] = $date;
	$one['dau'] = $dau;
	$one['reg_all'] = $loginArrRegRet[$date][$date];
	$one['reg_valid'] = $loginArrRegRet[$date][$date];//TODO
	for ($i = 1; $i <= $span; $i++) {
		$one['r'.$i] = 0;
	}

	$r = $loginArrRegRet[$date];
	ksort($r);
	foreach ($r as $date2=>$newreg) {
		if($date2 == $date) continue;
		$logindt = strtotime($date2);
		$days = round(($logindt - $regdt) / 86400);
		if($days<0) continue;
		$one["r$days"] = $newreg;
	}
	
	$records[] = $one;
}
// print_r($records);

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
	
	$db_tbl = "$statdb_allserver.stat_retention_daily";
	$sql = sprintf($insertSql, $db_tbl);
	echo $sql,"\n";
// 	query_infobright($sql);
}

function buildUpdateSql($kv){
	$all = array();
	foreach ($kv as $key => $value) {
		$all[] = "$key=$value";
	}
	return implode(',', $all);
}




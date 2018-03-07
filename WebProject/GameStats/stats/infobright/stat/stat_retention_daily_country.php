<?php 
//日期	日活跃	新注册数	有效注册	次日留存	3日留存	4日留存	5日留存	6日留存	7日留存
//date	dau	reg_all	reg_valid	r1	r2	r3	r4	r5	r6

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

// ************** get countrys' DAU
$loginArrDau = array();
$sql = "
	select l.date as date, r.country as country, count(distinct(l.uid)) as ucnt from 
	(select date, uid from $snapshotdb.stat_login where date >= $req_date_start and date <= $req_date_end ) l
	inner join 
	(select uid, country from $snapshotdb.stat_reg where date <= $req_date_end) r 
	on r.uid = l.uid
	group by date,country ;
";
$ret = query_infobright($sql);
foreach ($ret as $fields){
	$country = $fields['country']?$fields['country']:'Unknown';
	$loginArrDau[$fields['date']][$country] += $fields['ucnt'];
}
// print_r($loginArrDau);


// ************** get retention
$loginArrRegRet = array();
$sql = "
	select r.date as regdate, l.date as relogindate, r.country as country, count(distinct(r.uid)) as ucnt from 
	(select date, country, uid from $snapshotdb.stat_reg where date >= $req_date_start and date <= $req_date_end) r 
	inner join 
	(select date, uid from $snapshotdb.stat_login where date >= $req_date_start and date <= $req_date_end ) l
	on r.uid = l.uid 
	group by regdate,relogindate,country ;
";
$ret = query_infobright($sql);
foreach ($ret as $fields){
	$country = $fields['country']?$fields['country']:'Unknown';
	$loginArrRegRet[$fields['regdate']][$country][$fields['relogindate']] += $fields['ucnt'];
}
// print_r($loginArrRegRet);

/////////////
$records = array();
ksort($loginArrDau);
foreach ($loginArrDau as $date=>$countrydau) {
	foreach ($countrydau as $country=>$dau) {
		$regdt = strtotime($date);
		$one = array();
		$one['date'] = $date;
		$one['country'] = "'$country'";
		$one['dau'] = $dau;
		$one['reg_all'] = intval($loginArrRegRet[$date][$country][$date]);
		$one['reg_valid'] = intval($loginArrRegRet[$date][$country][$date]);//TODO
	
		$r = $loginArrRegRet[$date][$country];
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
	
	$db_tbl = "$statdb_allserver.stat_retention_daily_country";
	$sql = sprintf($insertSql, $db_tbl);
// 	echo $sql,"\n";
	query_infobright($sql);
}


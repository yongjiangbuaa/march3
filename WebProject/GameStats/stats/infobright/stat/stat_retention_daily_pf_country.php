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
if (SERVER_ID>=530){
	$sql = "
	select l.date as date, r.pf as pf, r.country as country, count(distinct(l.uid)) as ucnt from 
	(select date, uid,deviceId from $snapshotdb.stat_login_full where date >= $req_date_start and date <= $req_date_end ) l 
	inner join 
	(select uid, pf, country from $snapshotdb.stat_reg where date <= $req_date_end) r 
	on r.uid = l.uid 
	left join snapshot_global.cheat_deviceId cd 
	on l.deviceId=cd.deviceId where cd.deviceId is null 
	group by date,pf,country;";
}else {
	$sql = "
		select l.date as date, r.pf as pf, r.country as country, count(distinct(l.uid)) as ucnt from 
		(select date, uid from $snapshotdb.stat_login where date >= $req_date_start and date <= $req_date_end ) l 
		inner join 
		(select uid, pf, country from $snapshotdb.stat_reg where date <= $req_date_end) r 
		on r.uid = l.uid 
		group by date,pf,country;";
}
//echo $sql."\n";
$ret = query_infobright($sql);
foreach ($ret as $fields){
	if($fields['pf']==null){
		$pf='roll';
		$country='roll';
	}else{
		$pf=$fields['pf'];
		if($fields['country']==null){
			$country = "Unknown";
		}else{
			$country = $fields['country'];
		}
	}
	$loginArrDau[$fields['date']][$pf][$country] += $fields['ucnt'];
}
// print_r($loginArrDau);


// ************** get retention
$loginArrRegRet = array();
if (SERVER_ID>=530){
	$sql = "
	select r.date as regdate, l.date as relogindate, r.pf as pf, r.country as country, count(distinct(r.uid)) as ucnt from 
	(select date, pf, country, uid from $snapshotdb.stat_reg where date >= $req_date_start and date <= $req_date_end) r 
	inner join 
	(select date, uid,deviceId from $snapshotdb.stat_login_full where date >= $req_date_start and date <= $req_date_end ) l 
	on r.uid = l.uid 
	left join snapshot_global.cheat_deviceId cd 
	on l.deviceId=cd.deviceId where cd.deviceId is null 
	group by regdate,relogindate,pf,country;";
}else {
	$sql = "
		select r.date as regdate, l.date as relogindate, r.pf as pf, r.country as country, count(distinct(r.uid)) as ucnt from 
		(select date, pf, country, uid from $snapshotdb.stat_reg where date >= $req_date_start and date <= $req_date_end) r 
		inner join 
		(select date, uid from $snapshotdb.stat_login where date >= $req_date_start and date <= $req_date_end ) l 
		on r.uid = l.uid 
		group by regdate,relogindate,pf,country;";
}
$ret = query_infobright($sql);
// echo $sql."\n";
foreach ($ret as $fields){
	if($fields['pf']==null){
		$pf='roll';
		$country='roll';
	}else{
		$pf=$fields['pf'];
		if($fields['country']==null){
			$country = "Unknown";
		}else{
			$country = $fields['country'];
		}
	}
	$loginArrRegRet[$fields['regdate']][$pf][$country][$fields['relogindate']] += $fields['ucnt'];
}
// print_r($loginArrRegRet);

/////////////
$records = array();
ksort($loginArrDau);
foreach ($loginArrDau as $date=>$pfCountrydau) {
	foreach ($pfCountrydau as $pfkey=>$countrydau){
		foreach ($countrydau as $country=>$dau) {
			$regdt = strtotime($date);
			$one = array();
			$one['date'] = $date;
			$one['pf'] = "'$pfkey'";
			$one['country'] = "'$country'";
			$one['dau'] = $dau;
			$one['reg_all'] = intval($loginArrRegRet[$date][$pfkey][$country][$date]);
			$one['reg_valid'] = intval($loginArrRegRet[$date][$pfkey][$country][$date]);//TODO
			
			$r = $loginArrRegRet[$date][$pfkey][$country];
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
	
	$db_tbl = "$statdb_allserver.stat_retention_daily_pf_country";
	$sql = sprintf($insertSql, $db_tbl);
// 	echo $sql,"\n";
	query_infobright($sql);
}


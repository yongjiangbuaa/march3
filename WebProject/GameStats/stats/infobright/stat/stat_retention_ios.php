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

// ************** get DAU
/* $loginArrDau = array();
$sql = "
	select date, count(distinct uid) ucnt
	from $snapshotdb.stat_login 
	where date between $req_date_start and $req_date_end
	group by date
	order by date
";
$ret = query_infobright($sql);
foreach ($ret as $fields){
	$loginArrDau[$fields['date']] += $fields['ucnt'];
} */
// print_r($loginArr);


// ************** get retention
$loginArrRegRet = array();
$sql = "select r.date as regdate, l.date as relogindate, ph.model, ph.version, count(distinct(r.uid)) as ucnt from 
		$snapshotdb.stat_phone ph inner join 
		(select date, uid from $snapshotdb.stat_reg where date >= $req_date_start and date <= $req_date_end) r 
		on ph.uid=r.uid inner join 
		(select date, uid from $snapshotdb.stat_login where date >= $req_date_start and date <= $req_date_end ) l 
		on ph.uid=l.uid where ph.model like '%iphone%' or ph.model like '%ipad%'
		group by regdate,relogindate,model,version order by regdate,relogindate,model,version;";

$ret = query_infobright($sql);
foreach ($ret as $fields){
	$loginArrRegRet[$fields['regdate']][$fields['relogindate']][$fields['model']][$fields['version']] += $fields['ucnt'];
}
// print_r($loginArrRegRet);

/////////////
$records = array();
//ksort($loginArrDau);
for ($date=$req_date_start;$date<=$req_date_end;) {
	
	$regdt = strtotime($date);
	foreach ($loginArrRegRet[$date][$date] as $modelKey=>$versionVlue){
		foreach ($versionVlue as $versionKey=>$value){
			$one = array();
			$one['date'] = $date;
			$one['dau'] = 0;
			$one['model']="'{$modelKey}'";
			$one['version']="'{$versionKey}'";
			$one['reg_all'] = intval($value);
			$one['reg_valid'] = intval($value);//TODO
			$r = $loginArrRegRet[$date];
			ksort($r);
			foreach ($r as $date2=>$newreg) {
				if($date2 == $date) continue;
				$logindt = strtotime($date2);
				$days = round(($logindt - $regdt) / 86400);
				if($days<0 || !$newreg[$modelKey][$versionKey]) continue;
				$one["r$days"] = $newreg[$modelKey][$versionKey];
			}
			$records[] = $one;
		}
	}
	$date=date("Ymd",(strtotime($date)+86400));
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
	
	$db_tbl = "$statdb_allserver.stat_retention_ios";
	$sql = sprintf($insertSql, $db_tbl);
	query_infobright($sql);
}

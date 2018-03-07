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
$modelsArray=array(
					"GT-I9300",
					"GT-I9505",
					"SM-G900F",
					"GT-N7100",
					"GT-I9500",
					"RCT6773W22",
					"SM-N9005",
					"GT-I9100",
					"Nexus 7",
					"SM-G7102",
					"SM-T110",
					"SM-G900V",
					"SCH-I545",
					"GT-I9060",
					"GT-P3100",
					"XT1033",
					"GT-I9082",
					"SAMSUNG-SM-N900A",
					"Nexus 5",
					"GT-I8262",
					"SAMSUNG-SM-G900A",
					"SAMSUNG-SGH-I337",
					"GT-I8190",
					"SM-T210",
					"SM-G900T",
					"GT-S7582",
					"SPH-L710",
					"HTC One",
					"SM-G900P",
					"GT-I9195",
					"GT-I8552",
					"SM-T230NU",
					"SGH-M919",
					"SM-N900",
					"SCH-I535",
					"HTC One_M8",
					"SM-T310",
					"SM-T217S",
					"XT1032",
					"XT1080",
					"GT-I8190N",
					"LGMS323",
					"SM-T211",
					"GT-P5210",
					"ASUS_T00J",
					"SM-T210R",
					"GT-S5360",
					"GT-I8552B",
					"SM-N900T",
					"GT-N7000"
);
$models=implode("','", $modelsArray);
//
$s = microtime(true);

// ************** get retention
$loginArrRegRet = array();
$sql = "select r.date as regdate, l.date as relogindate, ph.model, count(distinct(r.uid)) as ucnt from 
		$snapshotdb.stat_phone ph inner join 
		(select date, uid from $snapshotdb.stat_reg where date >= $req_date_start and date <= $req_date_end) r 
		on ph.uid=r.uid inner join 
		(select date, uid from $snapshotdb.stat_login where date >= $req_date_start and date <= $req_date_end ) l 
		on ph.uid=l.uid where ph.model in('$models')
		group by regdate,relogindate,model order by regdate,relogindate,model;";

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
	
	$db_tbl = "$statdb_allserver.stat_retention_allPhone";
	$sql = sprintf($insertSql, $db_tbl);
	query_infobright($sql);
}

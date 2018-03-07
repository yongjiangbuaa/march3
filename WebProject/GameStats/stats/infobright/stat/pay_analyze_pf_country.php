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
$client = getInfobrightConnect('pay_analyze_pf_country.php');
if(!$client){
	echo 'mysql error pay_analyze_pf_country.php'.PHP_EOL;
	return;
}

$records = array();

$firstSql = "select date,pf,country, count(uid) as cnt from (select p.uid as uid, min(p.date) as date,r.pf as pf, r.country as country from $snapshotdb.paylog p left join $snapshotdb.stat_reg r on p.uid=r.uid where p.pf!='iostest' group by uid) as fristpay where  date between $req_date_start and $req_date_end group by date,pf,country;";
$firstResult = query_infobright_new($client,$firstSql);
$firstPay=array();
//echo $firstSql;
foreach ($firstResult as $temp){
	if($temp['pf']==null){
		$pf='roll';
		$country='roll';
	}else{
		$pf=$temp['pf'];
		if($temp['country']==null){
			$country = "Unknown";
		}else{
			$country = $temp['country'];
		}
	}
	$firstPay[$temp['date']][$pf][$country] += $temp['cnt'];
}

$sql = "select p.date as date,r.pf as pf,r.country as country,count(DISTINCT(p.uid)) as uniquePay,count(p.uid) as totalPay,sum(p.spend) as total from $snapshotdb.paylog p left join $snapshotdb.stat_reg r on p.uid=r.uid where p.pf!='iostest' and p.date between $req_date_start and $req_date_end GROUP BY date,pf,country;";
$result = query_infobright_new($client,$sql);
$data=array();
foreach ($result as $temp){
	
	if($temp['pf']==null){
		$pf='roll';
		$country='roll';
	}else{
		$pf=$temp['pf'];
		if($temp['country']==null){
			$country = "Unknown";
		}else{
			$country = $temp['country'];
		}
	}
	$data[$temp['date']][$pf][$country]['payTotle'] += $temp['total'];
	$data[$temp['date']][$pf][$country]['payUsers'] += $temp['uniquePay'];
	$data[$temp['date']][$pf][$country]['payTimes'] += $temp['totalPay'];
}
$DAUSql = "select l.date as date,r.pf as pf, r.country as country, count(distinct(l.uid)) as total from $snapshotdb.stat_login l left join $snapshotdb.stat_reg r on l.uid=r.uid where l.date between $req_date_start and $req_date_end GROUP BY date,pf,country;";
$DAUResult = query_infobright_new($client,$DAUSql);
//echo $DAUSql.PHP_EOL;
$tempDau=array();
foreach ($DAUResult as $temp){
	if($temp['pf']==null){
		$pf='roll';
		$country='roll';
	}else{
		$pf=$temp['pf'];
		if($temp['country']==null){
			$country = "Unknown";
		}else{
			$country = $temp['country'];
		}
	}
	$tempDau[$temp['date']][$pf][$country] += $temp['total'];
}
foreach ($tempDau as $dateKey=>$pfCountryValue){
	foreach ($pfCountryValue as $pfKey=>$countryValue){
		foreach ($countryValue as $countryKey=>$value){
			$one = array();
			$one['date']=$dateKey;
			$one['pf']="'$pfKey'";
			$one['country']="'$countryKey'";
			if(isset($data[$dateKey][$pfKey][$countryKey]['payTotle'])){
				$one['payTotle']=$data[$dateKey][$pfKey][$countryKey]['payTotle'];
				//print_r($one['payTotle'].'\n');
			}else {
				$one['payTotle']=0;
			}
			$one['payUsers']=intval($data[$dateKey][$pfKey][$countryKey]['payUsers']);
			$one['payTimes']=intval($data[$dateKey][$pfKey][$countryKey]['payTimes']);
			$one['dau']=$value;
			$one['firstPay']=intval($firstPay[$dateKey][$pfKey][$countryKey]);
			$records[] = $one;
		}
	}
}
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

	$db_tbl = "$statdb_allserver.pay_analyze_pf_country";
	$sql = sprintf($insertSql, $db_tbl);
	query_infobright_new($client,$sql);
}



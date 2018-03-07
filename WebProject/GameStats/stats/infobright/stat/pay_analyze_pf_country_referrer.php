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
$client = getInfobrightConnect('pay_analyze_pf_country_referrer.php');
if(!$client){
	echo 'mysql error pay_analyze_pf_country_referrer.php'.PHP_EOL;
	return;
}
$firstSql = "select date,pf,country,referrer, count(uid) as cnt from (select p.uid as uid, min(p.date) as date,r.pf as pf, r.country as country ,r.referrer as referrer from $snapshotdb.paylog p left join $snapshotdb.stat_reg r on p.uid=r.uid where p.pf!='iostest' group by uid) as fristpay where date between $req_date_start and $req_date_end group by date,pf,country,referrer;";
$firstResult = query_infobright_new($client,$firstSql);
$firstPay=array();
//echo $firstSql;
foreach ($firstResult as $temp){
	$major_key = dau_referrer($temp);
	$pf=$major_key[0];
	$country=$major_key[1];
	$referrer=$major_key[2];
	$firstPay[$temp['date']][$pf][$country][$referrer] += $temp['cnt'];
}

$sql = "select p.date as date,r.pf as pf,r.country as country,r.referrer as referrer, count(DISTINCT(p.uid)) as uniquePay,count(p.uid) as totalPay,sum(p.spend) as total from $snapshotdb.paylog p left join $snapshotdb.stat_reg r on p.uid=r.uid where p.pf!='iostest' and p.date between $req_date_start and $req_date_end GROUP BY date,pf,country,referrer;";
$result = query_infobright_new($client,$sql);
$data=array();
foreach ($result as $temp){

	$major_key = dau_referrer($temp);
	$pf=$major_key[0];
	$country=$major_key[1];
	$referrer=$major_key[2];
	$data[$temp['date']][$pf][$country][$referrer]['payTotle'] += $temp['total'];
	$data[$temp['date']][$pf][$country][$referrer]['payUsers'] += $temp['uniquePay'];
	$data[$temp['date']][$pf][$country][$referrer]['payTimes'] += $temp['totalPay'];
}
$DAUSql = "select l.date as date,r.pf as pf, r.country as country,r.referrer as referrer, count(distinct(l.uid)) as total from $snapshotdb.stat_login l left join $snapshotdb.stat_reg r on l.uid=r.uid where l.date between $req_date_start and $req_date_end GROUP BY date,pf,country,referrer;";
$DAUResult = query_infobright_new($client,$DAUSql);
//echo $DAUSql;
$tempDau=array();
foreach ($DAUResult as $temp){
	$major_key = dau_referrer($temp);
	$pf=$major_key[0];
	$country=$major_key[1];
	$referrer=$major_key[2];
	$tempDau[$temp['date']][$pf][$country][$referrer] += $temp['total'];
}
foreach ($tempDau as $dateKey=>$pfCountryValue){
	foreach ($pfCountryValue as $pfKey=>$countryValue){
		foreach ($countryValue as $countryKey=>$referrerValue){
			foreach ($referrerValue as $referrerKey=>$value) {
				$one = array();
				$one['date'] = $dateKey;
				$one['pf'] = "'$pfKey'";
				$one['country'] = "'$countryKey'";
				$one['referrer'] = "'$referrerKey'";
				if (isset($data[$dateKey][$pfKey][$countryKey][$referrerKey]['payTotle'])) {
					$one['payTotle'] = $data[$dateKey][$pfKey][$countryKey][$referrerKey]['payTotle'];
					//print_r($one['payTotle'].'\n');
				} else {
					$one['payTotle'] = 0;
				}
				$one['payUsers'] = intval($data[$dateKey][$pfKey][$countryKey][$referrerKey]['payUsers']);
				$one['payTimes'] = intval($data[$dateKey][$pfKey][$countryKey][$referrerKey]['payTimes']);
				$one['dau'] = $value;
				$one['firstPay'] = intval($firstPay[$dateKey][$pfKey][$countryKey][$referrerKey]);
				$records[] = $one;
			}
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

	$db_tbl = "$statdb_allserver.pay_analyze_pf_country_referrer";
	$sql = sprintf($insertSql, $db_tbl);
	query_infobright_new($client,$sql);
}



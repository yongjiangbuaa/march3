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
$client = getInfobrightConnect('pay_analyze_pf_country_referrer_new.php');
if(!$client){
	echo 'mysql error pay_analyze_pf_country_referrer_new.php'.PHP_EOL;
	return;
}
$firstSql = "select date,pf,country,referrer, type,count(uid) as cnt from
(select p.uid as uid, min(p.date) as date,r.pf as pf, r.country as country ,r.referrer as referrer,r.type as type
from $snapshotdb.paylog p
LEFT JOIN (select sr.* from $snapshotdb.stat_reg sr inner join
(select uid,max(time) as time from $snapshotdb.stat_reg group by uid) aa on sr.uid=aa.uid and sr.time=aa.time ) r on p.uid=r.uid
where p.pf!='iostest' group by uid,type) as fristpay
where date between $req_date_start and $req_date_end group by date,pf,country,referrer,type;";
$firstResult = query_infobright_new($client,$firstSql);
$firstPay=array();
//echo $firstSql;
foreach ($firstResult as $temp){
	$major_key = dau_referrer($temp);
	$pf=$major_key[0];
	$country=$major_key[1];
	$referrer=$major_key[2];
	$firstPay[$temp['date']][$pf][$country][$referrer][$temp['type']] += $temp['cnt'];
}

$sql = "select p.date as date,r.pf as pf,r.country as country,r.referrer as referrer, r.type as type, count(DISTINCT(p.uid)) as uniquePay,count(p.uid) as totalPay,sum(p.spend) as total
from $snapshotdb.paylog p
LEFT JOIN (select sr.* from $snapshotdb.stat_reg sr inner join
(select uid,max(time) as time from $snapshotdb.stat_reg group by uid) aa on sr.uid=aa.uid and sr.time=aa.time ) r on p.uid=r.uid
where p.pf!='iostest' and p.date between $req_date_start and $req_date_end
GROUP BY date,pf,country,referrer,type;";
$result = query_infobright_new($client,$sql);
$data=array();
foreach ($result as $temp){

	$major_key = dau_referrer($temp);
	$pf=$major_key[0];
	$country=$major_key[1];
	$referrer=$major_key[2];
	$data[$temp['date']][$pf][$country][$referrer][$temp['type']]['payTotle'] += $temp['total'];
	$data[$temp['date']][$pf][$country][$referrer][$temp['type']]['payUsers'] += $temp['uniquePay'];
	$data[$temp['date']][$pf][$country][$referrer][$temp['type']]['payTimes'] += $temp['totalPay'];
}
$DAUSql = "select l.date as date,r.pf as pf, r.country as country,r.referrer as referrer,r.type as type, count(distinct(l.uid)) as total from $snapshotdb.stat_login l
LEFT JOIN (select sr.* from $snapshotdb.stat_reg sr inner join
(select uid,max(time) as time from $snapshotdb.stat_reg group by uid) aa on sr.uid=aa.uid and sr.time=aa.time ) r on l.uid=r.uid
where l.date between $req_date_start and $req_date_end
GROUP BY date,pf,country,referrer,type;";
$DAUResult = query_infobright_new($client,$DAUSql);
//echo $DAUSql;
$tempDau=array();
foreach ($DAUResult as $temp){
	$major_key = dau_referrer($temp);
	$pf=$major_key[0];
	$country=$major_key[1];
	$referrer=$major_key[2];
	$tempDau[$temp['date']][$pf][$country][$referrer][$temp['type']] += $temp['total'];
}
foreach ($tempDau as $dateKey=>$pfCountryValue){
	foreach ($pfCountryValue as $pfKey=>$countryValue){
		foreach ($countryValue as $countryKey=>$referrerValue){
			foreach ($referrerValue as $referrerKey=>$value) {
				foreach ($value as $type=>$item) {
					$one = array();
					$one['date'] = $dateKey;
					$one['pf'] = "'$pfKey'";
					$one['country'] = "'$countryKey'";
					$one['referrer'] = "'$referrerKey'";
					$one['type'] = "'$type'";
					if (isset($data[$dateKey][$pfKey][$countryKey][$referrerKey][$type]['payTotle'])) {
						$one['payTotle'] = $data[$dateKey][$pfKey][$countryKey][$referrerKey][$type]['payTotle'];
						//print_r($one['payTotle'].'\n');
					} else {
						$one['payTotle'] = 0;
					}
					$one['payUsers'] = intval($data[$dateKey][$pfKey][$countryKey][$referrerKey][$type]['payUsers']);
					$one['payTimes'] = intval($data[$dateKey][$pfKey][$countryKey][$referrerKey][$type]['payTimes']);
					$one['dau'] = $item;
					$one['firstPay'] = intval($firstPay[$dateKey][$pfKey][$countryKey][$referrerKey][$type]);
					$records[] = $one;
				}
			}
		}
	}
}
/////////////

if(!function_exists("buildUpdateSql")){
	function buildUpdateSql($kv){
		$all = array();
		foreach ($kv as $key => $value) {
			$all[] = "$key=$value";
		}
		return implode(',', $all);
	}
}

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

	$db_tbl = "$statdb_allserver.pay_analyze_pf_country_referrer_new";
	$sql = sprintf($insertSql, $db_tbl);
	query_infobright_new($client,$sql);
}

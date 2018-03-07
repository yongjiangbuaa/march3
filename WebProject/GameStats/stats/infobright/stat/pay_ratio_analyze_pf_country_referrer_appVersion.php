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
$client = getInfobrightConnect('pay_ratio_analyze_pf_country_referrer_appVersion.php');
if(!$client){
	echo 'mysql error pay_ratio_analyze_pf_country_referrer_appVersion.php'.PHP_EOL;
	return;
}
//first day pay user   r.date注册时间  .新注册当日就付费
$sql = "select count(DISTINCT p.uid) as firstDayPay, r.date as date, r.pf as pf, r.country as country, r.referrer as referrer, ur.appVersion as appVersion from $snapshotdb.paylog p
left join (select distinct uid,max(date) as date, pf,country,referrer from $snapshotdb.stat_reg) r on p.uid=r.uid
left join $snapshotdb.user_reg ur on p.uid=ur.uid
where p.date=r.date and p.pf!='iostest' and p.date between $req_date_start and $req_date_end
group by date,pf,country,referrer,appVersion;";
$Result = query_infobright_new($client,$sql);
$firstPay=array();
foreach ($Result as $temp){
	$major_key = dau_referrer($temp);
	$pf=$major_key[0];
	$country=$major_key[1];
	$referrer=$major_key[2];
	$appVersion=$major_key[3];
	$firstPay[$temp['date']][$pf][$country][$referrer][$appVersion] += $temp['firstDayPay'];
}

// new reg device day
$sql="select r.date as date,r.pf as pf,r.country as country,count(distinct(u.deviceId)) as regDevice, r.referrer as referrer, ur.appVersion as appVersion
from $snapshotdb.stat_reg r, $snapshotdb.userprofile u, $snapshotdb.user_reg ur where r.uid = u.uid and r.uid=ur.uid and r.date between $req_date_start and $req_date_end and type = 0 group by date,pf,country,referrer,appVersion;";
$result = query_infobright_new($client,$sql);
$data=array();
foreach ($result as $temp){
	$major_key = dau_referrer($temp);
	$pf=$major_key[0];
	$country=$major_key[1];
	$referrer=$major_key[2];
	$appVersion=$major_key[3];
	$data[$temp['date']][$pf][$country][$referrer][$appVersion]['regDevice'] += $temp['regDevice'];
}

//old pay user dau
$sql = "select l.date as date,count(distinct(l.uid)) as oldPayDAU,l.pf as pf,l.country as country ,r.referrer as referrer, ur.appVersion as appVersion from $snapshotdb.stat_login_full l
inner join $snapshotdb.paylog p on l.uid = p.uid
left join (select distinct uid,pf,country,referrer from $snapshotdb.stat_reg) r on l.uid=r.uid
inner join $snapshotdb.user_reg ur on l.uid=ur.uid
where p.pf!='iostest' and p.date < $req_date_start and l.date between $req_date_start and $req_date_end group by date,pf,country,referrer,appVersion;";
$result = query_infobright_new($client,$sql);
foreach ($result as $temp){
	$major_key = dau_referrer($temp);
	$pf=$major_key[0];
	$country=$major_key[1];
	$referrer=$major_key[2];
	$appVersion=$major_key[3];
	$data[$temp['date']][$pf][$country][$referrer][$appVersion]['oldPayDAU'] += $temp['oldPayDAU'];
}

// new total pay
$sql = "select p.uid as uid,min(p.date) as date,p.spend as newTotalPay,r.pf as pf,r.country as country,r.referrer as referrer, ur.appVersion as appVersion from $snapshotdb.paylog p
left join (select distinct uid,pf,country,referrer from $snapshotdb.stat_reg) r on p.uid=r.uid
left join $snapshotdb.user_reg ur on p.uid=ur.uid
where p.pf!='iostest' group by uid,referrer,appVersion,pf,country having date between $req_date_start and $req_date_end;";
$result = query_infobright_new($client,$sql);
foreach ($result as $temp){
	$major_key = dau_referrer($temp);
	$pf=$major_key[0];
	$country=$major_key[1];
	$referrer=$major_key[2];
	$appVersion=$major_key[3];
	$tempData[$temp['date']][$pf][$country][$referrer][$appVersion]['newTotalPay'] += $temp['newTotalPay'];
}
$DAUSql = "select l.date as date,r.pf as pf, r.country as country, count(distinct(l.uid)) as total, r.referrer as referrer, ur.appVersion as appVersion from $snapshotdb.stat_login l
left join (select distinct uid,pf,country,referrer from $snapshotdb.stat_reg) r on l.uid=r.uid
left join $snapshotdb.user_reg ur on l.uid=ur.uid
where l.date between $req_date_start and $req_date_end GROUP BY date,pf,country,referrer,appVersion;";
$DAUResult = query_infobright_new($client,$DAUSql);
$tempDau=array();
foreach ($DAUResult as $temp){
	$major_key = dau_referrer($temp);
	$pf=$major_key[0];
	$country=$major_key[1];
	$referrer=$major_key[2];
	$appVersion=$major_key[3];
	$tempDau[$temp['date']][$pf][$country][$referrer][$appVersion] += $temp['total'];
}


foreach ($tempDau as $dateKey=>$pfCountryValue){
	foreach ($pfCountryValue as $pfKey=>$countryValue){
		foreach ($countryValue as $countryKey=>$referrerValue){
			foreach ($referrerValue as $referrerKey=>$appVersionValue) {
				foreach ($appVersionValue as $appVersionKey=>$value) {
					$one = array();
					$one['date'] = $dateKey;
					$one['pf'] = "'$pfKey'";
					$one['country'] = "'$countryKey'";
					$one['referrer'] = "'$referrerKey'";
					$one['appVersion'] = "'$appVersionKey'";
					$one['dau'] = intval($value);//
					$one['newTotalPay'] = $tempData[$dateKey][$pfKey][$countryKey][$referrerKey][$appVersionKey]['newTotalPay']? $tempData[$dateKey][$pfKey][$countryKey][$referrerKey][$appVersionKey]['newTotalPay']:0;
					$one['firstDayPay'] = intval($firstPay[$dateKey][$pfKey][$countryKey][$referrerKey][$appVersionKey]);
					$one['regDevice'] = intval($data[$dateKey][$pfKey][$countryKey][$referrerKey][$appVersionKey]['regDevice']);
					$one['oldPayDAU'] = intval($data[$dateKey][$pfKey][$countryKey][$referrerKey][$appVersionKey]['oldPayDAU']);
					$records[] = $one;
				}
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
	$db_tbl = "$statdb_allserver.pay_ratio_analyze_pf_country_referrer_appVersion";
	$sql = sprintf($insertSql, $db_tbl);
	query_infobright_new($client,$sql);
}



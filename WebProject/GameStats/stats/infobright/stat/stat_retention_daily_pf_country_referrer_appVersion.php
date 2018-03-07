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

$tempServer=530;

$crossServer=571;

//
$s = microtime(true);
$client = getInfobrightConnect('stat_retention_daily_pf_country_referrer_appVersion.php');
if(!$client){
	echo 'mysql error stat_retention_daily_pf_country_referrer_appVersion.php'.PHP_EOL;
	return;
}
// ************** get countrys' DAU
$loginArrDau = array();

if (false && SERVER_ID>=$tempServer && SERVER_ID<$crossServer){
	$sql = "select l.date,count(distinct(l.uid)) ucnt,l.pf,l.country from $snapshotdb.stat_login_full l left join snapshot_global.cheat_deviceId cd on l.deviceId=cd.deviceId where l.date between $req_date_start and $req_date_end and cd.deviceId is null group by date,pf,country;";
}else {
	$sql = "select l.date,count(distinct(l.uid)) ucnt,l.pf,l.country,r.referrer,ur.appVersion from $snapshotdb.stat_login_full l inner join $snapshotdb.stat_reg r on l.uid=r.uid inner join $snapshotdb.user_reg ur on l.uid=ur.uid where l.date between $req_date_start and $req_date_end group by l.date,l.pf,l.country,r.referrer,ur.appVersion;";
}
//echo $sql."\n";
$ret = query_infobright_new($client,$sql);
foreach ($ret as $fields){
	$major_key = dau_referrer($fields);
	$pf=$major_key[0];
	$country=$major_key[1];
	$referrer=$major_key[2];
	$appVersion=$major_key[3];
	$loginArrDau[$fields['date']][$pf][$country][$referrer][$appVersion] += $fields['ucnt'];
}
// print_r($loginArrDau);


// ************** get retention
$loginArrRegRet = array();
if (false && SERVER_ID>=$tempServer && SERVER_ID<$crossServer){
	$sql = "
	select r.date as regdate, l.date as relogindate, r.pf as pf, r.country as country, count(distinct(r.uid)) as ucnt from 
	(select date, pf, country, uid from $snapshotdb.stat_reg where date >= $req_date_start and date <= $req_date_end  and type=0) r 
	inner join 
	(select date, uid,deviceId from $snapshotdb.stat_login_full where date >= $req_date_start and date <= $req_date_end ) l 
	on r.uid = l.uid 
	left join snapshot_global.cheat_deviceId cd 
	on l.deviceId=cd.deviceId where cd.deviceId is null 
	group by regdate,relogindate,pf,country;";
}else {
	$sql = "
		select r.date as regdate, l.date as relogindate, r.pf as pf, r.country as country, r.referrer as referrer, ur.appVersion as appVersion, count(distinct(r.uid)) as ucnt from
		(select date, pf, country, uid, referrer from $snapshotdb.stat_reg where date >= $req_date_start and date <= $req_date_end and type=0) r
		inner join 
		(select date, uid from $snapshotdb.stat_login where date >= $req_date_start and date <= $req_date_end ) l 
		on r.uid = l.uid
		inner join $snapshotdb.user_reg ur on r.uid=ur.uid
		group by regdate,relogindate,pf,country,referrer,appVersion;";
}
$ret = query_infobright_new($client,$sql);
//echo $sql."\n";
foreach ($ret as $fields){
	$major_key = dau_referrer($fields);
	$pf=$major_key[0];
	$country=$major_key[1];
	$referrer=$major_key[2];
	$appVersion=$major_key[3];
	$loginArrRegRet[$fields['regdate']][$pf][$country][$referrer][$appVersion][$fields['relogindate']] += $fields['ucnt'];
}


$sql = "
		select r.date as regdate, l.date as relogindate, r.pf as pf, r.country as country, r.referrer as referrer,  ur.appVersion as appVersion, count(distinct(r.uid)) as ucnt from
		(select date, pf, country, uid, referrer from $snapshotdb.stat_reg where date >= $req_date_start and date <= $req_date_end and type=1) r
		inner join
		(select date, uid from $snapshotdb.stat_login where date >= $req_date_start and date <= $req_date_end ) l
		on r.uid = l.uid
		inner join $snapshotdb.user_reg ur on r.uid=ur.uid
		group by regdate,relogindate,pf,country,referrer,appVersion;";
$ret = query_infobright_new($client,$sql);
//echo $sql."\n";
foreach ($ret as $fields){
	$major_key = dau_referrer($fields);
	$pf=$major_key[0];
	$country=$major_key[1];
	$referrer=$major_key[2];
	$appVersion=$major_key[3];
	$loginArrRegRet2[$fields['regdate']][$pf][$country][$referrer][$appVersion][$fields['relogindate']] += $fields['ucnt'];
}
//新增付费用户留存
$payArrRegRet = array();

$sql = "
	select pay.date as paydate, l.date as relogindate, r.pf as pf, r.country as country, r.referrer as referrer, ur.appVersion as appVersion, count(distinct(pay.uid)) as ucnt from
	(select pp.uid,pp.date from (select min(p.date) as date, p.uid from $snapshotdb.paylog p group by p.uid)pp where pp.date >= $req_date_start and pp.date <= $req_date_end) pay
	inner join
	(select date, uid from $snapshotdb.stat_login where date >= $req_date_start and date <= $req_date_end ) l
	on pay.uid = l.uid
	inner join $snapshotdb.user_reg ur on pay.uid=ur.uid
	inner join $snapshotdb.stat_reg r on pay.uid = r.uid
	group by paydate,relogindate,pf,country,referrer,appVersion;";
$ret = query_infobright_new($client,$sql);
//echo $sql."\n";
foreach ($ret as $fields){
	$major_key = dau_referrer($fields);
	$pf=$major_key[0];
	$country=$major_key[1];
	$referrer=$major_key[2];
	$appVersion=$major_key[3];
	$payArrRegRet[$fields['paydate']][$pf][$country][$referrer][$appVersion][$fields['relogindate']] += $fields['ucnt'];
}

//0-新注册 1－重玩 2-迁服
if (false && SERVER_ID>=$tempServer && SERVER_ID<$crossServer){
	$sql="select r.date,r.pf,r.country,r.type,count(distinct r.uid) users from $snapshotdb.stat_reg r inner join $snapshotdb.userprofile_full u on r.uid=u.uid left join snapshot_global.cheat_deviceId cd on u.deviceId=cd.deviceId where r.date >= $req_date_start and r.date <= $req_date_end and cd.deviceId is null group by date,pf,country,type;";
}else {
	$sql="select r.date as date ,r.pf as pf ,r.country as country, r.type as type, r.referrer as referrer, ur.appVersion as appVersion, count(distinct(r.uid)) users from $snapshotdb.stat_reg r inner join $snapshotdb.user_reg ur on r.uid=ur.uid where date >= $req_date_start and date <= $req_date_end group by date,pf,country,type,referrer,appVersion;";
}
$ret = query_infobright_new($client,$sql);
$regArray=array();
foreach ($ret as $fields){
	$major_key = dau_referrer($fields);
	$pf=$major_key[0];
	$country=$major_key[1];
	$referrer=$major_key[2];
	$appVersion=$major_key[3];
	if ($fields['type']==0){
		$regArray[$fields['date']][$pf][$country][$referrer][$appVersion]['newUsers'] += $fields['users'];
	}elseif ($fields['type']==1){
		$regArray[$fields['date']][$pf][$country][$referrer][$appVersion]['replay'] += $fields['users'];
	}else {
		$regArray[$fields['date']][$pf][$country][$referrer][$appVersion]['relocation'] += $fields['users'];
	}
}

// print_r($loginArrRegRet);

/////////////
$records = array();
ksort($loginArrDau);
foreach ($loginArrDau as $date=>$pfCountrydau) {
	foreach ($pfCountrydau as $pfkey=>$countrydau){
		foreach ($countrydau as $country=>$referrerDau) {
			foreach ($referrerDau as $referrer => $appVersionDau) {
				foreach ($appVersionDau as $appVersion => $dau) {
					$regdt = strtotime($date);
					$one = array();
					$one['date'] = $date;
					$one['pf'] = "'$pfkey'";
					$one['country'] = "'$country'";
					$one['referrer'] = "'$referrer'";
					$one['appVersion'] = "'$appVersion'";
					$one['dau'] = $dau;
					$one['reg_all'] = intval($loginArrRegRet[$date][$pfkey][$country][$referrer][$appVersion][$date]);
					$one['reg_valid'] = intval($regArray[$date][$pfkey][$country][$referrer][$appVersion]['newUsers']);  //新注册
					$one['replay'] = intval($regArray[$date][$pfkey][$country][$referrer][$appVersion]['replay']);  //重玩人数
					$one['relocation'] = intval($regArray[$date][$pfkey][$country][$referrer][$appVersion]['relocation']);  //迁服人数
					$one['pay_all'] = intval($payArrRegRet[$date][$pfkey][$country][$referrer][$appVersion][$date]);//

					$r = $loginArrRegRet[$date][$pfkey][$country][$referrer][$appVersion];
					if ($r) {
						ksort($r);
						foreach ($r as $date2 => $newreg) {
							if ($date2 == $date) continue;
							$logindt = strtotime($date2);
							$days = round(($logindt - $regdt) / 86400);
							if ($days < 0) continue;
							$one["r$days"] = $newreg;
						}
						$r = $loginArrRegRet2[$date][$pfkey][$country][$referrer][$appVersion];
						if ($r) {
							ksort($r);
							foreach ($r as $date2 => $newreg) {
								if ($date2 == $date) continue;
								$logindt = strtotime($date2);
								$days = round(($logindt - $regdt) / 86400);
								if ($days != 1) continue;
								$one["rr$days"] = $newreg;
							}
						}
					}
					$r = $payArrRegRet[$date][$pfkey][$country][$referrer][$appVersion];
					if ($r) {
						ksort($r);
						foreach ($r as $date2 => $newPay) {
							if ($date2 == $date) continue;
							$logindt = strtotime($date2);
							$days = round(($logindt - $regdt) / 86400);
							if ($days < 0) continue;
							$one["p$days"] = $newPay;
						}
					}
					$records[] = $one;
				}
			}
		}
	}
}
//print_r($records);

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
	
	$db_tbl = "$statdb_allserver.stat_retention_daily_pf_country_referrer_appVersion";
	$sql = sprintf($insertSql, $db_tbl);
// 	echo $sql,"\n";
	query_infobright_new($client,$sql);
}


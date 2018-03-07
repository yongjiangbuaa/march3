<?php 
//日期	日活跃	新注册数	有效注册	次日留存	3日留存	4日留存	5日留存	6日留存	7日留存
//date	dau	reg_all	reg_valid	r1	r2	r3	r4	r5	r6
ini_set('memory_limit', '1024M');
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
$client = getInfobrightConnect('stat_retention_daily_pf_country_version.php');
if(!$client){
	echo 'mysql error stat_retention_daily_pf_country_version.php'.PHP_EOL;
	return;
}
$dateArr=array();
$pfArr=array();
$countryArr=array();
$appVersionArr=array();

// ************** get countrys' DAU
$loginArrDau = array();

if (SERVER_ID>=$tempServer && SERVER_ID<$crossServer){
	$sql = "select l.date,count(distinct(l.uid)) ucnt,l.pf,l.country,ur.appVersion from $snapshotdb.stat_login_full l left join snapshot_global.cheat_deviceId cd on l.deviceId=cd.deviceId inner join $snapshotdb.user_reg ur on l.uid=ur.uid where l.date between $req_date_start and $req_date_end and cd.deviceId is null group by date,pf,country,appVersion;";
}else {
	$sql = "select l.date,count(distinct(l.uid)) ucnt,l.pf,l.country,ur.appVersion from $snapshotdb.stat_login_full l inner join $snapshotdb.user_reg ur on l.uid=ur.uid where l.date between $req_date_start and $req_date_end group by date,pf,country,appVersion;";
}

//echo $sql."\n";
$ret = query_infobright_new($client,$sql);
foreach ($ret as $fields){
	if($fields['pf']==null || $fields['pf']==''){
		$pf='roll';
		$country='roll';
	}else{
		$pf=$fields['pf'];
		if($fields['country']==null || $fields['country']==''){
			$country = "Unknown";
		}else{
			$country = $fields['country'];
		}
	}
	if (!in_array($fields['date'], $dateArr)){
		$dateArr[]=$fields['date'];
	}
	if (!in_array($pf, $pfArr)){
		$pfArr[]=$pf;
	}
	if (!in_array($country, $countryArr)){
		$countryArr[]=$country;
	}
	if (!in_array($fields['appVersion'], $appVersionArr)){
		$appVersionArr[]=$fields['appVersion'];
	}
	$loginArrDau[$fields['date']][$pf][$country][$fields['appVersion']] += $fields['ucnt'];
}
// print_r($loginArrDau);


// ************** get retention
$loginArrRegRet = array();
if (SERVER_ID>=$tempServer && SERVER_ID<$crossServer){
	$sql = "
	select r.date as regdate, l.date as relogindate, r.pf as pf, r.country as country,ur.appVersion, count(distinct(r.uid)) as ucnt from 
	$snapshotdb.userprofile_full u inner join 
	(select date, pf, country, uid from $snapshotdb.stat_reg where date >= $req_date_start and date <= $req_date_end  and type=0) r 
	on u.uid=r.uid inner join 
	(select date, uid,deviceId from $snapshotdb.stat_login_full where date >= $req_date_start and date <= $req_date_end ) l 
	on r.uid = l.uid inner join $snapshotdb.user_reg ur on r.uid=ur.uid 
	left join snapshot_global.cheat_deviceId cd 
	on l.deviceId=cd.deviceId where cd.deviceId is null and u.banTime!=9223372036854775807 
	group by regdate,relogindate,pf,country,appVersion;";
}else {
	$sql = "
		select r.date as regdate, l.date as relogindate, r.pf as pf, r.country as country, ur.appVersion, count(distinct(r.uid)) as ucnt from 
		$snapshotdb.userprofile_full u inner join 
		(select date, pf, country, uid from $snapshotdb.stat_reg where date >= $req_date_start and date <= $req_date_end and type=0) r 
		on u.uid=r.uid inner join 
		(select date, uid from $snapshotdb.stat_login where date >= $req_date_start and date <= $req_date_end ) l 
		on r.uid = l.uid inner join $snapshotdb.user_reg ur on r.uid=ur.uid 
		where u.banTime!=9223372036854775807 
		group by regdate,relogindate,pf,country,appVersion;";
}

$ret = query_infobright_new($client,$sql);
//echo $sql."\n";
foreach ($ret as $fields){
	if($fields['pf']==null  || $fields['pf']==''){
		$pf='roll';
		$country='roll';
	}else{
		$pf=$fields['pf'];
		if($fields['country']==null || $fields['country']==''){
			$country = "Unknown";
		}else{
			$country = $fields['country'];
		}
	}
	if (!in_array($pf, $pfArr)){
		$pfArr[]=$pf;
	}
	if (!in_array($country, $countryArr)){
		$countryArr[]=$country;
	}
	if (!in_array($fields['appVersion'], $appVersionArr)){
		$appVersionArr[]=$fields['appVersion'];
	}
	$loginArrRegRet[$fields['regdate']][$pf][$country][$fields['appVersion']][$fields['relogindate']] += $fields['ucnt'];
}

//0-新注册 1－重玩 2-迁服
if (SERVER_ID>=$tempServer && SERVER_ID<$crossServer){
	$sql="select r.date,r.pf,r.country,r.type,ur.appVersion,count(distinct r.uid) users from $snapshotdb.stat_reg r inner join $snapshotdb.userprofile_full u on r.uid=u.uid inner join $snapshotdb.user_reg ur on r.uid=ur.uid left join snapshot_global.cheat_deviceId cd on u.deviceId=cd.deviceId where r.date >= $req_date_start and r.date <= $req_date_end and cd.deviceId is null group by date,pf,country,type,appVersion;";
}else {
	$sql="select r.date,r.pf,r.country,r.type,ur.appVersion,count(distinct r.uid) users from $snapshotdb.stat_reg r inner join $snapshotdb.user_reg ur on r.uid=ur.uid where r.date >= $req_date_start and r.date <= $req_date_end group by date,pf,country,type,appVersion;";
}

$ret = query_infobright_new($client,$sql);
$regArray=array();
foreach ($ret as $fields){
	if($fields['pf']==null  || $fields['pf']==''){
		$pf='roll';
		$country='roll';
	}else{
		$pf=$fields['pf'];
		if($fields['country']==null || $fields['country']==''){
			$country = "Unknown";
		}else{
			$country = $fields['country'];
		}
	}
	if ($fields['type']==0){
		$regArray[$fields['date']][$pf][$country][$fields['appVersion']]['newUsers'] += $fields['users'];
	}elseif ($fields['type']==1){
		$regArray[$fields['date']][$pf][$country][$fields['appVersion']]['replay'] += $fields['users'];
	}else {
		$regArray[$fields['date']][$pf][$country][$fields['appVersion']]['relocation'] += $fields['users'];
	}
}

// print_r($loginArrRegRet);

/////////////
$records = array();
ksort($loginArrDau);
sort($dateArr);

foreach ($dateArr as $date) {
	foreach ($pfArr as $pfkey){
		foreach ($countryArr as $country) {
			foreach ($appVersionArr as $versionKey){
				$regdt = strtotime($date);
				$dau = $loginArrDau[$date][$pfkey][$country][$versionKey];
				$reg = $loginArrRegRet[$date][$pfkey][$country][$versionKey][$date];
				if (intval($dau)<=0){
					continue;
				}
				$one = array();
				$one['date'] = $date;
				$one['pf'] = "'$pfkey'";
				$one['country'] = "'$country'";
				$one['version'] = "'$versionKey'";
				$one['dau'] = intval($loginArrDau[$date][$pfkey][$country][$versionKey]);
				$one['reg_all'] = intval($loginArrRegRet[$date][$pfkey][$country][$versionKey][$date]);
				$one['reg_valid'] = intval($regArray[$date][$pfkey][$country][$versionKey]['newUsers']);  //新注册
				$one['replay'] = intval($regArray[$date][$pfkey][$country][$versionKey]['replay']);  //重玩人数
				$one['relocation'] = intval($regArray[$date][$pfkey][$country][$versionKey]['relocation']);  //迁服人数
				
				$r = $loginArrRegRet[$date][$pfkey][$country][$versionKey];
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
}
// var_dump($records);

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
	
	$db_tbl = "$statdb_allserver.stat_retention_daily_pf_country_version";
	$sql = sprintf($insertSql, $db_tbl);
// 	echo $sql,"\n";
	query_infobright_new($client,$sql);
}


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
$client = getInfobrightConnect('pay_ublevel_rate_pf_country_referrer_appVersion.php');
if(!$client){
	echo 'mysql error pay_ublevel_rate_pf_country_referrer_appVersion.php'.PHP_EOL;
	return;
}
$records = array();
$tempData = array();
//ALL（所有人当天经过这一等级的人数）当天登陆的最大值和最小值
//$sql = "select uf.date,uf.uid,min(s.castlelevel) as castlelevel,uf.buildingLv as buildingLv,r.pf as pf,r.country as country,r.referrer as referrer, uf.appVersion as appVersion from $snapshotdb.userprofile_full uf LEFT JOIN $snapshotdb.stat_login s ON uf.uid = s.uid left join $snapshotdb.stat_reg r on uf.uid = r.uid  group by uid,referrer,appVersion,pf,country having date between $req_date_start and $req_date_end;";
$sql = "select s.date,s.uid,min(s.castlelevel) as minLevel,max(s.castlelevel) as maxLevel,r.pf as pf,r.country as country,r.referrer as referrer, u.appVersion as appVersion from  $snapshotdb.stat_login s
left join (select distinct uid,pf,country,referrer from $snapshotdb.stat_reg) r on s.uid=r.uid
LEFT  JOIN $snapshotdb.user_reg u on s.uid=u.uid
where s.date>=$req_date_start and s.date<=$req_date_end group by uid;";
$result = query_infobright_new($client,$sql);
foreach ($result as $temp){
	$major_key = dau_referrer($temp);
	$pf=$major_key[0];
	$country=$major_key[1];
	$referrer=$major_key[2];
	$appVersion=$major_key[3];
	for($lv=1;$lv<=30;$lv++){
		$minlevel = $temp['minLevel'];
		$maxlevel = $temp['maxLevel'];
		if($lv>=$minlevel&&$lv<=$maxlevel){
			$tempData[$temp['date']][$pf][$country][$referrer][$appVersion][$lv]['buildAll']++;
		}
	}
}
//人数  次数
$tempDau=array();
$sql2 = "select count(p.uid) `sum`,count(DISTINCT p.uid) `count`,p.date,`buildingLv`,r.pf as pf,r.country as country,r.referrer as referrer, u.appVersion as appVersion from $snapshotdb.paylog p
left join (select distinct uid,pf,country,referrer from $snapshotdb.stat_reg) r on p.uid=r.uid
left join $snapshotdb.user_reg u on p.uid=u.uid
where p.pf !='iostest' and p.date>=$req_date_start and p.date<=$req_date_end
group by buildingLv,referrer,appVersion,pf,country ;";
$result = query_infobright_new($client,$sql2);
foreach ($result as $temp){
	$major_key = dau_referrer($temp);
	$pf=$major_key[0];
	$country=$major_key[1];
	$referrer=$major_key[2];
	$appVersion=$major_key[3];
	$tempDau[$temp['date']][$pf][$country][$referrer][$appVersion][$temp['buildingLv']]['sum'] = $temp['sum'];//支付次数
	$tempDau[$temp['date']][$pf][$country][$referrer][$appVersion][$temp['buildingLv']]['count'] = $temp['count'];//支付人数
}
foreach ($tempData as $dateKey=>$pfCountryValue){
	foreach ($pfCountryValue as $pfKey=>$countryValue){
		foreach ($countryValue as $countryKey=>$referrerValue){
			foreach ($referrerValue as $referrerKey=>$appVersionValue) {
				foreach ($appVersionValue as $appVersionKey=>$buildLvValue) {
					foreach($buildLvValue as $buildLvKey=>$value) {
						$one = array();
						$one['date'] = $dateKey;
						$one['buildingLv'] = "'$buildLvKey'";
						$one['pf'] = "'$pfKey'";
						$one['country'] = "'$countryKey'";
						$one['referrer'] = "'$referrerKey'";
						$one['appVersion'] = "'$appVersionKey'";
						$one['buildAll'] = intval($tempData[$dateKey][$pfKey][$countryKey][$referrerKey][$appVersionKey][$buildLvKey]['buildAll']);
						$one['sum'] = intval($tempDau[$dateKey][$pfKey][$countryKey][$referrerKey][$appVersionKey][$buildLvKey]['sum']);
						$one['count'] = intval($tempDau[$dateKey][$pfKey][$countryKey][$referrerKey][$appVersionKey][$buildLvKey]['count']);
						$records[] = $one;
//						echo intval($tempDau[$dateKey][$pfKey][$countryKey][$referrerKey][$appVersionKey][$buildLvKey]['count']);
					}
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
	$db_tbl = "$statdb_allserver.pay_ublevel_rate_pf_country_referrer_appVersion";
	$sql = sprintf($insertSql, $db_tbl);
	query_infobright_new($client,$sql);
//	echo $sql;
}


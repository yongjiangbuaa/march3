<?php
//版本统计,每次运行,统计最后登录时间>7天前的玩家. 也就是只有一份数据存在
defined('IB_ROOT') || define('IB_ROOT', __DIR__);

require_once IB_ROOT . '/ib.inc.php';

ini_set('memory_limit', '2048M');

define('MODULE',basename(__FILE__, '.php'));
//参数只要name
if(!write_pid_file(MODULE)){
	return;
}

$req_date_end = date('Ymd',time());
$span = 7;//最后登录时间7天

$req_date_start = date("Ymd", strtotime("-$span day",strtotime($req_date_end)));

//date,serverid,level领主等级,paylevel,itemid,count,allpeople
$client = getInfobrightConnect('stats_version.php');
if(!$client){
	echo 'mysql error '.MODULE.'.php'.PHP_EOL;
	return;
}
$statdb_allserver = IB_DB_NAME_STAT_ALLSERVER;
$table = IB_DB_NAME_SNAPSHOT;
$lastoninetime = strtotime($req_date_start)*1000;

$sql = "select $req_date_end as date,sr.pf ,sr.country, p.appVersion as appversion,count(p.appVersion) cnt from $table.userprofile_full p
		INNER JOIN (select distinct uid,pf,country from $table.stat_reg) sr on p.uid=sr.uid
		where p.banTime <2422569600000  and sr.pf != '' and p.lastOnlineTime> $lastoninetime
		group by sr.pf,sr.country,p.appversion";

$res = query_infobright_new($client,$sql);
$data = array();
foreach($res as $row){

	$date = $row['date'];
	$pf = $row['pf'];
	$country = $row['country']?$row['country']:'unknow';
	$appversion = $row['appversion'];

	$data[$date][$pf][$country][$appversion] += $row['cnt'];
}
//print_r($data);
$records = array();
foreach ($data as $date=>$pfdata) {
	foreach ($pfdata as $pfkey => $countrydata) {
		foreach ($countrydata as $countrykey => $versiondata) {
			foreach ($versiondata as $version => $cnt) {
				$one = array();

				$one['date'] = $date;
				$one['pf'] = "'$pfkey'";
				$one['country'] = "'$countrykey'";
				$one['appversion'] = "'$version'";
				$one['cnt'] = $cnt;

				$records[] = $one;
			}
		}
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

	$db_tbl = "$statdb_allserver.stats_version";
	$sql = sprintf($insertSql, $db_tbl);
//	echo $sql."\n";
	query_infobright_new($client,$sql);
}

remove_pid_file(MODULE);

function buildUpdateSql($kv){
	$all = array();
	foreach ($kv as $key => $value) {
		$all[] = "$key=$value";
	}
	return implode(',', $all);
}
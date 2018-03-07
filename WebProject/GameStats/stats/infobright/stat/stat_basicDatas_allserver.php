<?php 
//日期	平台	国家		新注册	日活跃  付费dau
//date	pf	country	        reg		dau     paid_dau
//这个表 都是基于stat_allserver统计表做出来的,所以referrer不用再经dau_referrer函数过滤了

defined('IB') || define('IB', dirname(__DIR__));
require_once IB.'/ib.inc.php';
if(isset($_REQUEST['fixdate'])){
	$req_date_end = $_REQUEST['fixdate'];
	$span = 9;
}else{
	$req_date_end = date('Ymd',time());
	$span = 9;
}
$req_date_end = str_replace('-', '', $req_date_end);
$req_date_start = date("Ymd", strtotime("-$span day",strtotime($req_date_end)));

$snapshotdb = IB_DB_NAME_SNAPSHOT;
$statdb = IB_DB_NAME_STAT;
$statdb_allserver = IB_DB_NAME_STAT_ALLSERVER;
$crossId=900000;

$s = microtime(true);
//这个函数不需要 每个字段加单引号 '',因为上边赋值时已加
if(!function_exists("buildUpdateSql")){
	function buildUpdateSql($kv){
		$all = array();
		foreach ($kv as $key => $value) {
			$all[] = "$key=$value";
		}
		return implode(',', $all);
	}
}
$client = getInfobrightConnect('stat_basicDatas_allserver.php');
if(!$client){
	echo 'mysql error stat_basicDatas_allserver.php'.PHP_EOL;
	return;
}
$maxsid = getMaxserver();
$sids = implode(',',range(1,$maxsid));
// ************** get countrys' DAU
$dauData=array();
$sql="select sid,referrer,country,pf,date,sum(dau) s_dau,sum(reg) s_reg,sum(replay) s_replay,sum(relocation) s_relocation,sum(paid_dau) as paid_dau,sum(deviceDau) as deviceDau,sum(totalDeviceDau) as totalDeviceDau from stat_allserver.stat_dau_daily_pf_country_referrer where sid in($sids) and date>=$req_date_start and date <$req_date_end group by country,pf,date,referrer,sid;";
$ret=query_infobright_new($client,$sql);
foreach ($ret as $value){
	$country=strtoupper($value['country']);
	$dauData[$country][$value['pf']][$value['date']][$value['referrer']][$value['sid']]['dau']+=$value['s_dau'];
	$dauData[$country][$value['pf']][$value['date']][$value['referrer']][$value['sid']]['reg']+=$value['s_reg'];
	$dauData[$country][$value['pf']][$value['date']][$value['referrer']][$value['sid']]['replay']+=$value['s_replay'];
	$dauData[$country][$value['pf']][$value['date']][$value['referrer']][$value['sid']]['relocation']+=$value['s_relocation'];
	$dauData[$country][$value['pf']][$value['date']][$value['referrer']][$value['sid']]['paid_dau']+=$value['paid_dau'];
	$dauData[$country][$value['pf']][$value['date']][$value['referrer']][$value['sid']]['deviceDau']+=$value['deviceDau'];
	$dauData[$country][$value['pf']][$value['date']][$value['referrer']][$value['sid']]['totalDeviceDau']+=$value['totalDeviceDau'];
	$dauData[$country][$value['pf']][$value['date']][$value['referrer']][$value['sid']]['sdau']+=$value['s_dau']-$value['s_reg']-$value['s_replay']-$value['s_relocation'];
}

$payData=array();
$sql = "select sid,referrer,country,pf,date,sum(payTotle) as payTotle,sum(payUsers) as payUsers,sum(payTimes) as payTimes,sum(firstPay) as firstPay from stat_allserver.pay_analyze_pf_country_referrer_new where sid in($sids) and date>=$req_date_start and date <$req_date_end GROUP BY country,pf,date,referrer,sid;";
$ret=query_infobright_new($client,$sql);
foreach ($ret as $value){
	$country=strtoupper($value['country']);
	$payData[$country][$value['pf']][$value['date']][$value['referrer']][$value['sid']]['payTotle']+=$value['payTotle'];
	$payData[$country][$value['pf']][$value['date']][$value['referrer']][$value['sid']]['payUsers']+=$value['payUsers'];
	$payData[$country][$value['pf']][$value['date']][$value['referrer']][$value['sid']]['payTimes']+=$value['payTimes'];
	$payData[$country][$value['pf']][$value['date']][$value['referrer']][$value['sid']]['firstPay']+=$value['firstPay'];
}
$dayArr = array(1,3,7);
foreach ($dayArr as $day) {
	$rfields[] = "sum(".'r'.$day.") as ".'r'.$day;
}
$fields = implode(',', $rfields);
$sql = "select sid,country,pf,date,$fields,referrer from stat_allserver.stat_retention_daily_pf_country_referrer_appVersion where sid in($sids) and date>=$req_date_start and date <$req_date_end and reg_all>0  group by country,pf,date,referrer,sid;";
$ret=query_infobright_new($client,$sql);
$retentionData=array();
foreach ($ret as $value){
	$country=strtoupper($value['country']);
	$retentionData[$country][$value['pf']][$value['date']][$value['referrer']][$value['sid']]['r1']+=$value['r1'];
	$retentionData[$country][$value['pf']][$value['date']][$value['referrer']][$value['sid']]['r3']+=$value['r3'];
	$retentionData[$country][$value['pf']][$value['date']][$value['referrer']][$value['sid']]['r7']+=$value['r7'];
}
$records = array();
foreach ($dauData as $countryKey=>$pfValue){
	foreach ($pfValue as $pfKey=>$dateValue){
		foreach ($dateValue as $dateKey=>$referrerValue){
			foreach ($referrerValue as $referrerKey=>$sidValue) {
				foreach($sidValue as  $sidKey=>$dbValue){
					$one = array();
					$one['date'] = $dateKey;
					$one['country'] = "'{$countryKey}'";
					$one['platform'] = "'{$pfKey}'";
					$one['referrer'] = "'{$referrerKey}'";
					$one['dau'] = intval($dauData[$countryKey][$pfKey][$dateKey][$referrerKey][$sidKey]['dau']);
					$one['dau_device'] = intval($dauData[$countryKey][$pfKey][$dateKey][$referrerKey][$sidKey]['deviceDau']);
					$one['totalDeviceDau'] = intval($dauData[$countryKey][$pfKey][$dateKey][$referrerKey][$sidKey]['totalDeviceDau']);
					$one['dau_paid'] = intval($dauData[$countryKey][$pfKey][$dateKey][$referrerKey][$sidKey]['paid_dau']);
					$one['olduser'] = intval($dauData[$countryKey][$pfKey][$dateKey][$referrerKey][$sidKey]['sdau']);
					$one['newuser'] = intval($dauData[$countryKey][$pfKey][$dateKey][$referrerKey][$sidKey]['reg']);
					$one['replay'] = intval($dauData[$countryKey][$pfKey][$dateKey][$referrerKey][$sidKey]['replay']);
					$one['relocation'] = intval($dauData[$countryKey][$pfKey][$dateKey][$referrerKey][$sidKey]['relocation']);
					$one['pay_amount'] = doubleval($payData[$countryKey][$pfKey][$dateKey][$referrerKey][$sidKey]['payTotle']);
					$one['pay_usernum'] = intval($payData[$countryKey][$pfKey][$dateKey][$referrerKey][$sidKey]['payUsers']);
					$one['pay_times'] = intval($payData[$countryKey][$pfKey][$dateKey][$referrerKey][$sidKey]['payTimes']);
					$one['pay_firstusernum'] = intval($payData[$countryKey][$pfKey][$dateKey][$referrerKey][$sidKey]['firstPay']);
					$one['pay_rate'] = 0;
					$one['arpu'] = 0;
					$one['r1'] = $retentionData[$countryKey][$pfKey][$dateKey][$referrerKey][$sidKey]['r1'] ? $retentionData[$countryKey][$pfKey][$dateKey][$referrerKey][$sidKey]['r1'] : 0.00;
					$one['r3'] = $retentionData[$countryKey][$pfKey][$dateKey][$referrerKey][$sidKey]['r3'] ? $retentionData[$countryKey][$pfKey][$dateKey][$referrerKey][$sidKey]['r3'] : 0.00;
					$one['r7'] = $retentionData[$countryKey][$pfKey][$dateKey][$referrerKey][$sidKey]['r7'] ? $retentionData[$countryKey][$pfKey][$dateKey][$referrerKey][$sidKey]['r7'] : 0.00;
					$one['sid'] = $sidKey;
					$records[] = $one;
				}
			}
		}
	}
}
	

//print_r($records);
foreach ($records as $fieldvalue) {
	$keys = array_keys($fieldvalue);
	$temp=$fieldvalue;
	$updKv = buildUpdateSql($temp);
	$f = join(',', $keys);
	$str = join(',', $fieldvalue);

	$insertSql = "INSERT into %s ($f) VALUES "." ($str) ";
	$ondup = 'ON DUPLICATE KEY UPDATE '.$updKv;
	$insertSql .= " $ondup;";

	$db_tbl = "$statdb_allserver.stat_basic";
	$sql = sprintf($insertSql, $db_tbl);
//	echo $sql."\n";
	query_infobright_new($client,$sql);
}


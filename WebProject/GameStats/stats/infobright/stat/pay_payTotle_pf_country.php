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
$client = getInfobrightConnect('pay_payTotle_pf_country.php');
if(!$client){
	echo 'mysql error pay_payTotle_pf_country.php'.PHP_EOL;
	return;
}
// **************//
$records = array();
$sql="select sum(p.spend) as payCount,p.date as payDate,p.pf as payChanel, r.pf as pf, r.country as country
from $snapshotdb.paylog p
left join (select distinct uid,pf,country from $snapshotdb.stat_reg) r on p.uid=r.uid
where p.date between $req_date_start and $req_date_end and p.pf !='iostest'
group by payDate,payChanel,pf,country;";


$ret=query_infobright_new($client,$sql);
$result=array();
foreach ($ret as $curRow){
	
	if($curRow['pf']==null){
		$pf='roll';
		$country='roll';
	}else{
		$pf=$curRow['pf'];
		if($curRow['country']==null){
			$country = "Unknown";
		}else{
			$country = $curRow['country'];
		}
	}
	$result[$curRow['payDate']][$curRow['payChanel']][$pf][$country]+=$curRow['payCount'];
}
//echo $sql;
foreach ($result as $dateKey=>$chanelPfCountryPaycount){
	foreach ($chanelPfCountryPaycount as $chanelKey=>$pfCountryPaycount){
		foreach ($pfCountryPaycount as $pfKey=>$countryPaycount){
			foreach ($countryPaycount as $countryKey=>$paycount){
				$one = array();
				$one['date']=$dateKey;
				$one['payChanel']="'{$chanelKey}'";
				$one['pf']="'{$pfKey}'";
				$one['country']="'{$countryKey}'";
				$one['payCount']=$paycount;
				$records[] = $one;
			}
		}
	}
}
	

//print_r($records);
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

	$db_tbl = "$statdb_allserver.pay_payTotle_pf_country";
	$sql = sprintf($insertSql, $db_tbl);
	query_infobright_new($client,$sql);
}



<?php 
//日期	平台	国家		新注册	日活跃
//date	pf	country	reg		dau

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

$s = microtime(true);

// ************** get countrys' DAU
$sql="select date,pf,country, count(distinct(uid)) reg from $snapshotdb.stat_reg where date between $req_date_start and $req_date_end group by date,pf,country;";
$ret = query_infobright($sql);
$temp=array();
foreach( $ret as $value){
	if($value['pf']==null){
		$pf='roll';
		$country='roll';
	}else{
		$pf=$value['pf'];
		if($value['country']==null){
			$country = "Unknown";
		}else{
			$country = $value['country'];
		}
	}
	$temp[$value['date']][$pf][$country]+=$value['reg'];
}
$sql = "SELECT l.date ,count(distinct(l.uid)) dau, r.pf, r.country from $snapshotdb.stat_login l left join $snapshotdb.stat_reg r on l.uid=r.uid where l.date between $req_date_start and $req_date_end group by date,pf,country";
echo $sql."\n";
$ret = query_infobright($sql);
// print_r($ret);
$tempDau=array();
foreach( $ret as $value){
	if($value['pf']==null){
		$currPf='roll';
		$currCountry='roll';
	}else{
		$currPf=$value['pf'];
		if($value['country']==null){
			$currCountry='Unknown';
		}else{
			$currCountry=$value['country'];
		}
	}
	$tempDau[$value['date']][$currPf][$currCountry]+=$value['dau'];
}	
$records = array();
foreach ($tempDau as $dateKey=>$pfCountryDau){
	foreach ($pfCountryDau as $pfKey =>$countryDau){
		foreach ($countryDau as $countryKey=>$dau){
			$one=array();
			$one['date'] = $dateKey;
			$one['pf'] = "'{$pfKey}'";
			$one['country'] = "'{$countryKey}'";
			$one['reg'] = intval($temp[$dateKey][$pfKey][$countryKey]);
			$one['dau'] =intval($dau);
			$records[] = $one;
		}
	}
}


// print_r($records);
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
	
	$db_tbl = "$statdb_allserver.stat_dau_daily_pf_country";
	$sql = sprintf($insertSql, $db_tbl);
	//echo $sql."\n";
	query_infobright($sql);
}





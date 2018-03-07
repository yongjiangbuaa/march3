<?php
// roi
if (isset ( $_REQUEST ['fixdate'] )) {
	$req_date_end = $_REQUEST ['fixdate'];
	$span = 0;
	$req_date_end = str_replace ( '-', '', $req_date_end );
	$req_date_start = date ( "Ymd", strtotime ( "-$span day", strtotime ( $req_date_end ) ) );
} else {
	$req_date_end = date ( 'Ymd', time () );
	$span = 1;
	$req_date_end = str_replace ( '-', '', $req_date_end );
	$req_date_start = date ( "Ymd", strtotime ( "-$span day", strtotime ( $req_date_end ) ) );
	$req_date_end = $req_date_start;
}

$snapshotdb = IB_DB_NAME_SNAPSHOT;
$statdb = IB_DB_NAME_STAT;
$statdb_allserver = IB_DB_NAME_STAT_ALLSERVER;

//
$s = microtime ( true );

$sql = "select sum(p.spend) sum,p.date as payDate,
u.date as regDate,r.country,r.pf 
from $snapshotdb.paylog p inner join $snapshotdb.userprofile u on p.uid = u.uid left join (select distinct uid,country,pf from $snapshotdb.stat_reg) r on p.uid=r.uid 
where  p.date >= $req_date_start and p.date <= $req_date_end 
group by regDate,payDate,country,pf order by p.time asc;";

$result = query_infobright ( $sql );
$records = array ();
foreach ( $result as $fields ) {
	if($fields['pf']==null){
		$pf='roll';
		$country='roll';
	}else{
		$pf=$fields['pf'];
		if($fields['country']==null){
			$country = "Unknown";
		}else{
			$country = $fields['country'];
		}
	}
	
	$one = array ();
	$one ['country'] = "'$country'";
	$one ['pf'] = "'$pf'";
	$one ['payDate'] = $fields ['payDate'];
	$one ['regDate'] = $fields ['regDate'];
	$one ['spendSum'] = number_format ( $fields ['sum'], 2, '.', '' );
	$records [] = $one;
}

foreach ( $records as $fieldvalue ) {
	$keys = array_keys ( $fieldvalue );
	$f = join ( ',', $keys );
	$str = join ( ',', $fieldvalue );
	
	$insertSql = "INSERT into %s ($f) VALUES " . " ($str) ";
	$ondup = 'ON DUPLICATE KEY UPDATE spendSum=spendSum+' . $fieldvalue ['spendSum'];
	$insertSql .= " $ondup;";
	
	$db_tbl = "$statdb_allserver.stat_roi_pf_country_v2";
	$sql = sprintf ( $insertSql, $db_tbl );
	// echo $sql,"\n";
	query_infobright ( $sql );
	// break;
}

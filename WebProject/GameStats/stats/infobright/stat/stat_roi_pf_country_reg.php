<?php 
//roi


if(isset($_REQUEST['fixdate'])){
	$req_date_end = $_REQUEST['fixdate'];
	$span = 0;
	$req_date_end = str_replace('-', '', $req_date_end);
	$req_date_start = date("Ymd", strtotime("-$span day",strtotime($req_date_end)));
}else{
	$req_date_end = date('Ymd',time());
	$span = 1;
	$req_date_end = str_replace('-', '', $req_date_end);
	$req_date_start = date("Ymd", strtotime("-$span day",strtotime($req_date_end)));
	$req_date_end=$req_date_start;
}
$req_date_end = str_replace('-', '', $req_date_end);
$req_date_start = date("Ymd", strtotime("-$span day",strtotime($req_date_end)));

$snapshotdb = IB_DB_NAME_SNAPSHOT;
$statdb = IB_DB_NAME_STAT;
$statdb_allserver = IB_DB_NAME_STAT_ALLSERVER;

//
$s = microtime(true);

// ************** get countrys' DAU
$loginArrDau = array();
/*mysql> select * from snapshot_s98.stat_reg limit 5;
+----------+-----------------+---------------+---------------+------+----------+---------+
| date     | uid             | time          | pf            | pfId | referrer | country |
+----------+-----------------+---------------+---------------+------+----------+---------+
 * */
$sql = "select count(*) sum,date as regDate,country,pf from $snapshotdb.stat_reg where type=0 and date >= $req_date_start and date <= $req_date_end group by regDate,country,pf";
$result = query_infobright($sql);
$records = array();
foreach ($result as $fields){
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
	$one=array();
	$one['regDate']=$fields['regDate'];
	$one['country']="'$country'";
	$one['pf']="'$pf'";
	$one['reg']=$fields['sum'];
	$records[]=$one;
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

	$db_tbl = "$statdb_allserver.stat_roi_pf_country_reg";
	$sql = sprintf($insertSql, $db_tbl);
	query_infobright($sql);
}

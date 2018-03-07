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
$sql = "select count(*) sum,date as regDate,country from $snapshotdb.stat_reg where type=0 and date >= $req_date_start and date <= $req_date_end group by regDate,country";
$result = query_infobright($sql);
foreach ($result as $fields){
    $country = "Unknown";
    if($fields['country']!=null && $fields['country']!=''){
        $country = $fields['country'];
    }
    $eventAllCountry[$country][$fields['regDate']]['reg'] += $fields['sum'];
    $eventAllCountry[$country][$fields['regDate']]['date'] += $fields['regDate'];
}
$records = array();
foreach ($eventAllCountry as $country=>$one) {
    foreach ($one as $date=>$temp) {
        $t['country'] = "'$country'";
        $t['date'] = $date;
        $t['reg'] = floatval($temp['reg']);
        $t['today'] =0;
        $t['3day'] = 0;
        $t['7day'] = 0;
        $t['15day'] = 0;
        $t['30day'] = 0;
        $t['allday'] = 0;
        $records[]=$t;
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

	$db_tbl = "$statdb_allserver.stat_roi_pf_country";
	$sql = sprintf($insertSql, $db_tbl);
// 	echo $sql,"\n";
	query_infobright($sql);
//    break;
}

function getSumPayDay($info,$today,$num){
    $sum =  $info[$today];
    $timestamp = strtotime($today);
    for($i=1;$i<$num;$i++){
        $key = date('Ymd',$timestamp + 3600 * 24 *$i);
        $sum += intval($info[$key]);
    }
    return $sum;
}
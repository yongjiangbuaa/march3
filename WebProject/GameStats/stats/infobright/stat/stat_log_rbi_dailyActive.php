<?php 

$span = 2;
if(isset($_REQUEST['fixdate'])){
	$req_date_end = $_REQUEST['fixdate'];
}else{
	$req_date_end = date('Y-m-d',time());
}
$req_date_start = date("Y-m-d", strtotime("-$span day",strtotime($req_date_end)));

$reg_date_end_ts = strtotime($req_date_end)*1000 + 86400 * 1000;//包含今天
$reg_date_start_ts = strtotime($req_date_start)*1000;

$statdb_allserver = IB_DB_NAME_STAT_ALLSERVER;

//$whereSql= "l.date>=str_to_date('$req_date_start','%Y-%m-%d') and l.date<=str_to_date('$req_date_end','%Y-%m-%d')";
$monthArr = monthList($reg_date_start_ts/1000,$reg_date_end_ts/1000);
$client = getInfobrightConnect('stat_log_rbi_dailyActive.php.php');
if(!$client){
	echo 'mysql error stat_log_rbi_dailyActive.php.php'.PHP_EOL;
	return;
}
$i = 0;
foreach ($monthArr as $i) {
	$db_start = 'coklog_function.function_log_' . $i;
	$sql = "select server_id,var_data1,var_data2,date,type,count(DISTINCT l.userid) as `sum`  from $db_start l
		where category=17 and (type=1 or type=2 or type=3) and l.date>=str_to_date('$req_date_start','%Y-%m-%d') and l.date<=str_to_date('$req_date_end','%Y-%m-%d')
		group by type,date,var_data1,var_data2,server_id ";
	if(isset($sql_sum)){
		$sql_sum = $sql_sum . " union " . $sql ;
	}else{
		$sql_sum = $sql;
	}
}
$retData =query_infobright_new($client,$sql_sum);
$coun = array();
foreach($retData as $users){
	$date = $users['date'];
	$sid = $users['server_id'];
	$activeId = $users['var_data1'];
	$version = isset($users['var_data2'])?$users['var_data2']:0;
	$type = $users['type'];
	$coun[$sid][$date][$version][$activeId][$type] += isset($users['sum'])?$users['sum']:0;
}
$records = array();
foreach($coun as $sid=>$datekey) {
	foreach ($datekey as $date => $versionkey) {
		foreach ($versionkey as $version1 => $idkey) {
			foreach ($idkey as $id => $play) {
				$one = array();
				$one['sid'] = $sid;
				$one['date'] = date("Ymd", strtotime($date));
				$one['appVersion'] = "'$version1'";
				$one['activeId'] = $id;
				$one['part'] = isset($play['1']) ? $play['1'] : 0;
				$one['complete'] = isset($play['2']) ? $play['2'] : 0;
				$one['reward'] = isset($play['3']) ? $play['3'] : 0;
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
//	$f = 'sid,'.$f;
//	$str = SERVER_ID.','.$str;

	$insertSql = "INSERT into %s ($f) VALUES "." ($str) ";
	$ondup = 'ON DUPLICATE KEY UPDATE '.$updKv;
	$insertSql .= " $ondup;";

	$db_tbl = "$statdb_allserver.stat_log_rbi_dailyActive";
	$sql = sprintf($insertSql, $db_tbl);
//	echo $sql."\n";
	query_infobright_new($client,$sql);
}


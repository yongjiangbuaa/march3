<?php 
if(isset($_REQUEST['fixdate'])){
	$orcs_date_end = $_REQUEST['fixdate'];
	$span = 0;
}else{
	$orcs_date_end = date('Ymd',time());
	$span = 1;
}
$orcs_date_end = str_replace('-', '', $orcs_date_end);
$orcs_date_start = date("Ymd", strtotime("-$span day",strtotime($orcs_date_end)));

$orcs_date_end_ts = strtotime($orcs_date_end)*1000 + 86400 * 1000;//包含今天
$orcs_date_start_ts = strtotime($orcs_date_start)*1000;

$statdb_allserver = IB_DB_NAME_STAT_ALLSERVER;//stat_allserver

/**
 * @attackUser 当天新注册用户中攻击了NPC城堡的人数
 * @countHit 当天NPC城堡被攻击的次数
 */
$table_start = date('Ym',$orcs_date_start_ts/1000);
$table_end = date('Ym',$orcs_date_end_ts/1000);
for ($i = $table_start; $i <= $table_end; $i++) {
	$db_start = 'coklog_function.function_log_' . $i;
	$sql="select server_id,date,count(distinct l.userid) attackUser from $db_start l
	where l.timestamp>=$orcs_date_start_ts and l.timestamp<$orcs_date_end_ts and category=15 and l.var_data1>=$orcs_date_start_ts and l.var_data1<$orcs_date_end_ts
 	group by date,server_id  ";
	if(isset($sql_sum)){
		$sql_sum = $sql_sum . " union " . $sql ;
	}else{
		$sql_sum = $sql;
	}
}
echo $sql_sum;
$orcs_Array =query_infobright($sql_sum);
$sql_sum = false;
for ($i = $table_start; $i <= $table_end; $i++) {
	$db_start = 'coklog_function.function_log_' . $i;
	$sum_sql="select server_id,date,count(1) countHit from $db_start l where l.timestamp>=$orcs_date_start_ts and l.timestamp<$orcs_date_end_ts and category=15 group by date,server_id ";
	if($sql_sum){
		$sql_sum = $sql_sum . " union " . $sum_sql ;
	}else{
		$sql_sum = $sum_sql;
	}
}
$sum_Array =query_infobright($sql_sum);

$half_orcs=array();
foreach ($sum_Array as $npcBuild_user){
	$records = array();
	$records['sid'] = $npcBuild_user['server_id'];
	$records['date'] = date('Ymd',strtotime($npcBuild_user['date']));
	$records['countHit'] = $npcBuild_user['countHit'];
	foreach($orcs_Array as $data){
		if($data['date']==$npcBuild_user['date']&&$data['server_id']==$npcBuild_user['server_id']){
			$records['attackUser']=$data['attackUser'];
		}
	}
	$half_orcs[] = $records;
}

// print_r($records);
foreach ($half_orcs as $fieldvalue) {
	$keys = array_keys($fieldvalue);
	$updKv = buildUpdateSql($fieldvalue);
	$f = join(',', $keys);
	$str = join(',', $fieldvalue);
//	$f = 'sid,'.$f;
//	$str = SERVER_ID.','.$str;

	$insertSql = "INSERT into %s ($f) VALUES "." ($str) ";
	$ondup = 'ON DUPLICATE KEY UPDATE '.$updKv;
	$insertSql .= " $ondup;";

	$db_tbl = "$statdb_allserver.stat_half_orcs_npcBuild";
	$sql = sprintf($insertSql, $db_tbl);
	echo $sql,"\n";
	query_infobright($sql);
}

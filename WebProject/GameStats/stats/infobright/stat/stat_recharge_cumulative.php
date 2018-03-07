<?php 
if(isset($_REQUEST['fixdate'])){
	$req_date_end = $_REQUEST['fixdate'];
}else{
	$req_date_end = date('Ymd',time());
}
$req_date_end = str_replace('-', '', $req_date_end);

$endTime=strtotime($req_date_end)*1000;
$startTime=$endTime-86400000;
$beforeDay=date('Ymd',$startTime/1000);

$snapshotdb = IB_DB_NAME_SNAPSHOT;
$statdb = IB_DB_NAME_STAT;
$statdb_allserver = IB_DB_NAME_STAT_ALLSERVER;
//
$s = microtime(true);
// **************



$server_list = get_db_list();
$slave_db_list = array();
foreach ($server_list as $one) {
	$slave_db_list[$one['db_id']] =$one;
}
$link = mysqli_connect($slave_db_list[SERVER_ID]['slave_ip_inner'],GAME_DB_SERVER_USER, GAME_DB_SERVER_PWD,$slave_db_list[SERVER_ID]['dbname'],$slave_db_list[SERVER_ID]['port']);
if(!$link){
	echo 'connect db serve error '.SERVER_ID.'--'.PHP_EOL;
	return;
}
$sql="select count( distinct uid) users,level,date_format(from_unixtime(`createTime`/1000),'%Y%m%d') date ,type from pay_total_log where createTime>=$startTime and createTime <$endTime group by date,level,type;";
$res = mysqli_query($link,$sql);
$records = array();
while($row = mysqli_fetch_assoc($res)){
	if ($row['date']!=$beforeDay){
		continue;
	}
	$one = array();
	$one['date']=$row['date'];
	$one['level']=$row['level'];
	$one['users']=$row['users'];
	$one['type']=$row['type'];
	$records[] = $one;
}
// print_r($records);
/////////////
$client = getInfobrightConnect('stat_recharge_cumulative.php');
if(!$client){
	echo 'mysql error stat_recharge_cumulative.php'.PHP_EOL;
	return;
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


	$db_tbl = "$statdb_allserver.stat_recharge_cumulative";
	$sql = sprintf($insertSql, $db_tbl);
	query_infobright_new($client,$sql);
}



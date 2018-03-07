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

$statdb_allserver = IB_DB_NAME_STAT_ALLSERVER;

$server_list = get_db_list();
$slave_db_list = array();
foreach ($server_list as $one) {
	$slave_db_list[$one['db_id']] =$one;
}
$records = array();
$link = mysqli_connect($slave_db_list[SERVER_ID]['slave_ip_inner'],GLOBAL_DB_SERVER_USER,GLOBAL_DB_SERVER_PWD,$slave_db_list[SERVER_ID]['dbname'],$slave_db_list[SERVER_ID]['port']);
$sql="select id,DATE_FORMAT(FROM_UNIXTIME(`completeTime`/1000),'%Y%m%d') date,count(distinct uid) users from user_achievement where state>0 and DATE_FORMAT(FROM_UNIXTIME(`completeTime`/1000),'%Y%m%d')=$beforeDay group by id,date";
$res = mysqli_query($link,$sql);

while($row = mysqli_fetch_assoc($res)){
	$one=array();
	$one['date']=$row['date'];
	$one['achieveId']=$row['id'];
	$one['users']=$row['users'];
	$records[] = $one;
}
$client = getInfobrightConnect('stat_achievement.php');
if(!$client){
	echo 'mysql error stat_achievement.php'.PHP_EOL;
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

	$db_tbl = "$statdb_allserver.stat_achievement";
	$sql = sprintf($insertSql, $db_tbl);
	//echo $sql."\n";
	query_infobright_new($client,$sql);
}

<?php
if(isset($_REQUEST['fixdate'])){
	$req_date_end = $_REQUEST['fixdate'];
}else{
	$req_date_end = date('Ymd',time());
}
$req_date_end = str_replace('-', '', $req_date_end);

$endTime=strtotime($req_date_end)*1000;
$startTime=$endTime-86400000;

$statdb_allserver = IB_DB_NAME_STAT_ALLSERVER;

$server_list = get_db_list();
$slave_db_list = array();
foreach ($server_list as $one) {
	$slave_db_list[$one['db_id']] =$one;
}
$records = array();
$link = mysqli_connect($slave_db_list[SERVER_ID]['slave_ip_inner'],'root','t9qUzJh1uICZkA',$slave_db_list[SERVER_ID]['dbname'],$slave_db_list[SERVER_ID]['port']);
$sql="select DATE_FORMAT(FROM_UNIXTIME(p.`time`/1000),'%Y%m%d') date,ph.model,ph.version,sum(p.spend) sumSpend from paylog p inner join stat_phone ph on p.uid=ph.uid where p.time between $startTime and $endTime and ph.model like '%iphone%' or ph.model like '%ipad%' group by date,model,version;";
//$sql="select DATE_FORMAT(FROM_UNIXTIME(p.`time`/1000),'%Y%m%d') date,ph.model,ph.version,sum(p.spend) sumSpend from paylog p inner join stat_phone ph on p.uid=ph.uid where p.time<=1426550400000 and ph.model like '%iphone%' or ph.model like '%ipad%' group by date,model,version;";
echo $sql;
$res = mysqli_query($link,$sql);

while($row = mysqli_fetch_assoc($res)){
	$one=array();
	$one['date']=$row['date'];
	$one['model']="'{$row['model']}'";
	$one['version']="'{$row['version']}'";
	$one['sumSpend']=$row['sumSpend'];
	$records[] = $one;
}
$client = getInfobrightConnect('stat_iosPay_allServer.php');
if(!$client){
	echo 'mysql error stat_iosPay_allServer.php'.PHP_EOL;
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

	$db_tbl = "$statdb_allserver.iosPay_allServer";
	$sql = sprintf($insertSql, $db_tbl);
	//echo $sql."\n";
	query_infobright_new($client,$sql);
}

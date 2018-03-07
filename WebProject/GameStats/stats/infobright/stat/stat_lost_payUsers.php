<?php
if(isset($_REQUEST['fixdate'])){
	$req_date_end = $_REQUEST['fixdate'];
	$span = 1;
}else{
	$req_date_end = date('Ymd',time());
	$span = 1;
}
$req_date_end = str_replace('-', '', $req_date_end);
$req_date_start = date("Ymd", strtotime("-$span day",strtotime($req_date_end)));

$endTime=strtotime($req_date_end)*1000;
$startTime=strtotime($req_date_start)*1000;
$before30day=$startTime-86400000*30;
$before15day=$startTime-86400000*15;

$nowTime=time()*1000;

$statdb_allserver = IB_DB_NAME_STAT_ALLSERVER;

$server_list = get_db_list();
$slave_db_list = array();
foreach ($server_list as $one) {
	$slave_db_list[$one['db_id']] =$one;
}
$records = array();
$link = mysqli_connect($slave_db_list[SERVER_ID]['slave_ip_inner'],GAME_DB_SERVER_USER, GAME_DB_SERVER_PWD,$slave_db_list[SERVER_ID]['dbname'],$slave_db_list[SERVER_ID]['port']);
if(!$link){
	echo 'connect db serve error '.SERVER_ID.'--'.PHP_EOL;
	return;
}

$sql="select ub.level blevel,case
when u.payTotal>0 and u.payTotal<=1000 then 1 
when u.payTotal>1000 and u.payTotal<=10000 then 2 
when u.payTotal>10000 and u.payTotal<=40000 then 3 
when u.payTotal>40000 and u.payTotal<=100000 then 4 
when u.payTotal>100000 and  u.payTotal<=200000 then 5 
when u.payTotal>200000 and  u.payTotal<=1000000 then 6 
when u.payTotal>1000000 and  u.payTotal<=2000000 then 7 
when u.payTotal>2000000 then 8 end as payLevel,count(distinct u.uid) cnt from userprofile u inner join user_building ub on u.uid=ub.uid where u.payTotal>0 and ub.itemId=400000 and u.regTime< $before30day and u.lastOnlineTime< $before15day and u.banTime<$nowTime and u.gmFlag !=1 group by blevel,payLevel order by blevel,payLevel";

$res = mysqli_query($link,$sql);

while($row = mysqli_fetch_assoc($res)){
	$one=array();
	$one['date']=$req_date_start;
	$one['blevel']=$row['blevel'];
	$one['payLevel']=$row['payLevel'];
	$one['cnt']=$row['cnt'];
	$records[] = $one;
}
$client = getInfobrightConnect('stat_lost_payUsers.php');
if(!$client){
	echo 'mysql error stat_lost_payUsers'.PHP_EOL;
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

	$db_tbl = "$statdb_allserver.stat_lost_payUsers";
	$sql = sprintf($insertSql, $db_tbl);
	//echo $sql."\n";
	query_infobright_new($client,$sql);
}

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
$link = mysqli_connect($slave_db_list[SERVER_ID]['slave_ip_inner'],'root','t9qUzJh1uICZkA',$slave_db_list[SERVER_ID]['dbname'],$slave_db_list[SERVER_ID]['port']);
$sql="select DATE_FORMAT(FROM_UNIXTIME(`createTime`/1000),'%Y%m%d') date,count(distinct uid) users,count(uid) times from notice_cost_log where DATE_FORMAT(FROM_UNIXTIME(`createTime`/1000),'%Y%m%d')=$beforeDay;";
$res = mysqli_query($link,$sql);

while($row = mysqli_fetch_assoc($res)){
	$one=array();
	$one['date']=$row['date'];
	$one['users']=$row['users'];
	$one['times']=$row['times'];
	$records[] = $one;
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

	$db_tbl = "$statdb_allserver.stat_noticeUsersAndTimes";
	$sql = sprintf($insertSql, $db_tbl);
	//echo $sql."\n";
	query_infobright($sql);
}

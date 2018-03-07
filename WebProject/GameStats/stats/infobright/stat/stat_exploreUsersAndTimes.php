<?php
if(isset($_REQUEST['fixdate'])){
	$req_date_end = $_REQUEST['fixdate'];
}else{
	$req_date_end = date('Ymd',time());
}
$req_date_end = str_replace('-', '', $req_date_end);

$endTime=strtotime($req_date_end)*1000;
$startTime=$endTime-86400000;
if ($startTime<1437019200000){
	$startTime=1437019200000;
}
$beforeDay=date('Ymd',$startTime/1000);

$statdb_allserver = IB_DB_NAME_STAT_ALLSERVER;

$server_list = get_db_list();
$slave_db_list = array();
foreach ($server_list as $one) {
	$slave_db_list[$one['db_id']] =$one;
}
$records = array();
$sql="select DATE_FORMAT(FROM_UNIXTIME(`time`/1000),'%Y%m%d') date,param,count(distinct uid) users,count(uid) times from logaction_v3  where action ='MarchWorldPoint' and time >=$startTime and time <$endTime and param!='N/A' group by date,param;";
$res = queryInfoBright3('coklog_s'.SERVER_ID,$sql);

foreach ($res['data'] as $row){
	if ($row['date']!=$beforeDay){
		continue;
	}
	$one=array();
	$one['date']=$row['date'];
	$one['param']=$row['param'];
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
	
	$db_tbl = "$statdb_allserver.stat_exploreUsersAndTimes";
	$sql = sprintf($insertSql, $db_tbl);
	//echo $sql."\n";
	query_infobright($sql);
}

/*function queryInfoBright3($db,$sql){
	$ret = array();
	$mysql = new mysqli('10.143.155.237', 'root', 't9qUzJh1uICZkA', $db, '5029');
	if($mysql->connect_errno){
		var_dump($mysql->connect_error);
		exit();
	}
	$result = $mysql->query($sql);
	if($result){
		$ret['num'] = $result->num_rows;
		while($row = $result->fetch_assoc()){
			$ret['data'][] = $row;
		}
	}
	$mysql->close();
	return $ret;
}*/
<?php

$statdb_allserver = IB_DB_NAME_STAT_ALLSERVER;

$server_list = get_db_list();
$slave_db_list = array();
foreach ($server_list as $one) {
	$slave_db_list[$one['db_id']] =$one;
}
$records = array();
$link = mysqli_connect($slave_db_list[SERVER_ID]['slave_ip_inner'],'root','t9qUzJh1uICZkA',$slave_db_list[SERVER_ID]['dbname'],$slave_db_list[SERVER_ID]['port']);
$sql="select type,count(uid) users from stat_reg group by type;";
$res = mysqli_query($link,$sql);
$regData=array();
while($row = mysqli_fetch_assoc($res)){
	if ($row['type']==0){
		$regData['newUsers']=$row['users'];
	}elseif ($row['type']==1){
		$regData['replay']=$row['users'];
	}else {
		$regData['relocation']=$row['users'];
	}
	$regData['users']+=$row['users'];
}

$sql="select sum(spend) paySum from paylog;";
$res = mysqli_query($link,$sql);
$payData=array();
while($row = mysqli_fetch_assoc($res)){
	$payData['paySum']=$row['paySum'];
}

$sql="select id,daoliangStart,daoliangEnd from server_info where id=".SERVER_ID." and daoliangStart>0;";
$globalLink = mysqli_connect($GLOBALS['slave_db_global']['host'],$GLOBALS['slave_db_global']['user'],$GLOBALS['slave_db_global']['password'],$GLOBALS['slave_db_global']['dbname'],$GLOBALS['slave_db_global']['port']);
$res = mysqli_query($globalLink,$sql);
while($row = mysqli_fetch_assoc($res)){
	$one=array();
	$one['days']=ceil((time()*1000-$row['daoliangStart'])/86400000);
	$one['users']=intval($regData['users']);
	$one['paySum']=$payData['paySum']?$payData['paySum']:0.00;
	$one['newUsers']=intval($regData['newUsers']);
	$one['replay']=intval($regData['replay']);
	$one['relocation']=intval($regData['relocation']);
	if ($row['daoliangEnd'] && (time()*1000)>=$row['daoliangEnd']){
		$one['daoliangDays']=ceil(($row['daoliangEnd']-$row['daoliangStart'])/86400000);
		$temp=date('Y.m.d',$row['daoliangStart']/1000).'-'.date('Y.m.d',$row['daoliangEnd']/1000);
		$one['daoliangPeriod']="'{$temp}'";
	}else {
		$one['daoliangDays']=ceil((time()*1000-$row['daoliangStart'])/86400000);
		$temp=date('Y.m.d',$row['daoliangStart']/1000).'-'.'';
		$one['daoliangPeriod']="'{$temp}'";
	}
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

	$db_tbl = "$statdb_allserver.stat_server_info";
	$sql = sprintf($insertSql, $db_tbl);
	//echo $sql."\n";
	query_infobright($sql);
}

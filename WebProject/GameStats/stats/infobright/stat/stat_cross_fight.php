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


//$leng=10800000;
if (SERVER_ID>=900001){
	$zlink = mysqli_connect($slave_db_list[SERVER_ID]['slave_ip_inner'],'root','t9qUzJh1uICZkA',$slave_db_list[SERVER_ID]['dbname'],$slave_db_list[SERVER_ID]['port']);
	
	$sql="select serverId,startTime,round,count(distinct uid) partUsers from user_cross_fight_history where startTime>=$startTime and startTime<$endTime group by serverId,startTime,round;";
	//echo $sql;
	$res = mysqli_query($zlink,$sql);
	$serverIdArray=array();
	$timeArray=array();
	$partUsersData=array();
	$roundArray=array();
	while($row = mysqli_fetch_assoc($res)){
		if ($row['serverId']<=0){
			continue;
		}
		if (!in_array($row['serverId'], $serverIdArray)){
			$serverIdArray[]=$row['serverId'];
		}
		if (!in_array($row['startTime'], $timeArray)){
			$timeArray[]=$row['startTime'];
		}
		if (!in_array($row['round'], $roundArray)){
			$roundArray[]=$row['round'];
		}
		$partUsersData[$row['startTime']][$row['round']][$row['serverId']]+=$row['partUsers'];
	}
	
	$sql="select serverId,startTime,count(distinct uid) processUsers from user_cross_fight_history where startTime>=$startTime and startTime<$endTime group by serverId,startTime;";
	$res = mysqli_query($zlink,$sql);
	while($row = mysqli_fetch_assoc($res)){
		$partUsersData[$row['startTime']][999][$row['serverId']]+=$row['processUsers'];
	}
	$roundArray[]=999;
	
	$permissionCount=array();
	foreach ($serverIdArray as $serverId){
		//$slink=mysqli_connect($slave_db_list[$serverId]['slave_ip_inner'],'root','t9qUzJh1uICZkA',$slave_db_list[$serverId]['dbname'],$slave_db_list[$serverId]['port']);
		//$sql="select count(u.uid) users from userprofile u inner join user_building ub on u.uid=ub.uid where ub.itemId=400000 and ub.level>=15;";
		//$sql="select count(distinct(uid)) dau from snapshot_s$serverId.stat_login_full where time>=$startTime and time<$endTime and castlelevel>=15;";
		
		$sql="select count(distinct(deviceId)) dau from snapshot_s$serverId.stat_login_full where time>=$startTime and time<$endTime and castlelevel>=15;";
		
		$ret = query_infobright($sql);
		//echo $sql;
		$permissionCount[$serverId]+=$ret[0]['dau'];
	}
	/*$goodsData=array();
	$goldData=array();
	foreach ($timeArray as $time){
		foreach ($roundArray as $round){
			
			$sTime=$time+($round-1)*$leng;
			$eTime=$time+$round*$leng;
			$sql="select itemId,count(uid) goodsCount from goods_cost_record where time>=$sTime and time< $eTime and type=1 group by itemId;";
			$res = mysqli_query($zlink,$sql);
			while($row = mysqli_fetch_assoc($res)){
				$goodsData[$time][$round][$row['param1']]=$row['goodsCount'];
			}
			
			$sql="select type,count(uid) goldCount,sum(cost) goldSum from gold_cost_record where time>=$sTime and time< $eTime and cost<0 group by type;";
			$res = mysqli_query($zlink,$sql);
			while($row = mysqli_fetch_assoc($res)){
				$goldData[$time][$round][$row['type']]['goldCount']=$row['goldCount'];
				$goldData[$time][$round][$row['type']]['goldSum']=$row['goldSum'];
			}
		}
	}*/
	
	//print_r($partUsersData);
	
	$records = array();
	foreach ($timeArray as $pTime){
		foreach ($roundArray as $rKey){
			foreach ($serverIdArray as $ser){
				$one=array();
				$one['startTime']=$pTime;
				$one['round']=$rKey;
				$one['serverId']=$ser;
				$one['partUsers']=$partUsersData[$pTime][$rKey][$ser];
				$one['permissionUsers']=$permissionCount[$ser];
				$records[]=$one;
			}
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
	
		$db_tbl = "$statdb_allserver.stat_cross_fight_users";
		$sql = sprintf($insertSql, $db_tbl);
		//echo $sql."\n";
		query_infobright($sql);
	}
	
}else {
	echo SERVER_ID."\n";
}

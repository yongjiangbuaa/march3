<?php 
//日期	平台	国家		新注册	日活跃  付费dau
//date	pf	country	        reg		dau     paid_dau

if(isset($_REQUEST['fixdate'])){
	$req_date_end = $_REQUEST['fixdate'];
	$span = 1;
}else{
	$req_date_end = date('Ymd',time());
	$span = 1;
}
$req_date_end = str_replace('-', '', $req_date_end);
$req_date_start = date("Ymd", strtotime("-$span day",strtotime($req_date_end)));

$snapshotdb = IB_DB_NAME_SNAPSHOT;
$statdb = IB_DB_NAME_STAT;
$statdb_allserver = IB_DB_NAME_STAT_ALLSERVER;

$server_list = get_db_list();
$slave_db_list = array();
foreach ($server_list as $one) {
	$slave_db_list[$one['db_id']] =$one;
}

$totalCount=array();
$sitesUser=array();
$siteScores=array();
$scores=array(
	'01'=>array(2,8,32,128,512,2048),
	'05'=>array(4,16,64,256,1024,4096),
	'10'=>array(7,28,112,448,1792,7168),
	'15'=>array(10,40,160,640,2560,10240),
	'20'=>array(13,52,208,832,3328,13312),
	'25'=>array(17,68,272,1088,4352,17408),
	'30'=>array(21,84,336,1344,5376,21504),
	'35'=>array(25,100,400,1600,6400,25600),
	'40'=>array(29,116,464,1856,7424,29696),
	'45'=>array(33,132,528,2112,8448,33792),
	'50'=>array(37,148,592,2368,9472,37888),
);

$link = mysqli_connect($slave_db_list[SERVER_ID]['slave_ip_inner'],'root','t9qUzJh1uICZkA',$slave_db_list[SERVER_ID]['dbname'],$slave_db_list[SERVER_ID]['port']);
$sql="select u.uid,case when u.payTotal>0 then 1 when u.payTotal<=0 then 0 end as paidFlag,ub.level,ue.itemId itemId,ue.on from userprofile u inner join user_building ub on u.uid=ub.uid left join user_equip ue on ub.uid=ue.uid where ub.itemId=400000 and ub.level>=6;";
//$sql="select u.uid,ue.itemId,ue.on from userprofile u left join user_equip ue on u.uid=ue.uid where u.level>6;";
$res = mysqli_query($link,$sql);
while($row = mysqli_fetch_assoc($res)){
	$itemId=$row['itemId'];
	$ubLevel=$row['level'];
	$paidFlag=$row['paidFlag'];
	$totalCount[$ubLevel][$paidFlag]+=1;
	if (!empty($itemId)){
		if (in_array(substr($itemId, 4,1),array(0,1,2,3,4,5)) && $row['on']==1){
			$sitesUser[$ubLevel][$paidFlag][substr($itemId, 4,1)]+=1;
			$level=substr($itemId, 2,2);
			$quality=substr($itemId, strlen($itemId)-1);
			$siteScores[$ubLevel][$paidFlag][substr($itemId, 4,1)]+=$scores[$level][$quality];
			continue;
		}
	}
}
$records = array();
foreach ($totalCount as $ublevKey=>$paidFlagValue){
	foreach ($paidFlagValue as $paidKey=>$dbValue){
		$one=array();
		$one['date'] = $req_date_start;
		$one['ublevel'] = intval($ublevKey);
		$one['paidFlag'] = $paidKey;
		$one['totalUsers'] = $dbValue;
		for ($i=0;$i<=5;$i++){
			$one['u'.$i] = intval($sitesUser[$ublevKey][$paidKey][$i]);
		}
		for ($i=0;$i<=5;$i++){
			$one['s'.$i] = intval($siteScores[$ublevKey][$paidKey][$i]);
		}
		$records[] = $one;
	}
}

//print_r($records);
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
	
	$db_tbl = "$statdb_allserver.stat_equipUsedTimes_daily";
	$sql = sprintf($insertSql, $db_tbl);
	//echo $sql."\n";
	query_infobright($sql);
}





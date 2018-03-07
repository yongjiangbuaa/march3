<?php 
if(isset($_REQUEST['fixdate'])){
	$req_date_end = $_REQUEST['fixdate'];
}else{
	$req_date_end = date('Ymd',time());
}
$req_date_end = str_replace('-', '', $req_date_end);

$endTime=$req_date_end;
$beforeDay=date('Ymd',strtotime($endTime)-86400);

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
$client = getInfobrightConnect('stat_exchange_pf_country_send.php');
if(!$client){
	echo 'mysql error stat_exchange_pf_country_send'.PHP_EOL;
	return;
}
//$link = mysqli_connect($slave_db_list[SERVER_ID]['slave_ip_inner'],'root','t9qUzJh1uICZkA',$slave_db_list[SERVER_ID]['dbname'],$slave_db_list[SERVER_ID]['port']);
//$sql="select date_format(from_unixtime(p.`time`/1000),'%Y%m%d') date,p.productId,r.country,r.pf,count(p.receiverId) num from paylog p inner join stat_reg r on p.uid=r.uid where p.time >=$startTime and p.time <$endTime and p.receiverId is not null and p.receiverId!='' GROUP BY date,productId,country,pf;";
//$res = mysqli_query($link,$sql);
$sql="select p.date,p.productId,r.country,r.pf,count(p.orderId) num from $snapshotdb.paylog p inner join $snapshotdb.stat_reg r on p.uid=r.uid where p.pf !='iostest' and p.date >=$beforeDay and p.date <=$endTime and p.orderId is not null and p.orderId!='' GROUP BY p.date,p.productId,r.country,r.pf;";
//echo $sql.PHP_EOL;
$res = query_infobright_new($client,$sql);
$sendNumArray=array();
foreach($res as $row){
	if($row['pf']==null){
		$pf='roll';
		$country='roll';
	}else{
		$pf=$row['pf'];
		if($row['country']==null){
			$country = "Unknown";
		}else{
			$country = $row['country'];
		}
	}
	$sendNumArray[$row['date']][$row['productId']][$country][$pf]=$row['num'];//每个礼包 卖出多少
}
$records = array();
$sql="select p.date,p.productId,r.country,r.pf,count(p.productId) num from $snapshotdb.paylog p inner join $snapshotdb.stat_reg r on p.uid=r.uid where p.pf !='iostest' and p.date >=$beforeDay and p.date <=$endTime GROUP BY p.date,p.productId,r.country,r.pf;";
$res = query_infobright_new($client,$sql);
$result=array();
foreach($res as $curRow){

	if($curRow['pf']==null){
		$pf='roll';
		$country='roll';
	}else{
		$pf=$curRow['pf'];

		if($curRow['country']==null){
			$country = "Unknown";
		}else{
			$country = $curRow['country'];
		}
	}
	$one = array();
	$one['date']=$curRow['date'];
	$one['productId']="'{$curRow['productId']}'";
	$one['pf']="'{$pf}'";
	$one['country']="'{$country}'";
	$one['num']=$curRow['num'];
	$one['sendNum']=intval($sendNumArray[$curRow['date']][$curRow['productId']][$country][$pf]); //这里num 和sendNum 正常情况下都是相等的
	$records[] = $one;
	
}
//echo $sql.PHP_EOL;

// print_r($records);
/////////////

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

	$db_tbl = "$statdb_allserver.stat_exchange_pf_country_send";
	$sql = sprintf($insertSql, $db_tbl);

	query_infobright_new($client,$sql);
}


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

$link = mysqli_connect($slave_db_list[SERVER_ID]['slave_ip_inner'],'root','t9qUzJh1uICZkA',$slave_db_list[SERVER_ID]['dbname'],$slave_db_list[SERVER_ID]['port']);
$sql="select allianceId, count(*) allinum from alliance_territory where pointId is not null group by allianceId;";
$res = mysqli_query($link,$sql);
$allianceNum=0;
$territoryNum=0;
while($row = mysqli_fetch_assoc($res)){
	$territoryNum+=$row['allinum'];
	$allianceNum+=1;
}


$server_list = get_server_list();
foreach ( $server_list as $server) {
	if ($server['svr_id']==SERVER_ID){
		$clientTemp = new Redis();
		$clientTemp->connect($server['ip_inner']);
		$attackTimes= $clientTemp->lLen('TERRITORY_CRASH_RECORD');
		$callBackTimes=$clientTemp->lLen('TERRITORY_FIGHT_'.SERVER_ID);
		$clientTemp->close();
	}
}
//echo $territoryNum.','.$territoryNum.','.$attackTimes.','.$callBackTimes."\n";

$sql="select type,count(distinct allianceId) numCount from alliance_territory where type=18 union select type,count(distinct allianceId) numCount from alliance_territory where type=21;";
$res = mysqli_query($link,$sql);
$ironCount=0;
$warehouseCount=0;
while($row = mysqli_fetch_assoc($res)){
	if ($row['type']==18){
		$ironCount+=$row['numCount'];  //拥有联盟超级矿的联盟数量
	}elseif ($row['type']==21){
		$warehouseCount+=$row['numCount'];   //拥有联盟仓库的联盟数量
	}
}

$sql="select allianceId, count(*) towernum from alliance_territory where type=20 group by allianceId;";
$res = mysqli_query($link,$sql);
$towerNum=0;
$allianceTowerNum=0;
while($row = mysqli_fetch_assoc($res)){
	$towerNum+=$row['towernum'];     //联盟箭塔数量
	$allianceTowerNum+=1;          //拥有联盟箭塔的联盟数量
}

$records = array();
$one=array();
$one['territoryNum']=$territoryNum;
$one['allianceNum']=$allianceNum;
$one['attackTimes']=$attackTimes;
$one['callBackTimes']=$callBackTimes;
$one['ironCount']=$ironCount;
$one['warehouseCount']=$warehouseCount;
$one['towerNum']=$towerNum;
$one['allianceTower']=$allianceTowerNum;

$records[] = $one;
$client = getInfobrightConnect('stat_alliance_territory.php');
if(!$client){
	echo 'mysql error stat_alliance_territory.php'.PHP_EOL;
	return;
}
foreach ($records as $fieldvalue) {
	$keys = array_keys($fieldvalue);
	$f = join(',', $keys);
	$str = join(',', $fieldvalue);
	$f = 'sid,'.$f;
	$str = SERVER_ID.','.$str;

	$insertSql = "INSERT into %s ($f) VALUES "." ($str) ";
	$ondup = "ON DUPLICATE KEY UPDATE territoryNum=$territoryNum,allianceNum=$allianceNum,attackTimes=$attackTimes,callBackTimes=$callBackTimes,ironCount=$ironCount,warehouseCount=$warehouseCount,towerNum=$towerNum,allianceTower=$allianceTowerNum;";
	$insertSql .= " $ondup;";
	
	//echo $insertSql."\n";
	
	$db_tbl = "$statdb_allserver.stat_alliance_territory";
	$sql = sprintf($insertSql, $db_tbl);
	//echo $sql."\n";
	query_infobright_new($client,$sql);
}

<?php 
//日期	平台	国家		新注册	日活跃  付费dau
//date	pf	country	        reg		dau     paid_dau

if(isset($_REQUEST['fixdate'])){
	$req_date_end = $_REQUEST['fixdate'];
}else{
	$req_date_end = date('Ymd',time());
}
$req_date_end = str_replace('-', '', $req_date_end);

$endTime=date('Ymd',(strtotime($req_date_end)-86400));
$startTime=$endTime;

$snapshotdb = IB_DB_NAME_SNAPSHOT;
$statdb = IB_DB_NAME_STAT;
$statdb_allserver = IB_DB_NAME_STAT_ALLSERVER;

$s = microtime(true);
$client = getInfobrightConnect('stat_hot_goods_cost_record2.php');
if(!$client){
	echo 'mysql error stat_hot_goods_cost_record2.php'.PHP_EOL;
	return;
}

$sql="select buyTime, goodsId, price, priceType, count(*) num from $snapshotdb.hot_goods_cost_record where buyTime =$endTime group by goodsId,price order by priceType,price,num desc;";
//$sql="select buyTime, goodsId, price, priceType, count(*) num from $snapshotdb.hot_goods_cost_record where buyTime <=20150318 group by buyTime,goodsId,price order by priceType,price,num desc;";
$ret = query_infobright_new($client,$sql);
$numArray=array();
foreach( $ret as $value){
	$numArray[$value['buyTime']][$value['goodsId']][$value['price']]['num']+=$value['num'];
	$numArray[$value['buyTime']][$value['goodsId']][$value['price']]['priceType']=$value['priceType'];
}

$usersArray=array();
$sql="select count(distinct uid) people,goodsId,price,buyTime from $snapshotdb.hot_goods_cost_record where buyTime =$endTime group by goodsId,price;";
//$sql="select count(distinct uid) people,goodsId,price,buyTime from $snapshotdb.hot_goods_cost_record where buyTime <=20150318 group by buyTime,goodsId,price;";
$ret = query_infobright_new($client,$sql);
foreach( $ret as $value){
	$usersArray[$value['buyTime']][$value['goodsId']][$value['price']] += $value['people'];
}

$reTimesArray=array();
$sql="select date,count(goodsId) reTimes,goodsId,price from $snapshotdb.hot_info_before_refresh where date =$endTime group by goodsId,price;";
$ret = query_infobright_new($client,$sql);
foreach( $ret as $value){
	$reTimesArray[$value['date']][$value['goodsId']][$value['price']] += $value['reTimes'];
}

$records = array();
foreach ($numArray as $dateKey=>$goodsIdValue){
	foreach ($goodsIdValue as $goodsIdKey=>$priceValue){
		foreach ($priceValue as $priceKey=>$value){
			$one=array();
			$one['buyTime']=$dateKey;
			$one['goodsId']="'{$goodsIdKey}'";
			$one['price']=$priceKey;
			$one['priceType']=$value['priceType'];
			$one['num']=$value['num'];
			$one['people']=intval($usersArray[$dateKey][$goodsIdKey][$priceKey]);
			$one['reTimes']=intval($reTimesArray[$dateKey][$goodsIdKey][$priceKey]);
			$records[] = $one;
		}
	}
}



// print_r($records);
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
	
	$db_tbl = "$statdb_allserver.stat_hot_goods_cost_record2";
	$sql = sprintf($insertSql, $db_tbl);
	query_infobright_new($client,$sql);
}





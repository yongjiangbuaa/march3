<?php 
//日期	用户数	次数	            金币数目
//date	users	times	sumc


if(isset($_REQUEST['fixdate'])){
	$req_date_end = $_REQUEST['fixdate'];
	$span = 0;
}else{
	$req_date_end = date('Ymd',time());
	$span = 1;
}
$req_date_end = str_replace('-', '', $req_date_end);
$req_date_start = date("Ymd", strtotime("-$span day",strtotime($req_date_end)));

$snapshotdb = IB_DB_NAME_SNAPSHOT;
$statdb = IB_DB_NAME_STAT;
$statdb_allserver = IB_DB_NAME_STAT_ALLSERVER;
$client = getInfobrightConnect('pay_goldStatistics_daily_groupByType.php');
if(!$client){
	echo 'mysql error pay_goldStatistics_daily_groupByType.php'.PHP_EOL;
	return;
}
$s = microtime(true);
// **************
$sql = "select date,case when payTotal>0 then 1 when payTotal<=0 then 0 end as paidFlag,type,count(distinct(userId)) as users,count(userId) as times,sum(cost) sumc 
	from $snapshotdb.gold_cost_record_full 
	where date between $req_date_start and $req_date_end and gmflag != 1 and gmflag != 10
	group by date,paidFlag,type";
$ret = query_infobright_new($client,$sql);
$records = array();
foreach ($ret as $fields){
	$one = array();
	$one['date'] = $fields['date'];
	$one['paidFlag'] = $fields['paidFlag'];
	$one['type'] = $fields['type'];
	$one['users'] = $fields['users'];
	$one['times'] = $fields['times'];
	$one['sumc'] = $fields['sumc'];
	$records[] = $one;
}

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
	
	$db_tbl = "$statdb_allserver.pay_goldStatistics_daily_groupByType";
	$sql = sprintf($insertSql, $db_tbl);
	query_infobright_new($client,$sql);
}

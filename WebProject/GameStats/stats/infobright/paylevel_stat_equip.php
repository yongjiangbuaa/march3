<?php
//装备统计, 不能按日期运行,每天只能运行一次;只能统计当前装备状态
defined('IB_ROOT') || define('IB_ROOT', __DIR__);

require_once IB_ROOT . '/ib.inc.php';

ini_set('memory_limit', '2048M');

define('MODULE',basename(__FILE__, '.php'));
//参数只要name
if(!write_pid_file(MODULE)){
	return;
}
file_put_contents($pidFile,SERVER_ID);

$req_date_end = date('Ymd',time());
$span = 0;//只能统计当前装备状态

$req_date_end = str_replace('-', '', $req_date_end);
$req_date_start = date("Ymd", strtotime("-$span day",strtotime($req_date_end)));


//date,serverid,level领主等级,paylevel,itemid,count,allpeople
$client = getInfobrightConnect('paylevel_stat_equip.php');
if(!$client){
	echo 'mysql error paylevel_stat_equip.php'.PHP_EOL;
	return;
}
$statdb_allserver = IB_DB_NAME_STAT_ALLSERVER;
if(!isset($slave_db_list)){
	$server_list = get_db_list();
	$slave_db_list = array();
	foreach ($server_list as $one) {
		$slave_db_list[$one['db_id']] =$one;
	}
}

$link = mysqli_connect($slave_db_list[SERVER_ID]['slave_ip_inner'],GLOBAL_DB_SERVER_USER,GLOBAL_DB_SERVER_PWD,$slave_db_list[SERVER_ID]['dbname'],$slave_db_list[SERVER_ID]['port']);

//这主要是统计 付费等级,装备,个数,人数
$sql="select $req_date_start as date,
case
when p.allpay<=0 or p.uid is null then 0
when p.allpay>0 and p.allpay<=5 then 1
when p.allpay>5 and p.allpay<=500 then 2
when p.allpay>500 and p.allpay<=1000 then 3
when p.allpay>1000 and p.allpay<=5000 then 4
when p.allpay>5000 and  p.allpay<=10000 then 5
when p.allpay>10000 and  p.allpay<=20000 then 6
when p.allpay>20000 and  p.allpay<=30000 then 7
when p.allpay>30000 then 8 end as payLevel,u.level, ue.itemid ,count(1) cnt,count(DISTINCT ue.uid) cntusers
from userprofile u
INNER JOIN user_equip ue on ue.uid=u.uid
left join (select uid,sum(spend) allpay from paylog where pf!='iostest' and from_unixtime(time/1000,'%Y%m%d')<=$req_date_end group by uid) p on p.uid=u.uid
where u.banTime <2422569600000 and u.gmFlag != 1
group by payLevel,u.level,ue.itemid";

$res = mysqli_query($link,$sql);
$data = array();
while($row = mysqli_fetch_assoc($res)){
	$date=$row['date'];
	$level=$row['level'];
	$paylevel=$row['payLevel'];

	$data[$date][$level][$paylevel][$row['itemid']]['cnt'] = $row['cnt'];
	$data[$date][$level][$paylevel][$row['itemid']]['cntusers'] = $row['cntusers'];
}
//统计当日 不同付费等级,不同领主等级人数
$sql="select $req_date_start as date,
case
when p.allpay<=0 or p.uid is null then 0
when p.allpay>0 and p.allpay<=5 then 1
when p.allpay>5 and p.allpay<=500 then 2
when p.allpay>500 and p.allpay<=1000 then 3
when p.allpay>1000 and p.allpay<=5000 then 4
when p.allpay>5000 and  p.allpay<=10000 then 5
when p.allpay>10000 and  p.allpay<=20000 then 6
when p.allpay>20000 and  p.allpay<=30000 then 7
when p.allpay>30000 then 8 end as payLevel,u.level,count(1) allpeople
from userprofile u
left join (select uid,sum(spend) allpay from paylog where pf!='iostest' and from_unixtime(time/1000,'%Y%m%d')<=$req_date_end group by uid) p on p.uid=u.uid
where u.banTime <2422569600000 and u.gmFlag != 1
group by payLevel,u.level";


$res = mysqli_query($link,$sql);

$lordArr = array();
while($row = mysqli_fetch_assoc($res)){
	$date=$row['date'];
	$level=$row['level'];
	$paylevel = $row['payLevel'];

	$lordArr[$date][$level][$paylevel]['allpeople'] = $row['allpeople'];
}


$records = array();
foreach ($data as $dateKey=>$leveldata){
	foreach ($leveldata as $levelkey =>$payleveldata){
		foreach ($payleveldata as $paylevel=>$itemiddata){
			foreach($itemiddata as $itemid=>$item) {
				$one = array();
				$one['date'] = $dateKey;
				$one['level'] = intval($levelkey);
				$one['paylevel'] = intval($paylevel);
				$one['itemid'] = intval($itemid);

				$one['cnt'] = $item['cnt'];
				$one['users'] = $item['cntusers'];
				$one['allpeople'] = $lordArr[$dateKey][$levelkey][$paylevel]['allpeople'];

				$records[] = $one;
			}
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

	$db_tbl = "$statdb_allserver.pay_equip_level";
	$sql = sprintf($insertSql, $db_tbl);
	//echo $sql."\n";
	query_infobright_new($client,$sql);
}


if(file_exists($pidFile)){
	unlink($pidFile);
	writeRunLog("================ unlink paylevel_stat_equip pid file '".SERVER_ID."' ===============");
}
remove_pid_file(MODULE);

function buildUpdateSql($kv){
	$all = array();
	foreach ($kv as $key => $value) {
		$all[] = "$key=$value";
	}
	return implode(',', $all);
}
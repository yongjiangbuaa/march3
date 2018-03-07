<?php
error_reporting(E_ALL);
defined('STATS_ROOT') || define('STATS_ROOT', dirname(__DIR__));
include STATS_ROOT . '/stats.inc.php';

require_once STATS_ROOT .'/infobright/ib.inc.php';

$server_list = get_db_list();

// print_r($server_list);
$slave_db_list = array();
foreach ($server_list as $one) {
	$slave_db_list[$one['db_id']] =$one;
}
$pfArray=array(
		'cn_360','cn_am','cn_anzhi','cn_baidu','cn_dangle','cn_ewan','cn_huawei','cn_kugou','cn_kupai','cn_lenovo','cn_mi','cn_mihy','cn_mzw','cn_nearme','cn_pps','cn_pptv','cn_sogou','cn_toutiao','cn_uc','cn_vivo','cn_wdj','cn_wyx','cn_youku','cn_oppo','cn_sy37','cn_mz','tencent'
);
$pfmap_payvsreg = array(
		'cn_360' => 'cn_360',
		'cn_amigo' => 'cn_am',
		'cn_anzhi' => 'cn_anzhi',
		'cn_baidu' => 'cn_baidu',
		'cn_dangle' => 'cn_dangle',
		'cn_yiwan' => 'cn_ewan',
		'cn_huawei' => 'cn_huawei',
		'cn_kugou' => 'cn_kugou',
		'cn_lenovo' => 'cn_lenovo',
		'cn_mi' => 'cn_mi',
		'cn_mihy' => 'cn_mihy',
		'cn_mzw' => 'cn_mzw',
		'cn_oppo' => 'cn_nearme',
		'cn_pptv' => 'cn_pptv',
		'cn_sogou' => 'cn_sogou',
		'cn_jinritoutiao' => 'cn_toutiao',
		'cn_uc' => 'cn_uc',
		'cn_vivo' => 'cn_vivo',
		'cn_wandoujia' => 'cn_wdj',
		'cn_sina' => 'cn_wyx',
		'cn_tudou' => 'cn_youku',
		'tencent' => 'tencent',
		'cn_coolpad' => 'cn_kupai',
		'cn_pps' => 'cn_pps',
		'cn_37wan' => 'cn_sy37',
		'cn_meizu' => 'cn_mz',
);

$alldata=array();
date_default_timezone_set('Asia/Shanghai');
if(date('H')==00){
	$startTime=strtotime(date('Ymd'))*1000-86400000;
	$endTime=strtotime(date('Ymd'))*1000;
}else {
	$startTime=strtotime(date('Ymd'))*1000;
	$endTime=$startTime+86400000;
}

if(date('Ym',$startTime/1000)==date('Ym',$endTime/1000)){
	$yearMonth[]=date('Y',$startTime/1000).'_'.(date('m',$startTime/1000)-1);
}else {
	$yearMonth[]=date('Y',$startTime/1000).'_'.(date('m',$startTime/1000)-1);
	$yearMonth[]=date('Y',$endTime/1000).'_'.(date('m',$endTime/1000)-1);
}

foreach ($slave_db_list as $sidKey =>$DBvalue){
	$sid=$sidKey;
	if ($sid==20||$sid==35){
		continue;
	}
	if(empty($sid)) 
		exit('no sid');
	$link = mysqli_connect($slave_db_list[$sid]['slave_ip_inner'],'root','t9qUzJh1uICZkA',$slave_db_list[$sid]['dbname'],$slave_db_list[$sid]['port']);
	$sql = "select pf,count(uid) cnt from stat_reg where type=0 and time>=$startTime and time<$endTime group by pf;";
	$res = mysqli_query($link,$sql);
	while($row = mysqli_fetch_assoc($res)){
		$alldata[$row['pf']]['reg'] += $row['cnt'];
	}
	$sql = $sql = "select p.pf,count(distinct p.uid) ucnt,sum(
			case 
				when p.spend=0.99 then 6
				when p.spend=4.99 then 30 
				when p.spend=9.99 then 68 
				when p.spend=19.99 then 128 
				when p.spend=24.99 then 163 
				when p.spend=49.99 then 328 
				when p.spend=99.99 then 648 
				when p.spend=999.99 then 6498 
			end 
			) psum
			from paylog p
			where p.time>=$startTime and p.time<$endTime
			group by p.pf;";
	$res = mysqli_query($link,$sql);
	while($row = mysqli_fetch_assoc($res)){
		$regpf = isset($pfmap_payvsreg[$row['pf']]) ? $pfmap_payvsreg[$row['pf']] : $row['pf'];
		$alldata[$regpf]['payucnt'] += $row['ucnt'];
		$alldata[$regpf]['paysum'] += $row['psum'];
	}
	foreach ($yearMonth as $ym){
		$sql = "select count(distinct(uid)) dau,pf from stat_login_$ym where time >=$startTime and time<$endTime group by pf;";
		$res = mysqli_query($link,$sql);
		while($row = mysqli_fetch_assoc($res)){
			$alldata[$row['pf']]['dau'] += $row['dau'];
		}
	}
}
$final=array();
foreach ($alldata as $pf => $pfdata) {
	if ('cn_' != substr($pf, 0, 3) && 'tencent' != $pf) {
	//if (!in_array($pf, $pfArray)){
		continue;
	}
	$one = array();
	$one['date']=date('Ymd',$startTime/1000);
	$one['pf'] = "'{$pf}'";
	$one['dau'] = intval($pfdata['dau']);
	$one['reg'] = intval($pfdata['reg']);
	$one['payucnt'] = intval($pfdata['payucnt']);
	$one['paysum'] = floatval($pfdata['paysum']);
	$final[] = $one;
}

date_default_timezone_set('UTC');

foreach ($final as $fieldvalue) {
	$keys = array_keys($fieldvalue);
	$updKv = buildUpdateSql($fieldvalue);
	$f = join(',', $keys);
	$str = join(',', $fieldvalue);

	$insertSql = "INSERT into cn_regPay ($f) VALUES "." ($str) ";
	$ondup = 'ON DUPLICATE KEY UPDATE '.$updKv;
	$insertSql .= " $ondup;";

	$globalLink=mysqli_connect('IP','用户','密码','库名');
	$res = mysqli_query($globalLink,$insertSql);
	
}

function buildUpdateSql($kv){
	$all = array();
	foreach ($kv as $key => $value) {
		$all[] = "$key=$value";
	}
	return implode(',', $all);
}

// alter table `cn_regPay` Add column dau int(11) not null default 0 AFTER `pf`;

// CREATE TABLE `cn_regPay` (
// 	`date` int(8) NOT NULL,
// 	`pf` varchar(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
// 	`reg` int(11) DEFAULT 0,
// 	`payucnt` int(11) DEFAULT 0,
// 	`paysum` int(11) DEFAULT 0,
// 	PRIMARY KEY (`date`,`pf`)
// ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

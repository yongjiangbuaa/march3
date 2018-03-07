<?php
define('IN_ADMIN',true);
define('APP_ROOT', realpath(dirname(__DIR__) . '/../gameengine/test'));
define('ADMIN_ROOT', realpath(dirname(__DIR__) . '/'));
ini_set('mbstring.internal_encoding','UTF-8');
include ADMIN_ROOT.'/config.inc.php';
include ADMIN_ROOT.'/admins.php';
include_once ADMIN_ROOT.'/servers.php';
$page = new BasePage();
date_default_timezone_set('UTC');
set_time_limit(0);

global $servers;
$deviceIdArray=array();

$file="/tmp/lostUsers_wuni_20150813.log";
file_put_contents($file,"uid,level,支付总金币,最后登录日期,国家,邮箱\n" ,FILE_APPEND);
foreach ($servers as $server=>$serInfo){
	
	$sql="select u.uid,u.level,u.payTotal,date_format(from_unixtime(u.lastOnlineTime/1000),'%Y-%m-%d %H:%i:%s') lastDate,u.gmail,r.country from userprofile u inner join stat_reg r on u.uid=r.uid where u.regTime>=1433116800000 and u.regTime<1435708800000 and u.payTotal>0 and u.lastOnlineTime<1438819200000 and r.country in('AZ','BY','RU','UA','UZ','KZ');";
	$result = $page->executeServer($server, $sql, 3);
	$str='';
	foreach ($result['ret']['data'] as $row){
		$globalRet=cobar_getAccountInfoByGameuids($row['uid']);
		if ('s'.$globalRet[0]['server']!=$server){
			continue;
		}
		$temp=$row['uid'].','.$row['level'].','.$row['payTotal'].','.$row['lastDate'].','.$row['country'];
		if ($row['gmail']){
			$str.=$temp.','.$row['gmail']."\n";
		}else {
			if ($globalRet[0]['googleAccountName']){
				$str.=$temp.','.$globalRet[0]['googleAccountName']."\n";
			}else {
				$str.='';
			}
		}
	}
	file_put_contents($file, $str,FILE_APPEND);
}
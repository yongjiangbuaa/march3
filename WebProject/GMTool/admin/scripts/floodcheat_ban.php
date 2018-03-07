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
ini_set('memory_limit', '1024M');

global $servers;
$eventAll = array();

$redis = new Redis();
$redis->connect('10.81.103.90',6379);
$uidlist = $redis->hGetAll('h_flood_cheat');
$banneduidlist = $redis->hGetAll('h_flood_cheat_banned');

foreach ($uidlist as $uid=>$cnt) {
	if (isset($banneduidlist[$uid])) {
		continue;
	}
	
	$accall = cobar_getAccountInfoByGameuids($uid);
	$acc = $accall[0];
	$server = "s{$acc['server']}";
	echo "$server $uid\n";
	
	$opeDate=date('Y-m-d H:i:s');
	$time=time()*1000;
	$serverId=$acc['server'];
	$uuid=md5($serverId.$uid.$time);
	$sql="insert into ban_record(uuid,serverId,uid,time,operator,reason,opDate) values('$uuid',$serverId,'$uid',$time,'系统','行军作弊','$opeDate')";
	$page->globalExecute($sql, 2);
	
	$reasonSql="insert into banTime_reason(serverId,uid,operator,reason,opeDate,status) values('s$serverId','$uid','系统','行军作弊','$opeDate',1) ON DUPLICATE KEY UPDATE operator='系统',reason='行军作弊',opeDate='$opeDate',status=1;";
	$page->globalExecute($reasonSql, 2);
	
//	$taskSql="update user_task set id=CONCAT(id,'_ban') where uid='$uid' and state=0 limit 1;";
//	$page->executeServer($server,$taskSql, 2);
	
	$sql = "select pointid from user_world where uid='$uid'";
	$result = $page->executeServer($server,$sql, 3,true);
	$pointid = $result['ret']['data'][0]['pointid'];
	$currserver = $server;
	$serverinfo = $servers[$currserver];
	$ip = $serverinfo['ip_inner'];
	$rediskey = 'world'.substr($currserver, 1);
	$redissfs = new Redis();
	$redissfs->connect($ip,6379);
	$redissfs->hDel($rediskey, $pointid);
	
	$sql="update worldpoint set pointType=8 where id=$pointid;";
	$re = $page->executeServer($server,$sql,2);

	$sql = "update userprofile set banTime=9223372036854775806 where uid ='$uid'";
	$re = $page->executeServer($server,$sql,2);
	cobar_query_global_db_cobar("update account_new set active = 1 where gameUid = '{$uid}'");
	
	$redis->hSetNx('h_flood_cheat_banned', $uid, date('Y-m-d H:i:s'));
	file_put_contents('/data/log/flood_cheat_banned.log', date('Y-m-d H:i:s')." $server $uid\n", FILE_APPEND);
}


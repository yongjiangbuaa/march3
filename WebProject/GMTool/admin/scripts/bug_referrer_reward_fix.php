<?php
//added by qinbin
// 20160712

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
ini_set('memory_limit', '512M');

$servers = array(37,41,42,43,44,45,46); //服

$people=array();
foreach ($servers as $sid) {
	$server = 's' . $sid;

	$sql = "select uid from stat_reg where type=0 and referrer in ('Appturbo-ios','Appturbo-Android','Huawei') and time<=1469190779000";
	$resultsel = $page->executeServer($server, $sql, 3);

	foreach ($resultsel['ret']['data'] as $cow) {
		$people[] = array('sid'=>$sid, 'gameuid'=>$cow['uid']);
	}
}
	echo "----".print_r($people,true)."----".PHP_EOL;

$client = new Redis();
$redis_key = 'referrer_mailbug';
$r = $client->connect("10.173.2.11", 6379, 3);//conn 3 sec timeout.
if ($r === false) {
	file_put_contents("/tmp/admail_reward.log", "connect redis false $gameUid  $referrer " . time() . PHP_EOL, FILE_APPEND);
} else {
	foreach($people as $s ) {
		$arr=array();
		$arr['sid'] = $s['sid'];
		$arr['gameuid'] = $s['gameuid'];
		echo json_encode($arr);
		$client->rPush($redis_key,json_encode($arr));
	}
}
$client->close();
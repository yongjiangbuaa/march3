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

$uidlist = array(
's228 1068089373000209',
's228 1095185289000211',
's228 1360666195000144',
's278 654207288000278',
's329 1611615299000147',
's329 1612871441000147',
's329 19367886000329',
's329 196374117000329',
's329 463693391000351',
's329 794465544000325',
's329 89315965000329',
);

foreach ($uidlist as $li) {
	$ss = explode(' ', $li);
	$server = $ss[0];
	$uid = $ss[1];
	echo "$server $uid\n";
	
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
	print_r($re);
}


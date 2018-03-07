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

global $servers;
$eventAll = array();

$key = 'BADWORDS';

$redis = new Redis();
$redis->connect('184.173.110.102',6379);

$result = array();
$arr = $redis->sMembers($key);

if (isset($_REQUEST['servers'])) {
	$servers = array();
	$tos = explode(',', $_REQUEST['servers']);
	foreach ($tos as $s) {
		$servers[$s] = 1;
	}
}

foreach ($servers as $server=>$info){
	$n = 0;
	if ($server[0] != 's') {
		continue;
	}
	$result[$server][$key] = $page->redis(9, $key,'',$server);
	foreach($arr as $word){
		$page->redis(11, $key,$word,$server);
		$n++;
	}
	echo "$server $key $n".'<br>'.PHP_EOL;
}

print_r($result);
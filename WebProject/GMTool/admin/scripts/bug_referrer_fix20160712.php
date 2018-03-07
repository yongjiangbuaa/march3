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

$eventAll = array();


$servers = range(1,33); //服

$arr_referrer=array(
//	'everyads'=>'everyads%',
//	'Avazu'=>'Avazu%',
//	'Adsup'=>'Adsup%',
//	'Applift'=>'Applift%',
//	'Instal'=>'Instal%',
//	'SharkGamesAndroid'=>'SharkGamesAndroid%',
	'untrusted'=>'Untrusted Devices',
//	'liebao'=>'猎豹',
//	'ApploviniOS'=>'ApploviniOS%',
//	'ApplovinAndroid'=>'ApplovinAndroid%',
//	'uac'=>'Google Universal App Campaigns',
//	'MarsAndroid'=>'MarsAndroid%',
//	'adwords'=>'Google adwords%',
//	'Mobvista'=>'Mobvista%',
	'googlesearch'=>'Google (unknown)',
	'facebook'=>'Facebook Installs',
	'Zenna Android'=>'Zenna Android%',

);
//{"app":979966112074751,"t":1453556735}  这是facebook的

//Mobvista; Facebook Installs; NDB mobi; google adwords; NDB Mobi 2; everyads; Yeahmobi; Glispa;Instagram Installs;approud; NDB Mobi 3; AdColony; NDB Mobi; 猎豹; Google Universal App Campaigns; Everads; Mobisharks; google search

foreach ($servers as $sid) {
	echo "$sid\n";
	$server = 's'.$sid;
	foreach ($arr_referrer as $referrer => $referrer_old) {

		$sql = "update stat_reg set referrer='$referrer' WHERE referrer like '$referrer_old' ;";
		echo '====='.$sql.PHP_EOL;
		$resultsel = $page->executeServer($server,$sql,2,true);

		echo "----".$resultsel['ret']['effect']."----".PHP_EOL;
	}
}


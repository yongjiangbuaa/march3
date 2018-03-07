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


foreach ($servers as $server=>$serInfo){
	//$sql="select uid,name,picVer from userprofile where picVer>2000000 and picVer<3000000;";
	
	$sql="select count(distinct uid) cnt from userprofile where pf='vk';";
	$ret=$page->executeServer($server, $sql, 3);
	if ($ret['ret']['data'] && isset($ret['ret']['data']));
	$cnt=$ret['ret']['data'][0]['cnt'];
	
	file_put_contents('/tmp/vk_wangzhiyuan_20150915.txt', $server.','.$cnt."\n",FILE_APPEND);
	
	/*
	$uidArray=array();
	foreach ($ret['ret']['data'] as $row){
		$uid=$row['uid'];
		$ret = $page->webRequest('kickuser',array('uid'=>$uid),$server);
		$sql="update userprofile set picVer=mod(picVer,1000000) where uid ='$uid' and picVer>2000000 and picVer<3000000;";
		$page->executeServer($server, $sql, 2);
		adminLogUser('yaoduo',$uid,$server,array('picVer'=>$row['picVer']));
	}
	*/
}
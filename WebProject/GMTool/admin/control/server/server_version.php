<?php
!defined('IN_ADMIN') && exit('Access Denied');

$rediskey ='AppStore:Review:Version';

$client = new Redis();
$result = $client->connect(GLOBAL_REDIS_SERVER_IP);
if($result === false){
	exit('连接global_redis错误');
}

if(empty($versionAlready)){
	$versionAlready='空';
}
if ($_REQUEST['type']=='save') {
	$version = $_REQUEST['version'];
	if (!$version){
		exit('版本不能为空!');
	}
	$version = trim($version);

	$client->set($rediskey,$version);

	adminLogSystem($adminuid,"add version".$version.'---'.date('Ymd'));
}else if ($_REQUEST['type']=='del') {
	$temp = $client->get($rediskey);
	$client->delete($rediskey);
	adminLogSystem($adminuid,"delete version".$temp.'---'.date('Ymd'));
}

$versionAlready=$client->get($rediskey);
if(empty($versionAlready)){
	$versionAlready='空';
}
$client->close();

include( renderTemplate("{$module}/{$module}_{$action}") );
?>

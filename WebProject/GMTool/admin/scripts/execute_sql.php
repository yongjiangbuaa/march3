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

$sql1 = "SELECT   u.uid,  u.name,  u.lastOnlineTime,  u.lang,  u.pic,  u.level,  u.exp,  u.serverId,  u.allianceId,  COUNT(m.toUser) AS cnt,  v.score FROM  userprofile u     LEFT JOIN mail m ON      u.uid = m.toUser       AND m.status = 0       AND m.type = 24    LEFT JOIN user_vip v ON      u.uid = v.`uid` WHERE   u.gmFlag = 2 GROUP BY   uid,  name,  lastOnlineTime,  lang,  pic,  exp, serverId, allianceId, level ,score;";
$sql2 = "SELECT uid,lastOnlineTime from userprofile where lang='en' order by lastOnlineTime limit 1;";
foreach ($servers as $server=>$serverInfo){
	if(substr($server, 0 ,1) != 's'){
		continue;
	}
	
	$result = $page->executeServer($server,$sql1,3);
	if (!empty($result['ret']['data'])) {
		echo "$server OK \n";
		continue;
	}
	
	$result = $page->executeServer($server,$sql2,3);
	if (!empty($result['ret']['data'])) {
		$uid = $result['ret']['data'][0]['uid'];
		$sql3 = "update userprofile set gmFlag = 2 where uid='%s';";
		$sql3 = sprintf($sql3, $uid);
// 		echo $sql3,"\n";
// 		$result = $page->executeServer($server,$sql3,2);
		echo "$server $uid \n";
	}else {
		echo "$server N/A \n";
	}
	
}

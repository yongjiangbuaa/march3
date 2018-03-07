<?php
define('IN_ADMIN',true);
define('ADMIN_ROOT', realpath(dirname(__FILE__) . '/../'));
include ADMIN_ROOT.'/config.inc.php';
//include ADMIN_ROOT.'/etc/db.inc_online.php';
include ADMIN_ROOT.'/servers.php';
#include ADMIN_ROOT.'/servers_online.php';
ini_set('mbstring.internal_encoding','UTF-8');
includeModel("BasePage");
global $servers;
set_time_limit(0);


$errorSql = "SELECT uid FROM stat_reg WHERE type = 2";
$page = new BasePage();
foreach ($servers as $curServer=>$serverInfo){
	$sid = substr($curServer, 1);
	if ($sid > 1) {
		break;
	}
	$queryArray = array();
	$sqlData = $page->executeServer($curServer,$errorSql,3,true);
	foreach ($sqlData['ret']['data'] as $curRow) {
		$uid = $curRow['uid'];
		$serverId = getServerId($uid);
		$queryArray["$serverId"][] = $uid;
	}
	$rightSql = 'select `uid`, `time`, `pf`, `pfId`, `referrer`, `country`, `type` FROM stat_reg where type=1 and uid IN ';
	foreach ($queryArray as $serverId => $uids) {
		$server = getServer($serverId);
		foreach($uids as $oneUid){
			$where .= "'$oneUid',";
		}
		$where = substr($where, 0, -1);
		$querySql = $rightSql.'('.$where.')';
		$rightSqlData = $page->executeServer($server, $querySql, 3, true);
		$values = null;	
		foreach ($rightSqlData['ret']['data'] as $rightSqlRow) {
			$uid = $rightSqlRow['uid'];
			$time = $rightSqlRow['time'];
			$pf = $rightSqlRow['pf'];
			$pfId = $rightSqlRow['pfId'];
			$referrer = $rightSqlRow['referrer'];
			$country = $rightSqlRow['country'];
			$type = $rightSqlRow['type'];
			$values .= "('$uid', $time, '$pf', '$pfId', '$referrer', '$country', 2),";
		}
		if ($values) {
			$values = substr($values, 0, -1);
			$replaceSql = 'REPLACE INTO `stat_reg` (`uid`, `time`, `pf`, `pfId`, `referrer`, `country`, `type`) VALUES '.$values;
			file_put_contents('/tmp/repairreg.log', $replaceSql."\n", FILE_APPEND);
			$retddd = $page -> executeServer($curServer, $replaceSql, 1, true);
		}
	}
	echo date('Y-m-d H:i:s'). " done. $curServer \n";
}

function getServer($serverId){
	return 's'.$serverId;
// 	return 'localhost';
}

function getServerId($uid) {
	return $uid % 200;
}
?>

<?php
!defined('IN_ADMIN') && exit('Access Denied');

if($_REQUEST['type'] == 'modify'){
	$serverTemp = $_REQUEST['server'];
	$temp = explode('_', $serverTemp, 2);
	$modifyServer = $temp[0];
	$modifyActName = $temp[1];
	$newStat=$_REQUEST['newStat'];
	//$modifySql = "insert into server_config(uid,stat) values ('$modifyActName',$newStat) ON DUPLICATE KEY update stat = $newStat";
	$modifySql = "update server_config set stat = $newStat where uid='$modifyActName';";
	if($modifySql){
		$result = $page->executeServer($modifyServer,$modifySql,2, true);
	}
}


global $servers;
$title = "功能配置开关";
$allServerAct =array();

//attackProtection 攻击保护
$sql = "select uid,stat from server_config";
foreach ($servers as $server=>$serverInfo){
	if(substr($server, 0 ,1) != 's' && strpos($server, 'test')===false && strpos($server, 'localhost')===false){
		continue;
	}
	$result = $page->executeServer($server,$sql,3,true);
	foreach ($result['ret']['data'] as $curRow){
		$allServerAct[$server][$curRow['uid']] = $curRow[stat];
	}
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
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

$page->globalExecute('delete from mod_info;', 2, true);

$sql1 = "SELECT uid, lang FROM userprofile WHERE gmFlag=2;";
$modlist = array();
foreach ($servers as $server=>$serverInfo){
	if(substr($server, 0 ,1) != 's'){
		continue;
	}
	
	$result = $page->executeServer($server,$sql1,3);
	if (!empty($result['ret']['data'])) {
		foreach ($result['ret']['data'] as $record){
// 			$modlist[] = "('". $record['uid']. "','". (empty($record['lang'])?'en':$record['lang']) . "')";
			$modlist[] = "('". $record['uid']. "','". $record['lang'] . "')";
		}
	}
}
print_r($modlist);
if (!empty($modlist)) {
	$values = implode(',', $modlist);
	$insert = "insert into mod_info values $values";
	echo $insert,"\n";
	$page->globalExecute($insert, 2, true);
}

echo "done\n";

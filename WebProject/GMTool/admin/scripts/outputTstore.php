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

$sql = "select distinct(uid) user from paylog where time > 1432263600000 and time < 1433430000000 and pf ='tstore'";
global $servers;
foreach ($servers as $server=>$serverInfo){
	echo $server."\n";
	if(substr($server, 0 ,1) != 's'){
		continue;
	}
//continue;
	$result = $page->executeServer($server, $sql, 3, true);
	if(!$result['ret']||!isset($result['ret']['data']))
	{
		//writeLog("-- $server error\n");
		continue;
	}
	//writeLog("-- $server\n");
	foreach($result['ret']['data'] as $sqlData){
		writeLog($server.'	'.$sqlData['user']);	
	}
	//break;
}
function writeLog($row){
	file_put_contents( ADMIN_ROOT .'/COK-Tstore20150615.txt', $row . "\n",FILE_APPEND);
}
?>

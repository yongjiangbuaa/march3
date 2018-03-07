<?php
//统计韩国每日新增注册人数
define('IN_ADMIN',true);
define('APP_ROOT', realpath(dirname(__DIR__) . '/../gameengine/test'));
define('ADMIN_ROOT', realpath(dirname(__DIR__) . '/'));
ini_set('mbstring.internal_encoding','UTF-8');
include ADMIN_ROOT.'/config.inc.php';
include ADMIN_ROOT.'/admins.php';
include_once ADMIN_ROOT.'/servers.php';
$page = new BasePage();
date_default_timezone_set('UTC');

$startTime = strtotime('2015-06-30')*1000;
// $endTime = strtotime('2015-08-2')*1000;
$retData = array();
$sql = "select date_format(from_unixtime(`time`/1000),'%Y-%m-%d') as date,count(1) sum from stat_reg where time > $startTime and country = 'KR' and type = 0 group by date";
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
// 		writeLog("$server	error	0");
		continue;
	}
	//writeLog("-- $server\n");
	foreach($result['ret']['data'] as $sqlData){
		$retData[$sqlData['date']] += $sqlData['sum'];
// 		writeLog($server.'	'.$sqlData['date'].'	'.$sqlData['sum']);	
	}
	//break;
}
foreach ($retData as $date => $sum){
	echo $date.'	'.$sum."\n";
}
function writeLog($row){
	file_put_contents( ADMIN_ROOT .'/outputkr'.date('Ymd_H:i').'.txt', $row . "\n",FILE_APPEND);
}
?>

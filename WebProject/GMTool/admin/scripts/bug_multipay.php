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

$fixList = getFixList();
global $servers;
foreach ($fixList as $uid=>$descGold){
	$globalSql = "select server from account_new where gameuid='$uid'";
	$result = cobar_getAccountInfoByGameuids($uid);
	if(!$result)
	{
		writeLog("$uid	global	error");
		continue;
	}
	$server = 's'.$result[0]['server'];
	$sql = "select paidGold,banTime from userprofile where uid ='$uid'";
	$result = $page->executeServer($server, $sql, 3, true);
	if($result['ret']&&isset($result['ret']['data']))
	{
		$currGold = $result['ret']['data'][0]['banTime'];
		$updateSql = "update userprofile set banTime = 1462997400000 where uid ='$uid' ";
		$page->executeServer($server, $updateSql, 1, true);
		writeLog("$uid	gold	$currGold	$descGold");
	}else{
		writeLog("$uid	$server	error");
	}
}
function writeLog($row){
	echo $row."\n";
}
function getFixList(){
	return array(
		'1051941559000233'=>	3286.71
,'799052676000308'=>	3146.85
,'27109489000298'=>	3096.90
,'1029688357000233'=>	1958.04
,'1347457768000001'=>	1098.90
,'984326603000015'=>	769.23
,'227018942000308'=>	749.25
//,'343057099000126'=>	724.77
,'979488656000015'=>	709.29
,'1043702900000233'=>	629.37
	);
}
?>

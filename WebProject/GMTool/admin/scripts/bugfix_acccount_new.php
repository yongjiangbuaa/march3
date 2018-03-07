<?php
define('IN_ADMIN',true);
define('ADMIN_ROOT', realpath(dirname(__FILE__) . '/../'));
include ADMIN_ROOT.'/config.inc.php';
include ADMIN_ROOT.'/servers.php';
include_once ADMIN_ROOT.'/admins.php';

ini_set('mbstring.internal_encoding','UTF-8');
includeModel("BasePage");
set_time_limit(0);

ini_set('memory_limit','2048M');
$pidfile = '/tmp/bugfix_acccount_new_pid';

if(file_exists($pidfile)){
	echo 'allready running';
	exit();
}
$arruids = array();
$filename = '/tmp/MailSendUidContents.php';
if(file_exists($filename)) {
	$arruids = require($filename);
	if(!$arruids['uids']){
		unlink($filename);
		return;
	}
}else{
	return;
}

file_put_contents($pidfile,time());

$uidStr=str_replace('，', ',', $arruids['uids']);
$uids=explode(',', $uidStr);

$arruids2 = array_chunk($uids,200);
//print_r($arruids2);
$uidServerArray = array();
foreach($arruids2 as $key=>$value){
	$value1 = array_values($value);

	$result['ret']['data'] = cobar_getAccountInfoByGameuids($value1);
	foreach ($result['ret']['data'] as $curRow){
		$uidServerArray[$curRow['gameUid']]=$curRow['server'];
	}
}
//$uidServerArray = array('10732401041000068'=>'68');
$page = new BasePage();
// print_r($uidServerArray);

$title = $arruids['title'];
$contents = $arruids['contents'];

$sendTime = intval(microtime(true)*1000);
$reward = $arruids['reward'];
$adminid = $arruids['adminid'];

foreach ($uidServerArray as $uidValue=>$serverKey){
	$toUser = $uidValue;
	if(count($uidValue) == 0){
		continue;
	}
	$uid = md5($toUser.$serverKey.$uidValue.floor(microtime(true)*1000));
	//100001029|100001386|200200    50|100|5
	$rewardStatus = 0;

	echo $uidValue.'--'.$serverKey.PHP_EOL;

	$sql = "INSERT INTO `server_usermail` (`uid`, `toUser`, `sendBy`, `sendTime`, `title`, `contents`, `reward`,`rewardStatus`) VALUES ('$uid', '$toUser', $adminid, '$sendTime', '$title', '$contents', '$reward',$rewardStatus)";
	$page->executeServer('s'.$serverKey, $sql, 2);

	//srctype
//	$sql = "insert into mail (uid, toUser, fromUser, fromName, status, type, rewardStatus, saveFlag, title, contents, rewardId, createTime, itemIdFlag, reply) values ('$uid', '$toUser', '', 'system', 0, 13, $rewardStatus, 0, '$title', '$contents', '$reward', $sendTime, 0, 0)";
	$page->executeServer('s'.$serverKey, $sql, 2);

	$page->webRequest('sendmail',array('uid'=>$uid),'s'.$serverKey);

	adminLogUser ( $adminid, $uidValue, 's'.$serverKey, array (
			'groupMail'=>'add',
			'reward' => $reward,
			'sendTime' => $sendTime
		)
	);
}

if(file_exists($filename)){
	unlink($filename);
}
if(file_exists($pidfile)){
	unlink($pidfile);
}
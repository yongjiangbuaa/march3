<?php
define('IN_ADMIN',true);
define('ADMIN_ROOT', realpath(dirname(__FILE__) . '/../../'));
include ADMIN_ROOT.'/config.inc.php';
include ADMIN_ROOT.'/servers.php';
include ADMIN_ROOT.'/admins.php';
ini_set('mbstring.internal_encoding','UTF-8');
includeModel("BasePage");
global $servers;
set_time_limit(0);

$page = new BasePage();
$task = $argv[1]?$argv[1]:0;
if ($task == 1) {
	echo "task=1\n";
	$sendTime = floor(microtime(true)*1000);
	$filePath = './tstorePaymentReward.log';
	$fileData = file($filePath);
	$orderList = array();
	$reward = 'goods,209302,1|goods,209332,1|goods,200600,10|goods,200410,1|goods,200221,1|goods,200220,2';
	foreach ( $fileData as $line ) {
		$toUser = trim($line);
		$gameAccount = null;
		$accountSqlData = cobar_getAccountInfoByGameuids($toUser);
		$gameAccount = $accountSqlData[0]; 
		if($gameAccount){
			$toUser = $gameAccount['gameUid'];
			$title = 'TSTORE 첫 충전 이벤트 보너스 발송';
			$contents = '안녕하세요! Clash Of Kings  TSTORE버전 첫 충전 이벤트에 참여해주셔서 감사드립니다. 아래 보너스를 받으시고 게임내에서 유용하게 사용하시길 바랍니다. 즐거운 Clash Of Kings 되세요!';
			$uid = getGUID();
			$newMail = "insert into mail (uid, toUser, fromUser, fromName, status, type, rewardStatus, saveFlag, title, contents, rewardId, createTime, itemIdFlag, reply) values ('$uid', '$toUser', '', 'system', 0, 13, 0, 0, '$title', '$contents', '$reward', $sendTime, 0, 1)";
			$page->executeServer(getServer($gameAccount['server']),$newMail,1,true);
			$page->webRequest('sendmail',array('uid'=>$uid),getServer($gameAccount['server']));
			echo getServer($gameAccount['server']).'	'.$toUser.'	'.$uid."\n";
		}else{
			echo $toUser . " not found\n";
		}
	}
}
function getServer($serverId){
	return 's'.$serverId;
// 	return 'localhost';
}
function getGUID() {
	$ip = "127001";
	$unknown = 'unknown';
	if ( isset($_SERVER['HTTP_X_FORWARDED_FOR'])
	&& $_SERVER['HTTP_X_FORWARDED_FOR']
	&& strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'],
			$unknown) ) {
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	}
	elseif ( isset($_SERVER['REMOTE_ADDR'])
			&& $_SERVER['REMOTE_ADDR'] &&
			strcasecmp($_SERVER['REMOTE_ADDR'], $unknown) ) {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	$ip = str_replace(".","", $ip);
	$ip = str_replace(",","", $ip);
	$ip = trim($ip);
	return uniqid($ip.'COK');
}
?>

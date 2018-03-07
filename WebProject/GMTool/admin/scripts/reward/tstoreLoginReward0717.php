<?php
define('IN_ADMIN',true);
define('ADMIN_ROOT', realpath(dirname(__FILE__) . '/../../'));
include ADMIN_ROOT.'/config.inc.php';
include ADMIN_ROOT.'/servers.php';
ini_set('mbstring.internal_encoding','UTF-8');
includeModel("BasePage");
global $servers;
set_time_limit(0);

//插入待发奖励
$page = new BasePage();
$task = $argv[1]?$argv[1]:0;

if ($task == 1){
	echo "\n";
	$sendTime = floor(microtime(true)*1000);
	$reward = 'goods,200011,1|goods,200220,1|goods,200416,1|goods,200410,1|goods,200414,1|goods,200021,1';
	$sql = "select distinct(uid) from stat_login_2015_6 where time > 1437058800000 and time < 1437145200000 and pf = 'tstore'";
	foreach ($servers as $server=>$serverInfo){
		echo $server."\n";
		$sqlData = $page->executeServer($server,$sql,3,false);
		foreach ($sqlData['ret']['data'] as $curRow){
			$toUser = $curRow['uid'];
			$title = '7월 17일 T스토어 버전 로그인 이벤트 ';
			$contents = '한국 영주님들 안녕하세요! 7월 17일 T스토어 버전 로그인 이벤트 참여 감사드립니다! 로그인 보너스 받으시고 클래시 오브 킹즈와 함께 즐거운 시간 되시길 바라겠습니다! ';
			$uid = getGUID();
			$newMail = "insert into mail (uid, toUser, fromUser, fromName, status, type, rewardStatus, saveFlag, title, contents, rewardId, createTime, itemIdFlag, reply) values ('$uid', '$toUser', '', 'system', 0, 13, 0, 0, '$title', '$contents', '$reward', $sendTime, 0, 1)";
			$page->executeServer($server,$newMail,1,true);
			$page->webRequest('sendmail',array('uid'=>$uid),$server);
			echo $server.'	'.$toUser.'	'.$uid."\n";
			file_put_contents( "./tstoreLoginReware0717.log", $server.'	'.$toUser.'	'.$uid . "\n", FILE_APPEND);
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

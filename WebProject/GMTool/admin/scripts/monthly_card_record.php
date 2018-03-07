<?php
define('IN_ADMIN',true);
define('APP_ROOT', realpath(dirname(__DIR__) . '/../gameengine/test'));
define('ADMIN_ROOT', realpath(dirname(__DIR__) . '/'));
ini_set('mbstring.internal_encoding','UTF-8');
include ADMIN_ROOT.'/config.inc.php';
include ADMIN_ROOT.'/admins.php';
include_once ADMIN_ROOT.'/servers.php';
include ADMIN_ROOT . '/language/monthly_cardLang.php';
$page = new BasePage();
date_default_timezone_set('UTC');
set_time_limit(0);

global $servers;
$deviceIdArray=array();


$file="/tmp/liuwen_monthlyCard_20150831.log";
//file_put_contents($file, "操作日期,操作人,服,uid\n",FILE_APPEND);
//file_put_contents($file, "服,uid,领主等级,大本等级,充值金币,注册时间\n",FILE_APPEND);
foreach ($servers as $server=>$serInfo){
	
	if($server=='s532'){
		continue;
	}
	if (substr($server, 1)>641){
		continue;
	}
	
	$sql="select uid,accept from monthly_card where buyTime>=1438387200000;";
	$ret=$page->executeServer($server, $sql, 3);
	if ($ret['ret']['data']&&isset($ret['ret']['data'])){
		foreach ($ret['ret']['data'] as $row){
			$uid=$row['uid'];
			$accept=$row['accept'];
			file_put_contents($file, $uid.','.$accept."\n",FILE_APPEND);
			
			$sql="select level,lang from userprofile where uid='$uid';";
			$result=$page->executeServer($server, $sql, 3);
			$lang='';
			if(!$result['error'] && $result['ret']['data']){
				$payLevel=$result['ret']['data'][0]['level'];
				$lang=$result['ret']['data'][0]['lang'];
			}
			if (empty($lang) || (!isset($contentsArray[$lang]))){
				$lang='en';
			}
			
			$goldNum=($accept+1)*650;
			$reward="gold,0,$goldNum|goods,200200,5|goods,200201,15|goods,200300,10|goods,200330,10";
			$toUser = $uid;
			$sendTime = microtime(true)*1000;
			$title = addslashes($titleArray[$lang]['0']);//
			$contents = addslashes($contentsArray[$lang]['0']);//
			$uuid = md5($toUser.$sendTime.$title.$contents.$reward.time());
			$rewardStatus = 0;
			$sql = "insert into mail (uid, toUser, fromUser, fromName, status, type, rewardStatus, saveFlag, title, contents, rewardId, createTime, itemIdFlag, reply) values ('$uuid', '$toUser', '', 'system', 0, 13, $rewardStatus, 0, '$title', '$contents', '$reward', $sendTime, 0, 0)";
			$page->executeServer($server, $sql, 2);
			sendReward2($uuid,$server);
		}
	}
}

function sendReward2($mailUid,$serv){
	$page = new BasePage();
	$page->webRequest('sendmail',array('uid'=>$mailUid),$serv);
}


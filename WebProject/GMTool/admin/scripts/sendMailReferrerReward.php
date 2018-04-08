<?php
define('IN_ADMIN',true);
define('ADMIN_ROOT', realpath(dirname(__FILE__) . '/../'));
include ADMIN_ROOT.'/config.inc.php';
include ADMIN_ROOT.'/servers.php';
ini_set('mbstring.internal_encoding','UTF-8');
includeModel("BasePage");
set_time_limit(0);

$pidFile = '/tmp/sendMailReferrerReward'.'.pid';
if(file_exists($pidFile)){
	trackLog("================  pid file exits sendMailReferrerReward.pid ===============");
	return ;
}
file_put_contents($pidFile,'sendMailReferrerReward is running');

$redis_key = 'referrer_mail';
$redis_key_tmp = 'referrer_mail_tmp';

$mailItem = require ADMIN_ROOT . '/scripts/sendMail_referrer_config.php';

$client = new Redis();
$ret = $client->connect('10.173.2.11',6379);
if($ret === false){
	trackLog('connect redis error'.date('Y-m-d H:i:s'));
	return;
}

$page = new BasePage();

$ret = $client->rpoplpush($redis_key,$redis_key_tmp);//尾部pop,插入头部
if(!$ret){ //为空就不走下边了,tmpkey留着
	$client->close();
	unlink($pidFile);
	return;
}

while($ret){
	$ret = json_decode($ret,true);

	$toUser = $ret['gameuid'];
	if($toUser){
		$server = 's'.$ret['sid'];
		$sender = $mailItem['sender'];
		$type = $mailItem['type'];
		$reward = $mailItem['reward'];
		$sendBy = '';
		$sendTime = floor(microtime(true)*1000);
		$title = $mailItem['title'];//
		$contents = $mailItem['message'];//
		$uid = md5($toUser.$sendBy.$sendTime.$title.$contents.$reward.time());
		$rewardStatus = 1;
		if($reward)
			$rewardStatus = 0;

		$sql = "insert into mail (uid, toUser, fromUser, fromName, status, type, rewardStatus, saveFlag, title, contents, rewardId, createTime, itemIdFlag, reply) values ('$uid', '$toUser', '', '$sender', 0, $type, $rewardStatus, 0, '$title', '$contents', '$reward', $sendTime, 1, 1)";
		echo $sql.PHP_EOL;
		$insert_return = $page->executeServer($server,$sql,2);
		if($insert_return['error']){
			trackLog($sql);
		}

		$page->webRequest('sendmail',array('uid'=>$uid),$server);
	}else{
		echo 'touser read fail';
	}

	$ret = $client->rpoplpush($redis_key,$redis_key_tmp);//尾部pop,插入头部
}

$alluid = $client->lRange($redis_key_tmp,0,-1);
trackLog('del record'.json_encode($alluid));
$client->del($redis_key_tmp);
$client->close();

if(file_exists($pidFile)){
	unlink($pidFile);
	trackLog("================ unlink pid file sendMailReferrerReward.pid ===============");
}

function trackLog( $message){
	$file = '/tmp/referrerReward_'.date('Ymd').'.log';
	file_put_contents($file, "$message"."\n", FILE_APPEND);
}

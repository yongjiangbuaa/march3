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
set_time_limit(0);
ini_set('memory_limit', '512M');

global $servers;
$eventAll = array();

//档位=>礼包内容
$package_def = array(
		'9.99'=>"gold,0,400",
		'19.99'=>"gold,0,600",
		'49.99'=>"gold,0,1000",
		'99.99'=>"gold,0,4000",
);
//档位=>补偿奖励

$file = '/usr/local/cok/SFS2X/fbpay_uid.dat';
$runlog = '/usr/local/cok/SFS2X/bugfix_mail_fbpay-v2.log';

$handle = @fopen($file, "r");
$pr = 0;
if ($handle) {
	while (($buffer = fgets($handle)) !== false) {
		$buffer = trim($buffer);
		if (empty($buffer)) continue;
		$d = explode(',', $buffer);
		$uid = $d[0];
		$productid = $d[1];
		$sid = $d[2];
		
		echo date('Y-m-d H:i:s')." $buffer start...\n";
		
		file_put_contents($runlog, 'bugfix_mail_sendmail_runlog '.$buffer."\n", FILE_APPEND);
		$notopen = 0;
		$server = "s$sid";
		
		if (!isset($package_def[$productid])) {
			file_put_contents($runlog, 'ERROR '.$uid." $sid $productid PACAKGENOTEXISTS.\n", FILE_APPEND);
			continue;
		}
		
		//礼包内
		$mail_title = 'Recharge Gift';
		$mail_content = 'My Lord,\r\nWe have already received your recharge. We will send the golds which you haven’t received during your gift purchasing due to our server breakdown to you via mails. We apologize for any inconvenience caused by this. \r\nIn order to express thanks to your tolerance, we prepare a regret gift for you. \r\nYour servant';
		$reward = $package_def[$productid];
		$gameuid = $uid;
		$mail_uid = md5(uniqid(mt_rand(),1).microtime(true).$gameuid);
		$fromName = 'system';
		$mailType = 13;
		$rewardStatus = 0;
		$createTime = time()*1000;
		$sql = "insert into mail(uid, toUser, fromName, title, contents, rewardId, type, rewardStatus, createTime) values ('$mail_uid', '$gameuid', '$fromName', '$mail_title', '$mail_content', '$reward', $mailType, $rewardStatus, $createTime)";
		$result = $page->executeServer($server,$sql,2);
		file_put_contents($runlog, 'InsertMail1 '.$uid." $sid $sql ".json_encode($result)."\n", FILE_APPEND);
		
		$pr++;
	}
}
echo date('Y-m-d H:i:s')." complete. $pr\n";


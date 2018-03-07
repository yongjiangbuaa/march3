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
		'4.99'=>"goods,200300,20|goods,200309,5|goods,200330,20|goods,200339,5|goods,200004,5|goods,200031,5|goods,200200,1|goods,200208,5|goods,200207,30|goods,200201,30|goods,200390,10",
		'9.99'=>"goods,200300,40|goods,200309,15|goods,200330,40|goods,200339,15|goods,200004,5|goods,200031,5|goods,200221,2|goods,200220,2|goods,200203,20|goods,200208,8|goods,200207,40|goods,200201,40|goods,200390,15",
		'19.99'=>"goods,200321,5|goods,200320,50|goods,200329,20|goods,200300,40|goods,200309,10|goods,200301,5|goods,200330,50|goods,200339,20|goods,200331,5|goods,200001,1|goods,200221,2|goods,200220,3|goods,200004,5|goods,200031,5|goods,200203,30|goods,200208,10|goods,200207,50|goods,200201,50|goods,200391,1|goods,200390,15",
		'49.99'=>"goods,200300,50|goods,200309,20|goods,200301,10|goods,200330,60|goods,200339,25|goods,200331,15|goods,200321,20|goods,200320,60|goods,200329,30|goods,200310,60|goods,200311,20|goods,200319,30|goods,200002,1|goods,200417,1|goods,200415,1|goods,200221,3|goods,200220,3|goods,200004,5|goods,200031,5|goods,200203,40|goods,200208,15|goods,200207,60|goods,200201,60|goods,200391,2|goods,200390,25",
		'99.99'=>"goods,200303,5|goods,200309,25|goods,200301,10|goods,200300,60|goods,200333,10|goods,200339,30|goods,200331,20|goods,200330,60|goods,200313,10|goods,200310,60|goods,200311,20|goods,200319,30|goods,200323,10|goods,200321,20|goods,200320,60|goods,200329,30|goods,200420,1|goods,200002,1|goods,200450,1|goods,200221,5|goods,200220,5|goods,200004,5|goods,200031,5|goods,200203,60|goods,200208,25|goods,200207,70|goods,200201,70|goods,200392,1|goods,200391,5|goods,200390,25",
);
//档位=>补偿奖励
$buchang_def = array(
		'4.99'=>"gold,0,300|goods,200300,1|goods,200330,1|goods,200201,1",
		'9.99'=>"gold,0,300|goods,200300,1|goods,200330,1|goods,200201,1",
		'19.99'=>"gold,0,300|goods,200300,1|goods,200330,1|goods,200201,1",
		'49.99'=>"gold,0,300|goods,200300,1|goods,200330,1|goods,200201,1",
		'99.99'=>"gold,0,300|goods,200300,1|goods,200330,1|goods,200201,1",
);

$file = '/usr/local/cok/SFS2X/fbpay_uid.dat';
$runlog = '/usr/local/cok/SFS2X/bugfix_mail_fbpay.log';

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
		$mail_content = 'My Lord,\r\nWe have already received your recharge. We will send the items which you haven’t received during your gift purchasing due to our server breakdown to you via mails. We apologize for any inconvenience caused by this. \r\nIn order to express thanks to your tolerance, we prepare a regret gift for you. \r\nYour servant';
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

		//补偿
		$mail_title = 'Gratitude Gift';
		$mail_content = 'My Lord,\r\nThank you for your patient waiting. Please receive these gifts. We wish you have a happy time.\r\nYour servant';
		$reward = $buchang_def[$productid];
		$gameuid = $uid;
		$mail_uid = md5(uniqid(mt_rand(),1).microtime(true).$gameuid);
		$fromName = 'system';
		$mailType = 13;
		$rewardStatus = 0;
		$createTime = time()*1000;
		$sql = "insert into mail(uid, toUser, fromName, title, contents, rewardId, type, rewardStatus, createTime) values ('$mail_uid', '$gameuid', '$fromName', '$mail_title', '$mail_content', '$reward', $mailType, $rewardStatus, $createTime)";
		$result = $page->executeServer($server,$sql,2);
		file_put_contents($runlog, 'InsertMail2 '.$uid." $sid $sql ".json_encode($result)."\n", FILE_APPEND);
		
		$pr++;
	}
}
echo date('Y-m-d H:i:s')." complete. $pr\n";


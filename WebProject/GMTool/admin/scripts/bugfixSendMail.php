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

$file = '/usr/local/cok/SFS2X/bugfix_mail_delitem_uid.dat';
// $file = '/usr/local/cok/SFS2X/bugfix_mail_ressolder_uid.dat';// from bugfix_mail_res_runlog.logV2 439+ uids.
$runlog = '/usr/local/cok/SFS2X/bugfix_mail_sendmail_runlog.log';
$handle = @fopen($file, "r");
$pr = 0;
if ($handle) {
	while (($buffer = fgets($handle)) !== false) {
		$buffer = trim($buffer);
		if (empty($buffer)) continue;
		$d = explode(' ', $buffer);
		$uid = $d[0];
		$sid = $d[1];
		
		echo date('Y-m-d H:i:s')." $buffer start...\n";
		
		file_put_contents($runlog, 'bugfix_mail_sendmail_runlog '.$buffer."\n", FILE_APPEND);
		$notopen = 0;
		$server = "s$sid";
		
		//mail
		$mail_title = 'Resolution on Game Bug';
		$mail_content = 'My lord, We received feedback on game bug from enthusiastic player. The "loss compensation" can be received for many times rather than one time. In order to keep the game balance,we decide to deduct the duplicate items you received. Hope you can understand. Have fun in the game.  Clash of Kings game studio';
		$reward = '';
		$gameuid = $uid;
		$mail_uid = md5(uniqid(mt_rand(),1).microtime(true).$gameuid);
		$fromName = 'system';
		$mailType = 15;
		$rewardStatus = 1;//No奖
		$createTime = time()*1000;
		$sql = "insert into mail(uid, toUser, fromName, title, contents, type, rewardStatus, createTime) values ('$mail_uid', '$gameuid', '$fromName', '$mail_title', '$mail_content', $mailType, $rewardStatus, $createTime)";
		
		$result = $page->executeServer($server,$sql,2);
		file_put_contents($runlog, 'InsertMail '.$uid." $sid $sql\n", FILE_APPEND);
		$pr++;
	}
}
echo date('Y-m-d H:i:s')." complete. $pr\n";


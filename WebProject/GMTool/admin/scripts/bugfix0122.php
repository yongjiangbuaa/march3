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

$file = '/usr/local/cok/SFS2X/bugfix_mail_20150122.dat';
$runlog = '/usr/local/cok/SFS2X/bugfix_mail_runlog.log';
$handle = @fopen($file, "r");
$pr = 0;
if ($handle) {
	while (($buffer = fgets($handle)) !== false) {
		$buffer = trim($buffer);
		if (empty($buffer)) continue;
		$d = explode(',', $buffer);
		$uid = $d[0];
		$sid = $d[1];
		$err_cnt = $d[3];
		$err_cnt -= 1;//=发生错误的-1
		if ($err_cnt <= 0) {
			continue;
		}
		
		echo date('Y-m-d H:i:s')." $buffer start...\n";
		
		file_put_contents($runlog, 'bugfix_mail_20150122 '.$buffer."\n", FILE_APPEND);
		$notopen = 0;
		$server = "s$sid";
		
		// 删除22号20点后相关mail:
		//   title: 105714 createTime>1421928000000
		$sql = "select uid,toUser,title,rewardStatus,createTime from mail where toUser='$uid' and type=2 and createTime>1421928000000";
		$checkResult = $page->executeServer($server,$sql,3);
		foreach ($checkResult['ret']['data'] as $row) {
			if ($row['title'] != '105714') {
				continue;
			}
			file_put_contents($runlog, 'ORI_RECORD_MAIL '.implode('<>', $row)."\n", FILE_APPEND);
			if ($row['rewardStatus'] == 0) {
				// delete mail
				$mail_uid = $row['uid'];
				$sql_delmail = "delete from mail where uid='$mail_uid'";
				file_put_contents($runlog, 'DEL_SQL_MAIL '.$sql_delmail."\n", FILE_APPEND);
				$result = $page->executeServer($server,$sql_delmail,2);
				
				$notopen += 1;
			}
		}
		
		$got_mail_reward_cnt = $err_cnt - $notopen;
		file_put_contents($runlog, 'DATA_NUM_MAIL '."$got_mail_reward_cnt $err_cnt $notopen"."\n", FILE_APPEND);
		
		if ($got_mail_reward_cnt <= 0) {
			continue;
		}
		// user_item:
		//   itemId: 200002*1   200800*4
		// 玩家已经领的 ＝ 总共多发的（=发生错误的-1） - 没有开mail的
		$sql = "select ownerId,itemId,`count` from user_item where ownerId='$uid' and itemId in ('200002','200800')";
		$checkResult = $page->executeServer($server,$sql,3);
		foreach ($checkResult['ret']['data'] as $row) {
			file_put_contents($runlog, 'ORI_RECORD_ITEM '.implode('<>', $row)."\n", FILE_APPEND);
			$own = $row['count'];
			$itemid = $row['itemId'];
			if ($itemid == '200002') {
				$sub = 1 * $got_mail_reward_cnt;
				$unifyCount = $own * 4;
			}elseif ($itemid == '200800') {
				$sub = 4 * $got_mail_reward_cnt;
				$unifyCount = $own;
			}else {
				continue;
			}
			$do = $own - $sub;
			file_put_contents($runlog, 'DATA_NUM_ITEM '."$do $own $sub"."\n", FILE_APPEND);
			if ($do < 0) {
				$do = 0;
			}
			$upsql = "update user_item set `count`=$do where ownerId='$uid' and itemId='$itemid'";
			file_put_contents($runlog, 'UP_SQL_ITEM '.$upsql."\n", FILE_APPEND);
			$result = $page->executeServer($server,$upsql,2);
			
			file_put_contents($runlog, "UnifyCount $uid $sid $itemid $unifyCount"."\n", FILE_APPEND);
		}
		$pr++;
	}
}
echo date('Y-m-d H:i:s')." complete. $pr\n";


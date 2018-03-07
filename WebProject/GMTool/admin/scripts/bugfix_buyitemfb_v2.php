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

$runlog = '/usr/local/cok/SFS2X/bugfix_buyitemfb_v2.log';
// run_bugfix_buyitemfb.php.V2.log
// /usr/local/cok/SFS2X/bugfix_buyitemfb_v2.log

$files = array(
		'/usr/local/cok/SFS2X/buyitembug_v2.log152',
		'/usr/local/cok/SFS2X/buyitembug_v2.log188',
		'/usr/local/cok/SFS2X/buyitembug_v2.log191',
		'/usr/local/cok/SFS2X/buyitembug_v2.log194',
		'/usr/local/cok/SFS2X/buyitembug_v2.log197',
		'/usr/local/cok/SFS2X/buyitembug_v2.log199',
		'/usr/local/cok/SFS2X/buyitembug_v2.log236',
		'/usr/local/cok/SFS2X/buyitembug_v2.log263',
);

foreach ($files as $fname) {
		$i = 0;
		echo "$fname\n";
		$sid = substr($fname, -3);
		$server = 's'.$sid;
		$result = array();
		$result2 = array();
		$player = array();
		$f = file($fname);
		foreach ($f as $line) {
			$data = trim($line);
			$tok = explode('|', $data);
			if (count($tok) != 12) {
				continue;
			}
			$i++;
			$uid = trim($tok[6]);
			$pa = $tok[9];
			$j = trim($pa);
			$a = json_decode($j,true);
			
			if ($a['num'] <= 1) {
				continue;
			}
			
			$timestr = substr($tok[0], 0, 19);
			$acttime = strtotime($timestr);
			if ($acttime > 1429446600) {
				continue;
			}
			
			$result[$a['itemId']] += ($a['num']-1);
			$player[$uid][$a['itemId']] += ($a['num']-1);
		}
	
		foreach ($player as $gameuid=>$items) {
			foreach ($items as $itemid => $subcount) {
				$sql_curr_sel = "select count from user_item where ownerId='$gameuid' and itemId='$itemid'";
				$resultsel = $page->executeServer($server,$sql_curr_sel,3,true);
				if ($resultsel['ret']['data']) {
					$owncnt_ori = $resultsel['ret']['data'][0]['count'];
					$newcnt = $owncnt_ori - $subcount;
					if ($newcnt < 0) {
						$newcnt = 0;
					}
					$result2[$itemid] += $newcnt;
					$sql_curr_set = "update user_item set count=$newcnt where ownerId='$gameuid' and itemId='$itemid'";
 					$re = $page->executeServer($server,$sql_curr_set,2);
					file_put_contents($runlog, "OK,$server,$gameuid,$itemid,$subcount,$owncnt_ori,$newcnt,".json_encode($re)."\n", FILE_APPEND);
				}else{
					file_put_contents($runlog, "NG,$server,$gameuid,$itemid,$subcount,NORECORDS\n", FILE_APPEND);
				}
			}
		}
		
		foreach ($result as $k=>$v) {
			echo "$k $v ".intval($result2[$k])."\n";
		}
}


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

$runlog = '/usr/local/cok/SFS2X/bugdata/bugfix_buyitemfb_v4.log';//v3返还遗漏

//OK,s152,126181627000152,200016,63,3,0,{"ret":{"result":true,"effect":1}}
//NG,s152,327294256000152,200300,594,NORECORDS
//已经被扣掉的 200016 黄金剑 200020 战争号角
$prelogs = array(
		'/usr/local/cok/SFS2X/bugdata/bugfix_buyitemfb.log',
		'/usr/local/cok/SFS2X/bugdata/bugfix_buyitemfb_v2.log',
);
$player_been_subed = array();
foreach ($prelogs as $fname) {
	echo "$fname\n";
	$f = file($fname);
	foreach ($f as $line) {
		$data = trim($line);
		$tok = explode(',', $data);
		if ('NG' == $tok[0]) {
			continue;
		}
		if (!in_array($tok[3], array('200016','200020'))) {
			continue;
		}
		$been_subed = $tok[5] - $tok[6];
		if ($been_subed <= 0) {
			continue;
		}
		
		$player_been_subed[$tok[1]][$tok[2]][$tok[3]] += $been_subed;
	}
}

// print_r($player_been_subed);

$files = array(
		'/usr/local/cok/SFS2X/buyitembug_v3.log152',//merge => v1 v2 
		'/usr/local/cok/SFS2X/buyitembug_v3.log188',
		'/usr/local/cok/SFS2X/buyitembug_v3.log191',
		'/usr/local/cok/SFS2X/buyitembug_v3.log194',
		'/usr/local/cok/SFS2X/buyitembug_v3.log197',
		'/usr/local/cok/SFS2X/buyitembug_v3.log199',
		'/usr/local/cok/SFS2X/buyitembug_v3.log236',
		'/usr/local/cok/SFS2X/buyitembug_v3.log263',
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
			if (!in_array($a['itemId'], array('200016','200020'))) {
				continue;
			}
			
			$timestr = substr($tok[0], 0, 19);
			$acttime = strtotime($timestr);
			if ($acttime > 1429446600) {
				continue;
			}
			
			$ret = $tok[10];
			$retarr = json_decode($ret, true);
			$costGold = $retarr['costGold'];
			if ($a['itemId'] == '200016' && $costGold != 15) {
				continue;
			}
			if ($a['itemId'] == '200020' && $costGold != 5) {
				continue;
			}
			
			$result[$a['itemId']] += ($a['num']-1);
			$player[$uid][$a['itemId']] += ($a['num']-1);//真正要被扣掉的！
		}
		
		// PROCESSED AT v4. 2015.04.19
		foreach ($player_been_subed[$server] as $gameuid => $subedItems) {
			foreach ($subedItems as $itemid => $hasbeen_subed) {
				if (isset($player[$gameuid][$itemid])) {
					file_put_contents($runlog, "OK-0,$server,$gameuid,$itemid,PROCESSED-V3\n", FILE_APPEND);
					continue;// processed at v3.
				}
				
				$over = $hasbeen_subed - 0;
				if ($over <= 0) {
					file_put_contents($runlog, "NG-1,$server,$gameuid,$itemid,$subcount,NOTOVER\n", FILE_APPEND);
					continue;
				}
				
				$sql_curr_sel = "select count from user_item where ownerId='$gameuid' and itemId='$itemid'";
				$resultsel = $page->executeServer($server,$sql_curr_sel,3,true);
				if (!$resultsel['ret']['data']) {
					$uuid = md5($gameuid.$itemid.time());
					$sql = "insert into user_item(uuid,ownerId,itemId,count) values ('$uuid', '$gameuid', '$itemid', $over)";
					$re = $page->executeServer($server,$sql,2);
					file_put_contents($runlog, "OK-1,$server,$gameuid,$itemid,$over,0,$over,$sql,".json_encode($re)."\n", FILE_APPEND);
					continue;
				}
				$owncnt_ori = $resultsel['ret']['data'][0]['count'];
				$newcnt = $owncnt_ori + $over;
				$sql_curr_set = "update user_item set count=$newcnt where ownerId='$gameuid' and itemId='$itemid'";
				$re = $page->executeServer($server,$sql_curr_set,2);
				file_put_contents($runlog, "OK-2,$server,$gameuid,$itemid,$over,$owncnt_ori,$newcnt,$sql_curr_set,".json_encode($re)."\n", FILE_APPEND);
			}
		}
}

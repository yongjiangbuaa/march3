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

$unifyArr = array();
$unifycount = file('/usr/local/cok/SFS2X/bugfix_mail_UnifyCount.log');//UnifyCount 721723876000050 50 200002 6672
foreach ($unifycount as $line) {
	$line = trim($line);
	$sp = explode(' ', $line);
	$uid = $sp[1];
	$itid = $sp[3];
	$num = $sp[4];
	$unifyArr[$uid][$itid] = $num;
}

// $file = '/usr/local/cok/SFS2X/bugfix_mail_delitem_uid.dat';
$file = '/usr/local/cok/SFS2X/bugfix_mail_ressolder_uid.dat';// from bugfix_mail_res_runlog.logV2 439+ uids.
$runlog = '/usr/local/cok/SFS2X/bugfix_mail_res_runlog.log';
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
		
		file_put_contents($runlog, 'bugfix_mail_res_runlog '.$buffer."\n", FILE_APPEND);
		$notopen = 0;
		$server = "s$sid";
		
		$sqlSoldier = "select uid, sum(free) soldier from user_army where uid='$uid' and (armyId =107000 or armyId=107100 or armyId=107200 or armyId = 107300)";
		$checkSoldierResult = $page->executeServer($server,$sqlSoldier,3);
		foreach ($checkSoldierResult['ret']['data'] as $row) {
			$soldierNum = $row['soldier'];
			if ($soldierNum < 10000) {
				continue;
			}
			$sqlSoldierInner = "select level from user_building where uid = '$uid' and itemId = '400000'";
			$checkSoldierResultInner = $page->executeServer($server,$sqlSoldierInner,3);
			foreach ($checkSoldierResultInner['ret']['data'] as $row) {
				if ($row['level'] < 10) {
					continue;
				}
				//记录该uid:1级兵>10000
				file_put_contents($runlog, '1LevelSoldier '.$uid." $sid $soldierNum\n", FILE_APPEND);

// 				$sqlSoldierInner2 = "select count(uid) cnt from goods_cost_record where userId='$uid' and time>1421928000000 and itemid=200800 and type=1;";
// 				$checkSoldierResultInner2 = $page->executeServer($server,$sqlSoldierInner2,3);
// 				foreach ($checkSoldierResultInner2['ret']['data'] as $row) {
// 					//最近两天使用一级兵道具次数
// 					file_put_contents($runlog, '1LevelUse '.$uid." $sid {$row['cnt']}\n", FILE_APPEND);
// 				}

				//减兵：
				$usedItem = $unifyArr[$uid]['200002'] - $unifyArr[$uid]['200800'];//高迁数量 - 加兵数量
				$addedNum = $usedItem *1000;
				$remainNum = $soldierNum - $addedNum;
				if($remainNum > 10000) {
					 $avgNum = round($remainNum/4);	
				}else{
					 $avgNum = 10000/4;
				}
				$upsql = "update user_army set free = $avgNum where uid = '$uid' and (armyId = 107000 or armyId = 107100 or armyId = 107200 or armyId = 107300)";
				echo $upsql."\n";
				$result = $page->executeServer($server,$upsql,2);
				file_put_contents($runlog, 'Update1LS '.$uid." $sid $soldierNum -> $avgNum ".json_encode($unifyArr[$uid])."\n", FILE_APPEND);
			}
		
			$sqlResource = "select uid, wood, food from user_resource where uid = '$uid' and (food > 1000000 or wood > 1000000)";
			$checkResourceResult = $page->executeServer($server,$sqlResource,3);
			foreach ($checkResourceResult['ret']['data'] as $row) {
				//记录该uid:资源>1M
				file_put_contents($runlog, 'ResWoodFood '.$uid." $sid {$row['wood']} {$row['food']}\n", FILE_APPEND);
				//扣资源
				$addedResource = $unifyArr[$uid]['200002']/4 * 150000; //高迁数量/4 * 150000; //加过的资源
				$food = $row['food']; $wood = $row['wood'];
				$remainFood = $food - $addedResource;
				$remainWood = $wood - $addedResource;
				if ($remainFood < 1000000) $remainFood = 1000000;
				if ($remainWood < 1000000) $remainWood = 1000000;
				if ($food < 1000000) $remainFood = $food;
				if ($wood < 1000000) $remainWood = $wood;
				$upsql = "update user_resource set food = $remainFood, wood = $remainWood where uid = '$uid'";
				echo $upsql."\n";
				$result = $page->executeServer($server,$upsql,2);
				file_put_contents($runlog, 'UpdateResWF '.$uid." $sid wood $wood -> $remainWood food $food -> $remainFood ".json_encode($unifyArr[$uid])."\n", FILE_APPEND);
			}
		}
		$pr++;
	}
}
echo date('Y-m-d H:i:s')." complete. $pr\n";


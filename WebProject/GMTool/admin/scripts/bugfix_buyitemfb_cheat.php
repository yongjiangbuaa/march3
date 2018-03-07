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
ini_set('memory_limit', '1024M');

global $servers;
$eventAll = array();

$runlog = '/tmp/buyitembugfix/bugfix_buyitemfb_vAll.runresult';
$runlog_oridata = '/tmp/buyitembugfix/bugfix_buyitemfb_vAll.oridata.runresult';

$player = array();

//120081677000105_110_init.dat
foreach (glob('/tmp/buyitembugfix/*.dat') as $fname) {
	$files[] = $fname;
}
sort($files);

$fcnt = 0;
foreach ($files as $fname) {
		$fcnt++;
		echo "$fcnt  ->  $fname\n";
		$bn = basename($fname,'.dat');
		$bntok = explode('_', $bn);
		$sid = $bntok[1];
		$server = 's'.$sid;
		$uid = $bntok[0];
		
		$revertuids = file_get_contents('/usr/local/cok/SFS2X/fbbug_revert_uids.txt');
		if (strpos($revertuids, $uid)) {
			continue;
		}
		
		$sql = "update userprofile set banTime=9223372036854775807 where uid ='$uid'";
		$re = $page->executeServer($server,$sql,2);
		cobar_query_global_db_cobar("update account_new set active = 1 where gameUid = '{$uid}'");
		
		echo "$server $uid\n";
		$sql_file = '/tmp/buyitembugfix/'.$bn.'.sql';
		
		$result = array();
		$result2 = array();
		$player = array();
		
		$f = file($fname);
		
		if ($bntok[2] == 'GetScienceInfo') {
			foreach ($f as $line) {
				$data = trim($line);
				if (empty($data)) {
					continue;
				}
				$tok = explode('|', $data, 11);
				$mul = $tok[10];
				$lastpos = strrpos($mul, '|');
				$sci = substr($mul, 0, $lastpos);
		
				$sci_json = trim($sci);
				$sci_arr = json_decode($sci_json,true);
		
				$science = $sci_arr['science'];
				foreach ($science as $logrecord) {
					$sqlsel = "select * from user_science where uid='$uid' and itemId='".$logrecord['itemId']."'";
					backup_old_data($server,$sqlsel);
					$values = array(
							'level' => $logrecord['level'],
					);
					$setsql = build_update_values($values);
					$sql = "update user_science set $setsql where uid='$uid' and itemId='".$logrecord['itemId']."'";
					execute_server($server,$sql);
				}
			}
		}
		
		if ($bntok[2] == 'init') {
			foreach ($f as $line) {
				$data = trim($line);
				if (empty($data)) {
					continue;
				}
				$tok = explode('|', $data, 8);
				
				$init_json = trim($tok[7]);
				$init_arr = json_decode($init_json,true);

				$resource = $init_arr['resource'];
				$sqlsel = "select * from user_resource where uid='$uid'";
				backup_old_data($server,$sqlsel);
				$dbrecord = select_old_data($server,$sqlsel);
				
				$values = array(
						'chip' => $resource['chip'],
						'wood' => $resource['wood'],
						'food' => $resource['food'],
						'silver' => $resource['silver'],
						'diamond' => $resource['diamond'],
						'stone' => $resource['stone'],
						'iron' => $resource['iron'],
				);
				
				$same = true;
				foreach ($values as $dbkey => $dbvalue) {
					if ($dbrecord[$dbkey] != $dbvalue) {
						$same = false;
						break;
					}
				}
				if ($same) {
					$sql = "update userprofile set banTime=0 where uid ='$uid'";
					$re = $page->executeServer($server,$sql,2);
					cobar_query_global_db_cobar("update account_new set active = 0 where gameUid = '{$uid}'");
					file_put_contents('/usr/local/cok/SFS2X/fbbug_revert_uids.txt', "$server,$uid\n", FILE_APPEND);
					continue;
				}
				
				$setsql = build_update_values($values);
				$sql = "update user_resource set $setsql where uid='$uid'";
				execute_server($server,$sql);
				
				$building = $init_arr['building'];
				foreach ($building as $logrecord) {
					$sqlsel = "select * from user_building where uuid='".$logrecord['uuid']."' and uid='$uid' and itemId='".$logrecord['itemId']."'";
					backup_old_data($server,$sqlsel);
					$values = array(
							'level' => $logrecord['level'],
					);
					$setsql = build_update_values($values);
					$sql = "update user_building set $setsql where uuid='".$logrecord['uuid']."' and uid='$uid' and itemId='".$logrecord['itemId']."'";
					execute_server($server,$sql);
				}
				
				$army = $init_arr['army'];
				foreach ($army as $logrecord) {
					$sqlsel = "select * from user_army where uid='$uid' and armyId='".$logrecord['id']."'";
					backup_old_data($server,$sqlsel);
					$values = array(
							'free' => $logrecord['free'],
							'march' => $logrecord['march'],
					);
					$setsql = build_update_values($values);
					$sql = "update user_army set $setsql where uid='$uid' and armyId='".$logrecord['id']."'";
					execute_server($server,$sql);
				}
				
				$user = $init_arr['user'];
				$sqlsel = "select * from userprofile where uid='$uid'";
				backup_old_data($server,$sqlsel);
				$values = array(
						'level' => $user['level'],
						'exp' => $user['exp'],
						'gold' => $user['gold'],
						'paidGold' => 0,
				);
				$setsql = build_update_values($values);
				$sql = "update userprofile set $setsql where uid='$uid'";
				execute_server($server,$sql);
				
				$vip = $init_arr['vip'];
				$sqlsel = "select * from user_vip where uid='$uid'";
				backup_old_data($server,$sqlsel);
				$values = array(
						'loginDays' => $vip['loginDays'],
						'score' => $vip['score'],
						'vipEndTime' => $vip['vipEndTime'],
				);
				$setsql = build_update_values($values);
				$sql = "update user_vip set $setsql where uid='$uid'";
				execute_server($server,$sql);
			
				$items = $init_arr['items'];
				foreach ($items as $logrecord) {
					$sqlsel = "select * from user_item where uuid='".$logrecord['uuid']."' and ownerId='$uid' and itemId='".$logrecord['itemId']."'";
					backup_old_data($server,$sqlsel);
					$values = array(
							'count' => $logrecord['count'],
					);
					$setsql = build_update_values($values);
					$sql = "update user_item set $setsql where uuid='".$logrecord['uuid']."' and ownerId='$uid' and itemId='".$logrecord['itemId']."'";
					execute_server($server,$sql);
				}
			}
		}
}

function select_old_data($server,$sqlsel){
	global $page, $runlog_oridata, $runlog, $uid;
	$result = $page->executeServer($server,$sqlsel, 3,true);
	return $result['ret']['data'][0];
}

function backup_old_data($server,$sqlsel){
// 	global $page, $runlog_oridata, $runlog, $uid;
// 	$result = $page->executeServer($server,$sqlsel, 3);
// 	file_put_contents($runlog_oridata.'_'.$uid, "$server >> $uid >> $sqlsel >> ".json_encode($result)."\n", FILE_APPEND);
}

function execute_server($server,$sql) {
	global $page, $sql_file, $runlog, $uid;
	file_put_contents($sql_file, $sql.";\n", FILE_APPEND);
	$re = $page->executeServer($server,$sql,2);
	file_put_contents($runlog.'_'.$uid, "$server >> $uid >> $sql >> ".json_encode($re)."\n", FILE_APPEND);
}

function build_update_values($param) {
	foreach ($param as $k=>$v) {
		$arr[] = "$k=".intval($v);
	}
	$str = implode(',', $arr);
	return $str;
}
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

$runlog = '/usr/local/cok/SFS2X/bugfix_buyitemfb_details.csv';

for ($sid = 1; $sid <= 276; $sid++) {
		$fname = '/usr/local/cok/SFS2X/bugdata/buyitembug_all.log'.$sid;
		
		echo "$fname\n";
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
			
			$ret = $tok[10];
			$retarr = json_decode($ret, true);
			$costGold = $retarr['costGold'];
			if ($a['itemId'] == '200016' && $costGold != 15) {
				continue;
			}
			if ($a['itemId'] == '200020' && $costGold != 5) {
				continue;
			}
			
			file_put_contents($runlog, "$server,$timestr,$uid,{$a['itemId']},{$a['num']},$costGold,{$retarr['remainGold']}"."\n", FILE_APPEND);
		}
}

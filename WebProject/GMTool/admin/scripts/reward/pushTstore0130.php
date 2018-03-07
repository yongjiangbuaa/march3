<?php
define('IN_ADMIN',true);
define('ADMIN_ROOT', realpath(dirname(__FILE__) . '/../../'));
define('PUSH_ROOT', ADMIN_ROOT. '/push');
include ADMIN_ROOT.'/config.inc.php';
include ADMIN_ROOT.'/servers.php';
ini_set('mbstring.internal_encoding','UTF-8');
includeModel("BasePage");
global $servers;
set_time_limit(0);

$page = new BasePage();
$task = $argv[1]?$argv[1]:0;
require_once ADMIN_ROOT.'/push/Push.php';
$push = new Push();
if ($task == 1) {
	echo "\n";
	foreach ($servers as $server=>$serverInfo){
		echo $server."\n";
		$sqlData = $page->executeServer($server,"select u.parseRegisterId from userprofile u inner join stat_reg r on u.uid = r.uid where (u.pf ='tstore' or r.pf = 'tstore') and parseRegisterId != ''",3,true);
		$sendList = array();
		echo count($sqlData['ret']['data'])."\n";
		foreach ($sqlData['ret']['data'] as $curRow){
			$sendList[] = $curRow['parseRegisterId'];
			if(count($sendList) > 100){
				$push->pushToMultiUser($sendList,"COK, 단 하루! 쇼핑한거 T캐쉬로 다~ 돌려받자!!");
				$sendList = array();
			}
		}
		if($sendList)
			$push->pushToMultiUser($sendList,"COK, 단 하루! 쇼핑한거 T캐쉬로 다~ 돌려받자!!");
	}
}
?>

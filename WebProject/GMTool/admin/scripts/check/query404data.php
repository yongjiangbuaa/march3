<?php
	define('IN_ADMIN',true);
	define('ADMIN_ROOT', realpath(dirname(__FILE__) . '/../../'));
	include ADMIN_ROOT.'/config.inc.php';
	include ADMIN_ROOT.'/servers.php';
	ini_set('mbstring.internal_encoding','UTF-8');
	includeModel("BasePage");
	global $servers;
	set_time_limit(0);
$page = new BasePage();
date_default_timezone_set('UTC');

$sql = "select country,count(1) sum from paycheck p inner join stat_reg r on p.uid = r.uid where status = '404' group by country";
global $servers;
foreach ($servers as $server=>$serverInfo){
	sleep(0.3);
	echo "$server\n";
	$sqlData = $page->executeServer($server,$sql,3,false);
	foreach ($sqlData['ret']['data'] as $curRow){
		$country = $curRow['country'];
		$countryResult[$country] += $curRow['sum'];
	}
}
foreach($countryResult as $country => $count){
	echo "$country : $count\n";
}
?>

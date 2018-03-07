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


$fixList = file("./playerList.txt");
global $servers;
foreach ($fixList as $line){
	$uid = trim($line);
	$result = cobar_getAccountInfoByGameuids($uid);
	if(!$result)
	{
		echo "$uid global\n";
		continue;
	}
	$server = 's'.$result[0]['server'];
	$sql = "select country from stat_reg where uid ='$uid'";
	$result = $page->executeServer($server, $sql, 3, true);
	if($result['ret']&&isset($result['ret']['data']))
	{
		$country = $result['ret']['data'][0]['country'];
		$countryResult[$country]++;
		echo "$uid=$country\n";
	}else{
		echo "$uid error\n";
	}
}
foreach($countryResult as $country => $count){
	echo "$country : $count\n";
}
?>

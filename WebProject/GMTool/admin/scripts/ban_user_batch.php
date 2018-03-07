<?php
define('IN_ADMIN',true);
define('ADMIN_ROOT', realpath(dirname(__FILE__) . '/../'));
include ADMIN_ROOT.'/config.inc.php';
include ADMIN_ROOT.'/servers.php';
include_once ADMIN_ROOT.'/admins.php';

ini_set('mbstring.internal_encoding','UTF-8');
includeModel("BasePage");
set_time_limit(0);

ini_set('memory_limit','2048M');

$arruids = array(
	'10021018652000091',
	'10207302344000091',
	'10207312994000091',
	'10207323062000091',
	'10207333308000091',
	'10223657869000091',
	'10223668331000091',
	'10223679345000091',
	'10223680360000091',
	'10223691364000091',
	'10223702398000091',
	'10223713412000091',
	'10223724416000091',
	'10223735431000091',
	'10768453928000041',
	'10856697530000078',

);
echo '11'.PHP_EOL;
$arruids2 = array_chunk($arruids,50);
//print_r($arruids2);
unset($arruids);
$uidServerArray = array();
foreach($arruids2 as $key=>$value){
	$value1 = array_values($value);

	$result['ret']['data'] = cobar_getAccountInfoByGameuids($value1);
	foreach ($result['ret']['data'] as $curRow){
		$uidServerArray[$curRow['gameUid']]=$curRow['server'];
	}
}
//$uidServerArray = array('10732401041000068'=>'68');
$page = new BasePage();
echo '22'.PHP_EOL;
// print_r($uidServerArray);

foreach($uidServerArray as $gameuid=>$server){
	$server = 's'.$server;

	echo $server.'\t'.$gameuid.PHP_EOL;
	$ret = $page->webRequest('kickuser', array('uid' => $gameuid), $server);
	$sql = "update userprofile set banTime=2422569600000 where uid ='$gameuid'";
	$ret = $page->executeServer($server,$sql, 2);
//	$sql  = "select count(1) cnt from server_usermail where touser='{$gameuid}' and sendBy='luyong' and title='Compensation Mail' ";
//	$insert_return = $page->executeServer($server,$sql,3);
//	print_r($insert_return);
//	if($insert_return['ret']['data'][0]['cnt'] == 1){
//		echo '<<<<'.$server."___".$gameuid.PHP_EOL;
//	}else{
//		echo $server."___".$gameuid.PHP_EOL;
//	}

}

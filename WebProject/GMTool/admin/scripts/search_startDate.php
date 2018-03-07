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

global $servers;
$eventAll = array();

$sql = "select date, reg from stat_date order by date,reg";
$checkSql = "select * from server_info where uid='server'";
foreach ($servers as $server=>$serverInfo){
	if(substr($server, 0 ,1) != 's'){
		continue;
	}
	if(substr($server, 1) < 22){
		continue;
	}
	//检索server_info表
	$checkResult = $page->executeServer($server,$checkSql,3);
	$checkRow = $checkResult['ret']['data'][0];
	$dateTime['kaifu'] = intval($checkRow['daoliangStart']);
	$dateTime['yangfu'] = intval($checkRow['yangfu']);
	$dateTime['daoliangStart'] = intval($checkRow['daoliangStart']);
	$dateTime['daoliangEnd']= intval($checkRow['daoliangEnd']);
	$dateTime['shuaguaiActStart'] = intval($checkRow['shuaguaiActStart']);
	$dateTime['activityTime'] = intval($checkRow['activityTime']);
	
	$flag = 0;
	$tempdate = '';
	$result = $page->executeServer($server,$sql,3);
	foreach ($result['ret']['data'] as $curRow){
		$yIndex = $curRow['date'];
		$eventAll[$server][$yIndex]['reg'] = $curRow['reg'];
	}
	$date = array_keys($eventAll[$server]);
	$cnt = count($date);
	for($i=0;$i<$cnt;$i++){
		$tempdate =$date[$i];
		if(($eventAll[$server][$date[$i]]['reg']<100) && $flag==0){// || $eventAll[$server][$date[$i]]['reg']>300
			$dateTime['kaifu'] = strtotime($tempdate)*1000;
			$flag=1;
		}else if($eventAll[$server][$date[$i]]['reg']<4000 && $flag==1){
			$dateTime['yangfu'] = strtotime($tempdate)*1000;
			$flag=2;
		}
		else if($eventAll[$server][$date[$i]]['reg']>=4000 && $flag==2){
			$dateTime['daoliangStart'] = strtotime($tempdate)*1000;
			$flag=3;
		}
		else if($eventAll[$server][$date[$i]]['reg']<4000 && $flag==3){
			$dateTime['daoliangEnd'] = strtotime($tempdate)*1000;
			$flag=4;
		}
	}
	if ($dateTime['shuaguaiActStart']==0 && $dateTime['shuaguaiActStart']<$dateTime['daoliangStart']){
		$dateTime['shuaguaiActStart'] = $dateTime['daoliangStart'];
	}
	if ($dateTime['activityTime'] < $dateTime['yangfu']) {
		$dateTime['activityTime'] = $dateTime['yangfu'];
	}
	if ($dateTime['activityTime'] == 0) {
		$dateTime['activityTime'] = $dateTime['kaifu'];
	}
	$inserSql = "insert into server_info(uid,activityTime,kaifu,yangfu,daoliangStart,daoliangEnd,shuaguaiActStart) 
		values('server',{$dateTime['activityTime']},{$dateTime['kaifu']},{$dateTime['yangfu']},{$dateTime['daoliangStart']},{$dateTime['daoliangEnd']},{$dateTime['shuaguaiActStart']}) 
		ON DUPLICATE KEY UPDATE kaifu = ".$dateTime['kaifu'].",activityTime =".$dateTime['activityTime'].",yangfu =".$dateTime['yangfu'].",daoliangStart = ".$dateTime['daoliangStart'].",daoliangEnd = ".$dateTime['daoliangEnd'].",shuaguaiActStart =".$dateTime['shuaguaiActStart'].";";
	echo "$server, $inserSql\n";
	$result = $page->executeServer($server,$inserSql,2);
}

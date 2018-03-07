<?php
!defined('IN_ADMIN') && exit('Access Denied');

$title = "行云翻译测试设置";
global $servers;

////////
$selectedServers = array();

$host = gethostbyname(gethostname());
if ($host == 'IPIPIP' || $host == 'IPIPIP') {
	$selectedServers[] = 'localhost';
	$selectedServers[] = 'localhost2';
	$selectedServers[] = 's1';
	$selectedServers[] = 's2';
}elseif ($host == 'IPIPIP'){
	$selectedServers[] = 'test';
	$selectedServers[] = 's0';
	$selectedServers[] = 's999001';
}else {
	$maxServer=0;
	foreach ($servers as $server=>$serverInfo){
		if(substr($server, 0 ,1) != 's'){
		 continue;
		}
		if (substr($server,1)>900000){
			continue;
		}
		$maxServer=max($maxServer,substr($server,1));
	}
	$sttt = $_REQUEST['selectServer'];
	if (!empty($sttt)) {
		$sttt = str_replace('，', ',', $sttt);
		$sttt = str_replace(' ', '', $sttt);
		$tmp = explode(',', $sttt);
		foreach ($tmp as $tt) {
			$tt = trim($tt);
			if (!empty($tt)) {
				if(strstr($tt,'-')){
					$ttArray=explode('-', $tt);
					$min=min($ttArray[1],$maxServer);
					for ($i=$ttArray[0];$i<=$min;$i++){
						$selectedServers[$i] = 's'.$i;
					}
				}else {
					if($tt<=$maxServer){
						$selectedServers[$tt] = 's'.$tt;
					}
				}
			}
		}
	}else{
		$client = new Redis();
		$client->connect('10.41.163.10');
		$serverRatioConf = $client->get("RATIO_OF_CHOOSE_SERVER");
		if (!empty($serverRatioConf)) {
			$serverConfArr = explode(';',$serverRatioConf);
			foreach($serverConfArr as $serverItem) {
				$idRatioArr = explode(':', $serverItem);
				$keyList[] = $idRatioArr[0];
				$selectedServers[$idRatioArr[0]] = 's'.$idRatioArr[0];
			}
			$defaultselectServer = min($keyList).'-'.max($keyList);
		}
		$sttt = $defaultselectServer;
	}
	krsort($selectedServers, SORT_NUMERIC);
}

if($_REQUEST['type'] == 'modify')
{
	$serverTemp = $_REQUEST['server'];
	$temp = explode('_', $serverTemp, 2);
	$modifyServer = $temp[0];
	$modifyActName = $temp[1];
	$statValue = $_REQUEST['newDate'];
	
	$redis = new Redis();
	
	$host = gethostbyname(gethostname());
	if ($host == 'IPIPIP' || $host == 'IPIPIP') {
		$redis->connect('URLIP',6379);
	}elseif ($host == 'IPIPIP'){
		$redis->connect('10.142.9.80',6379);
	}else {
		$currentIP = $servers[$modifyServer]['ip_inner'];
		$redis->connect($currentIP,6379);
	}
	
	$redis->hSet('chat_function_key',$modifyActName,$statValue);
	$redis->close();
}

//批量更新
if($_REQUEST['event'] == 'batchDo')
{
	$erversAndSidsArr=getSelectServersAndSids($_REQUEST['selectServer']);
	$selectServer=$erversAndSidsArr['withS'];
	$columnName=$_REQUEST['columnName'];
	$columnValue=$_REQUEST['columnValue']?$_REQUEST['columnValue']:0;
	
	$redis = new Redis();
	$host = gethostbyname(gethostname());
	foreach ($selectServer as $server=>$servInfo){
		if ($host == 'IPIPIP' || $host == 'IPIPIP') {
			$redis->connect('URLIP',6381);//72的本地库
		}elseif ($host == 'IPIPIP'){
			$redis->connect('10.142.9.80',6379);
		}else {
			$currentIP = $servers[$server]['ip_inner'];
			$redis->connect($currentIP,6379);
		}
		$redis->hSet('chat_function_key',$columnName,$columnValue);
	}
	$redis->close();
	exit('SUCCESS');
}

$header=array('xctranslate');
$data=array();
$redis = new Redis();
foreach ($selectedServers as $server){
	if(substr($server, 0 ,1) != 's' && strpos($server, 'test')===false && strpos($server, 'localhost')===false){
		continue;
	}
	if ($host == 'IPIPIP' || $host == 'IPIPIP') {
		$redis->connect('URLIP',6379);
	}elseif ($host == 'IPIPIP'){
		$redis->connect('10.142.9.80',6379);
	}else {
		$currentIP = $servers[$server]['ip_inner'];
		$redis->connect($currentIP,6379);
	}
	
	foreach ($header as $field){
		$temp=$redis->hGet('chat_function_key',$field);
		$data[$server][$field]=$temp?$temp:0;
	}
}
$redis->close();
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
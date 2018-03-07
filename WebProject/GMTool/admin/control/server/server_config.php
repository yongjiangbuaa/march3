<?php
!defined('IN_ADMIN') && exit('Access Denied');
include "serverConfig.php";
$headLine = "新用户进入服务器配置";
$rateConfigKey = "RATIO_OF_CHOOSE_SERVER";
//修改配置
$pfServerList = getAllPfServers();
$fServer = reset(array_keys($servers));
$fServer = 'global';
if($_REQUEST['action'] == 'modify'){
	$modifyRateList = array();
	$modifyCountryConfig = array();
	$modifyCountryConfigDel = array();
	$modifyPfConfig = array();
	$modifyPfConfigDel = array();
	foreach($_REQUEST as $key => $value)
	{
		$modifyArr = explode("_", $key);
		if(count($modifyArr) == 3){
			if($modifyArr[2] == 'rate' && $value)
				$modifyRateList[$modifyArr[1]] += $value;
			if($modifyArr[2] == 'country' && $value)
				$modifyCountryConfig[$modifyArr[1]] = $value;
			if($modifyArr[2] == 'country' && empty($value))
				$modifyCountryConfigDel[] = $modifyArr[1];
			if($modifyArr[2] == 'pf' && $value){
				$modifyPfConfig[$modifyArr[1]] = $value;
			}
			if($modifyArr[2] == 'pf' && empty($value)){
				$modifyPfConfigDel[] = $modifyArr[1];
			}
		}

	}
	if($modifyRateList){
		$modifyRate = array();
		foreach ($modifyRateList as $serverId=>$serverRate){
			$modifyRate[] = implode(":", array($serverId,$serverRate));
		}
		$page->redis(8,$rateConfigKey,implode(";", $modifyRate),$fServer);
	}else{
		$page->redis(9,$rateConfigKey,null,$fServer);
	}
	if ($modifyCountryConfig && $modifyCountryConfigDel) {
		foreach ($modifyCountryConfigDel as $sid) {
			$page->redis(6,$countryConfigKey,$sid,$fServer);
		}
	}
	if($modifyCountryConfig){
		$page->redis(5,$countryConfigKey,$modifyCountryConfig,$fServer);
	}else{
		$page->redis(9,$countryConfigKey,null,$fServer);
	}
	if($modifyPfConfig && $modifyPfConfigDel){
		foreach ($modifyPfConfigDel as $sid) {
			$page->redis(6,$pfConfigKey,$sid,$fServer);
		}
	}
	if($modifyPfConfig){
		$page->redis(5,$pfConfigKey,$modifyPfConfig,$fServer);
	}else{
		$page->redis(9,$pfConfigKey,null,$fServer);
	}
	//update db
	$config_server = array();
	foreach ($servers as $server=>$serverInfo){
		$serverId = substr($server, 1);
		if ($serverId < $displaymin) {
			continue;
		}
		if(!is_numeric($serverId)){
			$serverId = 0;
		}
		if (in_array($serverId, $pfServerList)) {
			continue;
		}
		$is_hot = ('true' == $_REQUEST["value_{$serverId}_hot"]) ? 1 : 0;
		$is_test = ('true' == $_REQUEST["value_{$serverId}_worldmap"]) ? 0 : 1;
		
		if ($serverId < 100) {
			$is_hot = 0;
//			$is_test = 0;
		}
		
		$config_server['is_hot'][$is_hot][] = $serverId;
		$config_server['is_test'][$is_test][] = $serverId;
	}
	foreach ($config_server as $field => $c) {
		foreach ($c as $key => $svrids) {
			$ids = implode(',', $svrids);
			$update_sql = "update cokdb_admin_deploy.tbl_webserver set $field=$key where svr_id in ($ids);";
// 			echo $update_sql;
			$page->globalExecute($update_sql, 2);
		}
	}
	
	$upxml = $_REQUEST['upxml'];
	if ($upxml == 1) {
		//调用107发布机的API，以更新servers.xml的状态
//		$status = file_get_contents('http://IPIPIP:8081/api/update_serversxml.php');
		$status = generte_serversXml($serverXmlPath, $serverCreatePath, $serverBakPath);
		if ($status=='OK') {
			$count = $page->redis(12, $channelKey, "servers.xml", $fServer);//通知各个服更新servers.xml
			$headLine = '导量比例 保存成功；servers.xml更新发布到各服'.$count.' 成功。';
//		}elseif ($status=='OK_NONE') {
//			$headLine = '导量比例 保存成功；servers.xml没有变化。';
		}else{
			$headLine = '导量比例 保存成功';
			$headAlert = 'servers.xml 更新、发布到各服 失败！';
		}
	}else{
		$headLine = '导量比例 保存成功';
	}
	
	include ADMIN_ROOT.'/servers.php';
}
//读取当前配置
$rateList = array();
//比例配置
$s;
$rateConfig = $page->redis(7,$rateConfigKey,null,$fServer);
$rateConfigArr = explode(";",$rateConfig);
foreach ($rateConfigArr as $config){
	list($serverId,$serverRate) = explode(":",$config);
	$rateList[$serverId] = $serverRate;
	$s.=$config;
}
//语言配置
$countryConfig = $page->redis(1, $countryConfigKey,null,$fServer);
$pfConfig = $page -> redis(1, $pfConfigKey, null, $fServer);

//汇总
$configData = array();
$sorted = array();
foreach ($servers as $server=>$serverInfo){
	$serverId = substr($server, 1);
	if(!is_numeric($serverId)){
		$serverId = 0;
	}
	if ($serverId < $displaymin) {
		continue;
	}
	if ($serverId > 900000) {
		continue;
	}
	if (in_array($serverId, $pfServerList)) {
		continue;
	}
	$configData[$server]['server'] = $server;
	$configData[$server]['serverId'] = $serverId;
	$configData[$server]['rate'] = $rateList[$serverId];
	$configData[$server]['country'] = $countryConfig[$serverId];
	$configData[$server]['pf'] = $pfConfig[$serverId];
	$configData[$server]['is_hot'] = $serverInfo['is_hot']?true:false;
	$configData[$server]['is_test'] = $serverInfo['is_test']?true:false;
	$sorted[] = $serverId;
}
$configData = array_reverse($configData);//倒序排列server
// rsort($sorted);
// array_multisort($sorted, $configData);

include( renderTemplate("{$module}/{$module}_{$action}") );
?>

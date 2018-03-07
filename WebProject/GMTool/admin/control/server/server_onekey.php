<?php
!defined('IN_ADMIN') && exit('Access Denied');
$headLine = "一键部署";

$statusMsg = array(
		0=>'OK',
		1=>'Stopping',
		2=>'Stopped',
);

if($_REQUEST['action'] == 'monitor'){
	$sid = $_REQUEST['sid'];
	$status = file_get_contents('http://IPIPIP:8081/api/get_server_status.php?sid='.$sid);
	$result = unserialize($status);
	
	$data = $result[$sid];
	echo json_encode($data);
	exit;
}

//汇总
$configData = array();
$sorted = array();
foreach ($servers as $server=>$serverInfo){
	$configData[$server]['server'] = $server;
	$serverId = substr($server, 1);
	if(!is_numeric($serverId)){
		$serverId = 0;
	}
	$configData[$server]['serverId'] = $serverId;
	$configData[$server]['status'] = '';//$statusMsg[$result[$serverId]['status']];
	$configData[$server]['log'] = '';//$result[$serverId]['log'];
	$sorted[] = $serverId;
}
rsort($sorted);
array_multisort($sorted, $configData);

include( renderTemplate("{$module}/{$module}_{$action}") );
?>
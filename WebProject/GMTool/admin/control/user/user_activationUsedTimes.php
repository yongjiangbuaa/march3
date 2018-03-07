<?php
!defined('IN_ADMIN') && exit('Access Denied');

global $servers;
$maxServer='';
foreach ($servers as $server=>$serverInfo){
	if(substr($server, 0 ,1) != 's'){
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
					$selectServer['s'.$i] = '';
					$selectId[]=intval($i);
				}
			}else {
				if($tt<=$maxServer){
					$selectServer['s'.$tt] = '';
					$selectId[]=intval($tt);
				}
			}
		}
	}
}

if (empty($selectServer)){
	$selectServer = $servers;
	foreach ($servers as $server=>$serverInfo){
		$selectId[]=intval(substr($server, 1));
	}
}

$showData=false;
$alertHeader='';

if ($_REQUEST['action'] == 'view') {
	$activationKey=trim($_REQUEST['activationKey']);
	if (empty($activationKey)){
		$alertHeader='请输入激活码';
	}else {
		$redis = new Redis();
		$redis->connect(GLOBAL_REDIS_SERVER_IP,6379);
		$totalNum=$redis->get($activationKey);
		$redis ->close();
		
// 		$data=array();
// 		$totalNum=0;
// 		$sql="select count(*) allUsers from user_activation where data like '%$activationKey%';";
// 		foreach ($selectServer as $server=>$serValue){
// 			$result=$page->executeServer($server, $sql, 3);
// 			if($result['ret']['data'][0]['allUsers']){
// 				$data[$server]=$result['ret']['data'][0]['allUsers'];
// 				$totalNum+=$result['ret']['data'][0]['allUsers'];
// 			}
// 		}
		if ($totalNum){
			$showData=true;
		}else {
			$alertHeader="没有查询到相关数据信息";
		}
	}
}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>
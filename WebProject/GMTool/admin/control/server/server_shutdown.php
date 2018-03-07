<?php
!defined('IN_ADMIN') && exit('Access Denied');
global $servers;
// foreach ($_REQUEST as $server=>$value)
// {
// 	if($servers[$server] && $value == 'on')
// 		$selectServer[] = $server;
// }
$maxServer=0;
foreach ($servers as $server=>$serverInfo){
	if(substr($server, 0 ,1) != 's'){
	 continue;
	}
	$maxServer=max($maxServer,substr($server,1));
}
$todotime = $_REQUEST['todotime'];
if (empty($todotime)) {
	$todotime = 15;
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
					$selectServer['s'.$i] = 's'.$i;
					$selectId[]=$i;
				}
			}else {
				if($tt<=$maxServer || $tt > 900000){
					$selectServer['s'.$tt] = 's'.$tt;
					$selectId[]=$tt;
				}
			}
		}
	}
}

if (count($selectServer) > 0) {
	$op_msg = date('Y-m-d H:i:s', time()+28800)." + $todotime' = ". date('Y-m-d H:i:s', time()+28800+60*$todotime)."<br/>";
	try {
		$now = (int)microtime(true)*1000;
		$param = array(
				'now'=>$now,
				'code'=>md5($now."6RhshZxnmzUZ6cXb"),
				'time'=>$todotime,
		);
		foreach ($selectServer as $server)
		{
			$ret = $page->webRequest('stopserver',$param,$server);
			if($ret == 'ok'){
				$op_msg .= "$server ok<br />";
			}else{
				$error_msg .= "$server fail<br />";
			}
		}
	} catch ( Exception $e ) {
		$error_msg = $e->getMessage ();
	}
}

//总服处理
$rediskey = 'server.restart.time';
$client = new Redis();
//$client->connect ('10.1.16.211');
$client->connect ('127.0.0.1');
$_time = $_REQUEST['restart_time'];
if(empty($_time) && $_REQUEST['parm1'] == 1){
	$client->delete($rediskey);
}
if(!empty($_time)){
	$client->set($rediskey,$_time);
}
$restart_time = $client->get($rediskey);
include( renderTemplate("{$module}/{$module}_{$action}") );
?>

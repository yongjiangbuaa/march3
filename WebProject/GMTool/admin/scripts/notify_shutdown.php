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
$maxServer=0;
foreach ($servers as $server=>$serverInfo){
	if(substr($server, 0 ,1) != 's'){
	 continue;
	}
	$maxServer=max($maxServer,substr($server,1));
}
$todotime = 15;//$_REQUEST['todotime'];
if (empty($todotime)) {
	$todotime = 15;
}
$sttt = '53,164,166,196,199,562';//$_REQUEST['selectServer'];
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
	$op_msg = date('Y-m-d H:i:s', time()+28800)." + $todotime' = ". date('Y-m-d H:i:s', time()+28800+60*$todotime)."\n";
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
				$op_msg .= "$server ok\n";
			}else{
				$error_msg .= "$server fail\n";
			}
		}
	} catch ( Exception $e ) {
		$error_msg = $e->getMessage ();
	}
}

echo $op_msg."\n";
echo $error_msg."\n";
?>


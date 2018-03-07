<?php
!defined('IN_ADMIN') && exit('Access Denied');

//serverid  all或者 服  1 int值
if ($_REQUEST['type']=='repair') {
	$serverid = $_REQUEST['serverid'];
	if (!$serverid){
		exit('serverid not empty!');
	}
	$server = 's'.$serverid;
	if($serverid == 'all'){
		$server = $_COOKIE['Gserver2'];
	}
	$ret = $page->webRequest("repairmulicity",array('serverid'=>$serverid),$server );
	adminLogSystem($adminid,array('serverid'=>$serverid,'execute result'=>"$ret"));
	exit($ret);
	
}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>
<?php
define('IN_ADMIN',true);
define('APP_ROOT', realpath(dirname(__FILE__) . '/../gameengine/test'));
define('ADMIN_ROOT', realpath(dirname(__FILE__) . '/'));
define('TIMES', 1.5);
ini_set('mbstring.internal_encoding','UTF-8');
include ADMIN_ROOT.'/config.inc.php';
include ADMIN_ROOT.'/admins.php';
include ADMIN_ROOT.'/menu_config.php';
include_once ADMIN_ROOT.'/servers.php';
$page = new BasePage();

foreach ( $argv as $arg ) {
	$kv = explode ( '=', $arg, 2 );
	$_REQUEST [$kv [0]] = $kv [1];
}

$chktime = time();
$startTime = $chktime - 1800;

$chktime *= 1000;
$startTime *= 1000;

$isNG = false;
$errInfo = array();

	$server = 's152';
	$sql = "select count(uid) count from stat_reg where time>$startTime and time<$chktime";
// 	echo $sql;
	$ret = $page->executeServer ( $server, $sql, 2 );
	$result = $ret ['ret'] ['data'] [0] ['count'];
// 	echo $result;
	$res = array('data'=>$result, 'desc'=>'');
	if ($result > 500) {
		$isNG = true;
	}

if($isNG){
   exit('fb reg alarm > 500. '.$result);
}
else{
   exit('zhengchang'); 
}

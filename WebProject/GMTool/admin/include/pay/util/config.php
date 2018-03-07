<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
header ( "Content-Type:text/html; charset=utf-8" );
date_default_timezone_set ("Etc/GMT");
ob_clean();
ob_implicit_flush(true);
define('PAY_LOG_DIR', realpath(dirname(__FILE__) . '/../log'));

function showMsg($msg){
	echo $msg."<br />";
	ob_flush();
}
function writeLog($file,$log){
	$currTime = time();
	$msg = date("Y-m-d H:i:s",$currTime) . '	' . $log;
	file_put_contents( PAY_LOG_DIR."/$file.log", $msg . "\n", FILE_APPEND);
}
?>
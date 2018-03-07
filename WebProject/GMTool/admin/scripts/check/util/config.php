<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
header ( "Content-Type:text/html; charset=utf-8" );
date_default_timezone_set ("Etc/GMT-8");
ob_clean();
define('LOG_DIR', realpath(dirname(__FILE__) . '/../log'));

function showMsg($msg){
	echo $msg."<br />";
	ob_flush();
}
function writeLog($file,$log){
	$currTime = time();
	$msg = date("Y-m-d H:i:s",$currTime) . '	' . $currTime . '	' . json_encode($log);
	file_put_contents( LOG_DIR."/$file.log", $msg . "\n", FILE_APPEND);
}
?>
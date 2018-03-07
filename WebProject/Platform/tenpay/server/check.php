<?php
include './../util/config.php';

header ( "Content-Type: application/json; charset=utf-8" );
date_default_timezone_set ( "Asia/Shanghai" );

writeLog('call.'.date("Ymd",time()),$_REQUEST);
$billno = $_REQUEST['billno'];
$openid = $_REQUEST['openid'];
if(strlen($openid) > 50)
	return array();
$sql = "select * from paylog where openid=? and billno=? and state = 0";
$retObj = array();
$reason = '';
$querySuccess = false;
$db_client = getPDO();
if($db_client){
	$querySuccess = true;
	$curRow = $db_client->fetchOne($sql,array($openid,$billno));
	if($curRow) {
		$retObj = $curRow;
	}
}
if($querySuccess)
	$retObj['ret'] = 0;
else{
	$retObj['ret'] = 1;
	$retObj['msg'] = $reason;
	writeLog('fail.'.date("Ymd",time()),$retObj);
}
writeLog('call.'.date("Ymd",time()),json_encode($retObj));
echo json_encode($retObj);

function writeLog($file,$log){
	$currTime = time();
	$msg = date("Y-m-d H:i:s",$currTime) . '	' . $currTime . '	' . json_encode($log); 
	file_put_contents( "./log/check.$file.log", $msg . "\n", FILE_APPEND);
}
?>
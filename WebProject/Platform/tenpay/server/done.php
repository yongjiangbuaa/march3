<?php
include './../util/config.php';

header ( "Content-Type: application/json; charset=utf-8" );
date_default_timezone_set ( "Asia/Shanghai" );

writeLog('call.'.date("Ymd",time()),$_REQUEST);
$billno = $_REQUEST['billno'];
$openid = $_REQUEST['openid'];
if(strlen($openid) > 50)
	return array();
//根据该玩家最后20次支付记录判断此笔订单是否有效，openid为服务器记录值
$sql = "select * from paylog where openid=? and billno=? and state=0";
$retObj = array('billno'=>$billno);
$reason = '';
$querySuccess = false;
$db_client = getPDO();
if($db_client){
	$curRow = $db_client->fetchOne($sql,array($openid,$billno));
	if ($curRow) {
		$querySuccess = true;
	}
}
if($querySuccess){
	$currTime = time();
	$updateSql = "update paylog set state=1,finishTime=? where billno=? and openid=?";
	$db_client->execute($updateSql, array($currTime,$billno,$openid));
	$retObj['ret'] = 0;
}
else{
	$retObj['ret'] = 1;
	$retObj['msg'] = $reason;
	writeLog('fail.'.date("Ymd",time()),$failObj);
}
echo json_encode($retObj);

function writeLog($file,$log){
	$currTime = time();
	$msg = date("Y-m-d H:i:s",$currTime) . '	' . $currTime . '	' . json_encode($log); 
	file_put_contents( "./log/done.$file.log", $msg . "\n", FILE_APPEND);
}
?>
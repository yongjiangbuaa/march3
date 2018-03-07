<?php
include './util/config.php';

header ( "Content-Type: application/json; charset=utf-8" );
date_default_timezone_set ( "Asia/Shanghai" );

writeLog('call.'.date("Ymd",time()),$_REQUEST);
$openid = $_REQUEST['openid'];
if(strlen($openid) > 50)
	return array();
$timeFilter = time() - 7*86400;
$sql = "select * from paylog where openid=? and createTime>? and state = 0  order by createTime desc limit 20";
$retObj = array('data'=>array());
$reason = '';
$querySuccess = false;
$db_client = getPDO();
if($db_client){
	$querySuccess = true;
	$dataList = $db_client->fetchAll($sql,array($openid,$timeFilter));
	foreach ($dataList as $curRow) {
		$retObj['data'][] = array('billno'=>$curRow['billno'],'openid'=>$curRow['openid'],'itemId'=>$curRow['itemId']);
	}
}
if($querySuccess)
	$retObj['ret'] = 0;
else{
	$retObj['ret'] = 1;
	$retObj['msg'] = $reason;
	writeLog('fail.'.date("Ymd",time()),$retObj);
}
echo json_encode($retObj);

function writeLog($file,$log){
	$currTime = time();
	$msg = date("Y-m-d H:i:s",$currTime) . '	' . $currTime . '	' . json_encode($log); 
	file_put_contents( "./server/log/query.$file.log", $msg . "\n", FILE_APPEND);
}
?>
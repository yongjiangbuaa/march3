<?php
include './util/OpenApiV3.php';
include './util/config.php';

header ( "Content-Type: application/json; charset=utf-8" );
date_default_timezone_set ( "Asia/Shanghai" );

writeLog('call.'.date("Ymd",time()),$_REQUEST);

// $enableList = array(300,680,1280,3280,6480,1630,60);
// if(!in_array($_REQUEST['amt'],$enableList)){
// 	$retObj = array('ret'=>1,'msg'=>'invalid amt','request'=>$_REQUEST);
// 	writeLog('fail.'.date("Ymd",time()),$retObj);
// 	echo json_encode($retObj);
// 	exit;
// }

$sdk = new OpenApiV3($appid, $appkey);
$sdk->setServerName($server);
$script_name = '/mpay/pay_m';
$sdk->setIsStat(false);
$params_api = array();
$params_api['openid'] = $_REQUEST['openid'];
$params_api['openkey'] = $_REQUEST['openkey'];
$params_api['pf'] = $_REQUEST['pf'];
$params_api['pfkey'] = $_REQUEST['pfkey'];
$params_api['ts'] = time();
$params_api['zoneid'] = "1";
$params_api['amt'] = $_REQUEST['amt'];
$cookie = array();
$cookie['session_id'] = $_REQUEST['sessionId'];
$cookie['session_type'] = $_REQUEST['sessionType'];
$cookie['org_loc'] = $script_name;
$retObj = $sdk->api($script_name, $params_api, 'get', 'https', $cookie);

writeLog('call.'.date("Ymd",time()),json_encode($retObj));

if($retObj['ret'] != 0){
	$retObj['request'] = $_REQUEST;
	writeLog('fail.'.date("Ymd",time()),$retObj);
}else{
	$oriData['request'] = $_REQUEST;
	$oriData['result'] = $retObj;
	$billno = $retObj['billno'];
	$openid = $_REQUEST['openid'];
	$currentTime = time();
	$itemId = $_REQUEST['itemId'];
	$retObj['data'] = array(array('billno'=>$billno,'openid'=>$openid,'itemId'=>$itemId));
	//记录回调数据
	$insertData = array($billno,$openid,$currentTime,$itemId,json_encode($oriData),0,0);
	$sql = "insert into paylog values (?,?,?,?,?,?,?) on duplicate key update state = state";
	try {
		$db_client = getPDO();
	} catch (Exception $e) {
		writeLog('call.'.date("Ymd",time()), $e->getMessage());
	}
	if($db_client){
		$ret = $db_client->execute($sql,$insertData);
		if($ret){
			echo json_encode($retObj);
			return;
		}
	} else {
		writeLog('call.'.date("Ymd",time()),'DB connection is null');
	}
	$retObj['reason'] = $reason;
	writeLog('fail.'.date("Ymd",time()),$retObj);
}
echo json_encode($retObj);

function writeLog($file,$log){
	$currTime = time();
	$msg = date("Y-m-d H:i:s",$currTime) . '	' . $currTime . '	' . json_encode($log); 
	file_put_contents( "./server/log/create.$file.log", $msg . "\n", FILE_APPEND);
}
?>
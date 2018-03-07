<?php
include './util/OpenApiV3.php';
include './util/config.php';

header ( "Content-Type: application/json; charset=utf-8" );
date_default_timezone_set ( "Asia/Shanghai" );

writeLog('call.'.date("Ymd",time()),$_REQUEST);

$sdk = new OpenApiV3($appid, $appkey);
$sdk->setServerName($server);
$script_name = '/mpay/get_balance_m';
$sdk->setIsStat(false);
$params_api = array();
$params_api['openid'] = $_REQUEST['openid'];
$params_api['openkey'] = $_REQUEST['openkey'];
$params_api['pf'] = $_REQUEST['pf'];
$params_api['pfkey'] = $_REQUEST['pfkey'];
$params_api['ts'] = time();
$params_api['zoneid'] = "1";
$cookie = array();
$cookie['session_id'] = $_REQUEST['sessionId'];
$cookie['session_type'] = $_REQUEST['sessionType'];
$cookie['org_loc'] = $script_name;
$retObj = $sdk->api($script_name, $params_api, 'get', 'https', $cookie);

if($retObj['ret'] != 0){
	$retObj['request'] = $_REQUEST;
	writeLog('fail.'.date("Ymd",time()),$retObj);
}
echo json_encode($retObj);

function writeLog($file,$log){
	$currTime = time();
	$msg = date("Y-m-d H:i:s",$currTime) . '	' . $currTime . '	' . json_encode($log); 
	file_put_contents( "./server/log/balance.$file.log", $msg . "\n", FILE_APPEND);
}
?>
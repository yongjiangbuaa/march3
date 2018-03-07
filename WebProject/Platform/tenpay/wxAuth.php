<?php

require_once './util/lib/SnsNetwork.php';
require_once './util/lib/SnsSigCheck.php';
require_once './util/lib/SnsStat.php';

header ( "Content-Type: application/json; charset=utf-8" );
date_default_timezone_set ( "Asia/Shanghai" );

writeLog('call.'.date("Ymd",time()),$_REQUEST);

$protocol = 'https';
$url = $protocol . '://api.weixin.qq.com/sns/oauth2/access_token';
$cookie = array();
$method = 'get';
$params= array();
$params['appid'] = $_REQUEST['appid'];
$params['secret'] = 'a88478251b607389490e36c95db2855f';
$params['code'] = $_REQUEST['code'];
$params['grant_type'] = $_REQUEST['grant_type'];
// 发起请求
$ret = SnsNetwork::makeRequest($url, $params, $cookie, $method, $protocol);
	
if(!is_null($ret['errcode']) || !is_null($ret['msg']['errcode'])){
	writeLog('fail.'.date("Ymd",time()),$ret);
}
echo $ret['msg'];

function writeLog($file,$log){
	$currTime = time();
	$msg = date("Y-m-d H:i:s",$currTime) . '	' . $currTime . '	' . json_encode($log); 
	file_put_contents( "./server/log/wxAuth.$file.log", $msg . "\n", FILE_APPEND);
}
?>
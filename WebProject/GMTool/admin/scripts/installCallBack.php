<?php
define('IN_ADMIN',true);
define('ADMIN_ROOT', realpath(dirname(__FILE__) . '/../'));
include ADMIN_ROOT.'/config.inc.php';
include ADMIN_ROOT.'/servers.php';
ini_set('mbstring.internal_encoding','UTF-8');
includeModel("BasePage");
global $servers;
set_time_limit(0);

if($_REQUEST['idfa'])
	$gaid = $_REQUEST['idfa'];
if($_REQUEST['gps_adid'])
	$gaid = $_REQUEST['gps_adid'];
writeLog('call.'.date("Ymd",time()),$_REQUEST);
if(!$gaid){
	return;
}

// Construct the TROPHiT attribution handler:
require_once('TrophitAttributionHandler.php');
use \Trophit\TrophitAttributionHandler;
$tah = new TrophitAttributionHandler(
	'0b0fe9978bb60b7657403c99598f52805ddcdb14',
	'fe49aae9d1e025bb6b8999dd6ba3cac3e55c9b9b');
	
// Perform redemption:
$tah->redeemForAdjust(
	TrophitAttributionHandler::TransactionalRedeem,
	'enableContent', 'onRedeemFailure');

function writeLog($file,$log){
	global $gaid;
	$msg = date("Y-m-d H:i:s",time()) . '	' . time() . '	' . $gaid . '	' . json_encode($log); 
	file_put_contents( "./installCallBackLog/$file.log", $msg . "\n", FILE_APPEND);
}

function enableContent($app, $device) {
	$result = array($app,$device);
	writeLog('success.'.date("Ymd",time()),$result);
	global $gaid;
	$page = new BasePage();
	$enabledVoucherCodes = array();
	foreach ($app->vouchers as $code => $voucher) {
		// TODO: grant the user content based on
		// $voucher->quantity and voucher->type
		// If successful, add the voucher code to $enabledVoucherCodes:
		foreach ($voucher->values as $rewardInfo){
			//{"quantity":"10000","type":"food","DT_RowId":"row_1"}
			//gold,0,100|wood,0,10000|food,0,10000|stone,0,10000|iron,0,10000
			if(in_array($rewardInfo->type, array('gold','food','wood','iron','silver'))){
				if($rewardInfo->type == 'silver')
					$rewardType = 'stone';
				else
					$rewardType = $rewardInfo->type;
				$time = floor(microtime(true)*1000);
				$device = $gaid;
				$reward = $rewardInfo->type.',0,'.$rewardInfo->quantity;
				$rewardSql = "insert into adreward (`device`,`time`,`user`,`reward`,`state`) values ('$device','$time','','$reward',0)";
				$page->executeServer('global', $rewardSql,1,true);
			}
		}
		$enabledVoucherCodes[] = $code;
	}
	return $enabledVoucherCodes;
}
 
function onRedeemFailure($error) {
	writeLog('fail',$error->message);
// 		error_log($error->message);
}
?>
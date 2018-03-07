<?php
	define('IN_ADMIN',true);
	define('ADMIN_ROOT', realpath(dirname(__FILE__) . '/../../'));
	include ADMIN_ROOT.'/config.inc.php';
	include ADMIN_ROOT.'/servers.php';
	ini_set('mbstring.internal_encoding','UTF-8');
	includeModel("BasePage");
	global $servers;
	set_time_limit(0);
	define('SCRIPTROOT', realpath(dirname(__FILE__) . '/'));
	include SCRIPTROOT.'/util/config.php';
	include SCRIPTROOT.'/util/lib/SnsNetwork.php';
	
	$url = 'https://accounts.google.com/o/oauth2/token';
	$params = array();
// 	$params['grant_type'] = 'authorization_code';
// 	$params['code'] = '4/2pEFwfn42MtTB77LqWCzb7qfflUIpn5wUVLa82ZXZtM';
// 	$params['client_id'] = '312229299745-r4s6j62ipvupte12giov0c94dm8ca9es.apps.googleusercontent.com';
// 	$params['client_secret'] = 'gMQYYKtVONMY3_8_PwUIt-DW';
// 	$params['redirect_uri'] = 'https://localhost/oauth2callback';

	function getNewToken(){
		$url = 'https://accounts.google.com/o/oauth2/token';
		$params = array();
		$params['grant_type'] = 'refresh_token';
		$params['client_id'] = '312229299745-r4s6j62ipvupte12giov0c94dm8ca9es.apps.googleusercontent.com';
		$params['client_secret'] = 'gMQYYKtVONMY3_8_PwUIt-DW';
		$params['refresh_token'] = '1/SA5SO5cScRKxXVK7A06FPJSC80HQb0tE8fyz3eIX4SFIgOrJDtdun6zK6XiATCKT';
		$httpResult = SnsNetwork::makeRequest($url,$params,array(),'post');
		$httpObj = json_decode($httpResult,true);
		$access_token = $httpObj['access_token'];
		return $access_token;
	}
	
	//支付时间范围	验证过的订单判断
	//读取30天内所有支付信息
	$endTime = strtotime(date('Y-m-d')) * 1000;
	$startTime = $endTime - 86400*1000*3;
	$sql = "select p.uid,p.orderId,p.pf,p.time paytime,p.orderParam,c.time checktime,c.status checkstatus from paylog p left join paycheck c on p.uid = c.uid and p.orderId = c.orderId and p.pf = c.pf "
			."where p.time > $startTime and p.time < $endTime "
			."and p.pf = 'google' and p.orderParam != ''";
	//间隔时间1天 3天 7天 15天
	$checkDayList = array(1,3,7,15);
	$checkDayList = array();
	$checkTime= floor(microtime(true)*1000);
	$logFile = date("Ymd",time());
	$page = new BasePage();
	foreach ($servers as $server=>$serverInfo){
		sleep(0.3);
		echo "$server\n";
		$params = array();
		$params['access_token'] = getNewToken();
		$sqlData = $page->executeServer($server,$sql,3,false);
		foreach ($sqlData['ret']['data'] as $curRow){
			$uid = $curRow['uid'];
			$orderId = $curRow['orderId'];
			$pf = $curRow['pf'];
			$orderParam = $curRow['orderParam'];
			$needCheck = true;
			if($curRow['checktime']){
				if($curRow['checkstatus'] != 'success'){
					$needCheck = false;
				}else{
					$dayAfterCheck = floor(($curRow['checktime'] - $endTime)/86400/1000);
					if(!in_array($dayAfterCheck, $checkDayList))
						$needCheck = false;
				}
			}
			if($needCheck){
				$orderObj= json_decode($orderParam,true);
				$productId = $orderObj['productId'];
				$purchaseToken = $orderObj['purchaseToken'];
				$checkUrl = "https://www.googleapis.com/androidpublisher/v2/applications/com.hcg.cok.gp/purchases/products/$productId/tokens/$purchaseToken";
				$httpResult = SnsNetwork::makeRequest($checkUrl,$params,array(),'get');
				$httpObj = json_decode($httpResult,true);
				if($httpObj['purchaseState'] === 0)
					$status = 'success';
				else if($httpObj['purchaseState'] === 1)
					$status = 'cancel';
				else if($httpObj['purchaseState'] === 2)
					$status = 'refunded';
				else
					$status = $httpObj['error']['code'];
				if($status == 403)
					break 2;
				if(!$curRow['checkstatus'] || $httpObj['purchaseState'] !== 0){
					$apidata = json_encode($httpObj);
					$newCheck = "replace into paycheck values('$uid','$orderId','$pf','$checkTime','$status','$apidata')";
					$page->executeServer($server,$newCheck,1,true);
				}
				$msg = $server.'	'.$uid.'	'.$orderId.'	'.$status."\n";
				file_put_contents( LOG_DIR."/google.$logFile.log", $msg , FILE_APPEND);
				echo $msg;
			}else{
				$msg = $server.'	'.$uid.'	'.$orderId.'	skip'."\n";
// 				echo $msg;
			}
		}
	}
	if($status == 403)
		echo "403 error\n";
	else
		echo "done\n";
?>
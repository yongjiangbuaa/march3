<?php
define('IN_ADMIN',true);
define('ADMIN_ROOT', realpath(dirname(__FILE__) . '/../../'));
include ADMIN_ROOT.'/config.inc.php';
include ADMIN_ROOT.'/servers.php';
ini_set('mbstring.internal_encoding','UTF-8');
includeModel("BasePage");
global $servers;
set_time_limit(0);

$page = new BasePage();
$task = $argv[1]?$argv[1]:0;
if ($task == 1) {
	echo "\n";
	$filePath = './payandroiderr.log';
	$fileData = file($filePath);
	$orderList = array();
	foreach ( $fileData as $cmdString ) {
		$cmdArray = explode('--', $cmdString);
		$user = substr($cmdArray[4],7);
		list($uid,$name) = explode(',', $user);
		$param = substr($cmdArray[5],7);
		$param = json_decode($param,true);
		$nonce = $param['nonce'];
		$paymentSeq = $param['paymentSeq'];
		if($orderList[$paymentSeq])
			continue;
		$orderList[$paymentSeq] = 1;
		$accountSqlData = $page->executeServer('global',"select * from account_new where gameuid = '$uid'",1,true);
		$gameAccount = $accountSqlData['ret']['data'][0]; 
		if($gameAccount){
			$server = 's'.$gameAccount['server'];
// 			$url = "http://s$server.cok.elexapp.com:8080/gameservice/paymentrecheck";
// 			$urlParam = array('uid'=>$uid,'nonce'=>$nonce,'paymentSeq'=>$paymentSeq);
// 			$page->post_request($url,$urlParam);
			$paylog = $page->executeServer($server,"select * from paylog where orderId = '$paymentSeq'",1,true);
			echo $paymentSeq.' '.$paylog['ret']['data'][0]['orderId']."\n";
		}else{
			echo $paymentSeq." error\n";
		}
	}
}
?>

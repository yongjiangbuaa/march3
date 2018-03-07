<?php
!defined('IN_ADMIN') && exit('Access Denied');

$key='3de36cb3125c5e21';
$resutArray=array(
	'success'=>'购买成功',
	'fail1'=>'参数错误',
	'fail2'=>'验证错误',
	'fail3'=>'礼包不存在', 
	'fail4'=>'用户不存在', 
	'fail5'=>'支付错误'
);



if ($_REQUEST['type']=='gmBuy') {
	$uid = $_REQUEST['uid'];
	$packageId = $_REQUEST['packageId'];
	if (!$uid){
		exit('uid不能为空!');
	}
	if (!$packageId){
		exit('礼包ID不能为空!');
	}
	$time=time();
	$code=md5($time.$key.$uid.$packageId);
	$ret = $page->webRequest("payByGM",array('uid'=>$uid,'productId'=>$packageId,'time'=>$time,'code'=>$code));
	adminLogUser($adminid,$uid,$currentServer,array('packageId'=>$packageId,'执行结果'=>$resutArray[$ret]));
	exit($resutArray[$ret]);
	
}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>
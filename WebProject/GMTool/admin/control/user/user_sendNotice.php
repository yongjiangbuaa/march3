<?php
!defined('IN_ADMIN') && exit('Access Denied');
$showData = false;
$type = $_REQUEST['action'];
if($_REQUEST['contents'])
	$contents = $_REQUEST['contents'];
if($contents){
	$contents = addslashes ($contents);
	$ret=sendReward($contents);
	if($ret=='success'){
		$headAlert='发布成功';
	}else {
		$headAlert='发布失败';
	}
}
function sendReward($msg) {
	$page = new BasePage ();
	$result=$page->webRequest ( 'sendNotice', array (
			'msg' => $msg
	) );
	return $result;
}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>

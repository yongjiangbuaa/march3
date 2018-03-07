<?php
!defined('IN_ADMIN') && exit('Access Denied');
$type = $_REQUEST['type'];
if($_REQUEST['username'])
	$username = $_REQUEST['username'];
if($_REQUEST['useruid'])
	$useruid = $_REQUEST['useruid'];
if($_REQUEST['showContents']){
	$showContents = $_REQUEST['showContents'];
}
if($type){
	if($username){
		$account_list = cobar_getValidAccountList('name', $username);
		$uid = $account_list[0]['gameUid'];
		$sql = "select * from mail where toUser = '{$uid}' order by createtime desc limit 500";
	}else{
		$sql = "select * from mail where toUser = '{$useruid}' order by createtime desc limit 500";
	}
	$result = $page->execute($sql,3);
//	echo json_encode($result['ret']['data']);
	if(!$result['error'] && $result['ret']['data']){
		$lang = loadLanguage();
		$clientXml = loadXml('goods','goods');
		$mails = $result['ret']['data'];
		$No = 1;
		foreach ($mails as $key => $mail) {
			$mails[$key]['No'] = $No++;
			$mails[$key]['createTime'] = date('Y-m-d H:i:s',$mail['createTime']/1000);
			$mails[$key]['fromUser'] = $lang[$mail['fromUser']];
			$mails[$key]['fromName'] = $lang[$mail['fromName']];
			if(isset($lang[$mail['title']])){
				$mails[$key]['title'] = $lang[$mail['title']];
			}
//			$mails[$key]['rewardId'] = explode("|",$mail['rewardId']);
//			$mails[$key]['contents'] = $lang[$mail['contents']];
			$mails[$key]['status'] = $mail['status']!=0?'已读':'未读';
			$mails[$key]['rewardStatus'] = $mail['rewardStatus']!=0?'已接收':'未接收';
			$mails[$key]['saveFlag'] = $mail['saveFlag']!=3?'存在':'已删除';
		}
	}else{
		$error_msg = ‘UID不存在或错误’;
	}
}
if( $_REQUEST['uid']){
	$uid = trim($_REQUEST['uid']);
	$sql = "select contents from mail where uid='$uid' order by createtime desc;";
	$result = $page->execute($sql,3);
	$result = $result['ret']['data'][0]['contents'];
	$lang = loadLanguage();
	if(isset($lang[$result])){
		$result = $lang[$result];
	}
//	else{
//		$result = json_decode(trim($result) ,true);
//	}
	echo $result;
	exit();
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
<?php
!defined('IN_ADMIN') && exit('Access Denied');
$showData = false;
$type = $_REQUEST['action'];
if($_REQUEST['username'])
	$username = $_REQUEST['username'];
if($_REQUEST['useruid'])
	$useruid = $_REQUEST['useruid'];
$headLine = "玩家资源";
$headAlert = "";
$dbArray = array(
	'uid' => array('name'=>'uid'),
	'stone' => array('name'=>'秘银',),
	'wood' => array('name'=>'木材',),
	'iron' => array('name'=>'铁矿',),
	'food' => array('name'=>'粮食',),
	'silver' => array('name'=>'钢材',),
	'chip' => array('name'=>'筹码',),
	'diamond' => array('name'=>'金筹码',),
	'lastUpdateTime' => array('name'=>'刷新时间'),
);
if($type){
// 	if($username)
// 		$sql = "select * from user_resource c inner join userprofile u on c.uid = u.uid and u.name = '{$username}'";
// 	else
// 		$sql = "select * from user_resource where uid = '{$useruid}'";
	
	if($username){
		$account_list = cobar_getValidAccountList('name', $username);
		$uid = $account_list[0]['gameUid'];
		$sql = "select * from user_resource where uid = '{$uid}'";
	}else{
		$sql = "select * from user_resource where uid = '{$useruid}'";
	}
	
	$result = $page->execute($sql,3);
	if(!$result['error'] && $result['ret']['data']){
		$item = $result['ret']['data'][0];
		$item['lastUpdateTime'] = date('Y-m-d H:i:s',$item['lastUpdateTime']/1000);
		$showData = true;
	}else{
		$error_msg = search($result);
	}
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
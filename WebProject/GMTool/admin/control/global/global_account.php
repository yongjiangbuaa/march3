<?php
!defined('IN_ADMIN') && exit('Access Denied');
$showData = false;
$type = $_REQUEST['action'];
if($_REQUEST['username'])
	$username = $_REQUEST['username'];
if($_REQUEST['useruid'])
	$useruid = $_REQUEST['useruid'];
if($_REQUEST['facebook'])
	$facebook = $_REQUEST['facebook'];
if($_REQUEST['google'])
	$google = $_REQUEST['google'];
if($_REQUEST['gaid'])
	$gaid = $_REQUEST['gaid'];
// $headLine = "";
// $headAlert = "";
if ($type) {
	if($username){
		$account_list = cobar_getAllAccountList('name', addslashes($username));
	}elseif($useruid){
		$account_list = cobar_getAccountInfoByGameuids($useruid);
	}elseif($facebook){
		$account_list = cobar_getValidAccountList('facebook', $facebook);
		if (empty($account_list)) {
			$account_list = cobar_getValidAccountList('device', $facebook);
		}
	}elseif($google){
		$account_list = cobar_getValidAccountList('google', $google);
	}elseif($gaid){
		$account_list = cobar_getValidAccountList('gaid', $gaid);
	}
	if (in_array($_COOKIE['u'],$privilegeArr)){
		print_r($account_list);
	}
    $item = $account_list[0];
    $item['gameUserName']=str_replace(' ', '&nbsp;', $item['gameUserName']);
    //print_r($item);
	$showData = true;
	$username=str_replace("'", "&#39;", $username);
}
if($_REQUEST['refresh']){
	$uid=$_REQUEST['uid'];
	$oldName=$_REQUEST['oldname'];
	$server='s'.$_REQUEST['server'];
	$host = gethostbyname(gethostname());
	if ($host == 'IPIPIP') {
		$server = 'localhost';
	}elseif ($host == 'IPIPIP'){
		$server = 'test';
	}
	$sql="select name, level from userprofile where uid='$uid';";
	$result=$page->executeServer($server, $sql, 3);
	if(!$result['error'] && $result['ret']['data']){
		$userName=$result['ret']['data'][0]['name'];
		$userLevel=intval($result['ret']['data'][0]['level']);
	}
	if (empty($userName)) {
		exit("ERROR: 没有找到用户名!");;
	}
	
	$sql="update account_new set gameUserName='$userName',gameUserLevel=$userLevel where gameUid='$uid';";
// 	$page->globalExecute($sql, 2);
	cobar_query_global_db_cobar($sql);
	cobar_changeUserName($uid, $oldName, $userName);
	
	adminLogUser($adminid,$uid,$server,array('cokdb_global'=>'account_new','gameUserName'=>$userName,'gameUserLevel'=>$userLevel));
	exit("数据更新成功!");
}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>
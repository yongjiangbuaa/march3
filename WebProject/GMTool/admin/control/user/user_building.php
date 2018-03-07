<?php
!defined('IN_ADMIN') && exit('Access Denied');
$type = $_REQUEST['type'];
if($_REQUEST['username'])
	$username = $_REQUEST['username'];
if($_REQUEST['useruid'])
	$useruid = $_REQUEST['useruid'];
if($type){
	//查看building
// 	if($username)
// 		$sql = "select b.* from user_building b inner join userprofile u on b.uid = u.uid and u.name = '{$username}'";	
// 	else
// 		$sql = "select * from user_building where uid = '{$useruid}'";

	if($username){
		$account_list = cobar_getValidAccountList('name', $username);
		$uid = $account_list[0]['gameUid'];
		$sql = "select * from user_building where uid = '{$uid}'";
	}else{
		$sql = "select * from user_building where uid = '{$useruid}'";
	}

	$sql .= " order by pos asc";
	$result = $page->execute($sql,3);
	if(!$result['error'] && $result['ret']['data']){
		$lang = loadLanguage();
		$enlang = loadLanguage('en');
		$clientXml = loadXml('building','building');
		$buildings = $result['ret']['data'];
		foreach ($buildings as $key => $building) {
			$buildings[$key]['enname'] = $enlang[(String)$clientXml[$building['itemId']]['name']];
			$buildings[$key]['name'] = $lang[(String)$clientXml[$building['itemId']]['name']];
		}
	}else{
		$error_msg = search($result);
	}
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
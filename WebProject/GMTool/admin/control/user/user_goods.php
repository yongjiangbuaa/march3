<?php
!defined('IN_ADMIN') && exit('Access Denied');
$type = $_REQUEST['type'];
if($_REQUEST['username'])
	$username = $_REQUEST['username'];
if($_REQUEST['useruid'])
	$useruid = $_REQUEST['useruid'];
//固定框架，单主键表，需修改插入部分
$db = 'user_item';
$dbArray = array(
	'itemId' => array('name'=>'ID',),
	'name' => array('name'=>'名称',),
	'enname' => array('name'=>'英文名称',),
	'count' => array('name'=>'数量',),
// 	'pos' => array('name'=>'位置',),
);
if($type){
	//查看
// 	if($username)
// 		$sql = "select b.* from $db b inner join userprofile u on b.ownerId = u.uid and u.name = '{$username}'";	
// 	else
// 		$sql = "select * from $db where ownerId = '{$useruid}'";
	
	if($username){
		$account_list = cobar_getValidAccountList('name', $username);
		$uid = $account_list[0]['gameUid'];
		$sql = "select * from $db where count>0 and ownerId = '{$uid}'";
	}else{
		$sql = "select * from $db where count>0 and ownerId = '{$useruid}'";
	}
	
	$sql .= " order by itemId asc";
	$result = $page->execute($sql,3);
	if(!$result['error'] && $result['ret']['data']){
		$lang = loadLanguage();
		$enlang = loadLanguage('en');
		$clientXml = loadXml('goods','goods');
		$items = $result['ret']['data'];
		foreach ($items as $key => $item) {
			$items[$key]['enname'] = $enlang[(int)$clientXml[$item['itemId']]['name']];
			$items[$key]['name'] = $lang[(int)$clientXml[$item['itemId']]['name']];
		}
	}else{
		$error_msg = search($result);
		$items = array();
	}
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
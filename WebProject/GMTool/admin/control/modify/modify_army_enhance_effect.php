<?php
!defined('IN_ADMIN') && exit('Access Denied');
//固定框架，双主键修改，不可添加删除单行数据
$title = "各兵种强化技能等级";
$alert = "请慎重修改此页数据";
$type = $_REQUEST['type'];
if($_REQUEST['username'])
	$username = $_REQUEST['username'];
if($_REQUEST['useruid'])
	$useruid = $_REQUEST['useruid'];
$db = 'army_enhance_effect';
$dbArray = array(
	'item_id' => array('name'=>'技能ID',),
	'level' => array('name'=>'等级','editable'=>1,),
	'army_type' => array('name'=>'兵种类型'),
	'effect_type' => array('name'=>'效果类型'),
);
if($type) {
	//修改
	if ($type == 3) {
		$sql = "update $db set {$_REQUEST['vid']} = '{$_REQUEST['num']}' where uid = '{$_REQUEST['mUid']}' and item_id = '{$_REQUEST['mitemId']}'";
		$page->execute($sql);

		adminLogUser($adminid, $_REQUEST['mUid'], $currentServer, array($_REQUEST['vid'] => $_REQUEST['num'], 'item_id' => $_REQUEST['mitemId']));
	}
	//查看
// 	if($username)
// 		$sql = "select b.* from $db b inner join userprofile u on b.uid = u.uid and u.name = '{$username}'";	
// 	else
// 		$sql = "select * from $db where uid = '{$useruid}'";

	if ($username) {
		$account_list = cobar_getValidAccountList('name', $username);
		$uid = $account_list[0]['gameUid'];
		$sql = "select * from $db where uid = '$uid' ";
	} else {
		$sql = "select * from $db where uid = '{$useruid}'";
	}

	$result = $page->execute($sql, 3);
	$items = array();
	if (!$result['error'] && $result['ret']['data']) {
		$lang = loadLanguage();
		$ret = $result['ret']['data'];
		foreach ($ret as $key => $item) {
			$itemId = $item['item_id'];
			$items[$itemId]['uid'] = $item['uid'];
			$items[$itemId]['item_id'] = $item['item_id'];
			$items[$itemId]['level'] = $item['level'];
			$items[$itemId]['army_type'] = $item['army_type'];
			$items[$itemId]['effect_type'] = $item['effect_type'];
		}
	} else {
		$error_msg = search($result);
		$items = array();
	}
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
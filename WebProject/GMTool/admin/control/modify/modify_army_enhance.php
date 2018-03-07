<?php
!defined('IN_ADMIN') && exit('Access Denied');
//固定框架，双主键修改，不可添加删除单行数据
$title = "各兵种强化等级";
$alert = "请慎重修改此页数据";
$type = $_REQUEST['type'];
if($_REQUEST['username'])
	$username = $_REQUEST['username'];
if($_REQUEST['useruid'])
	$useruid = $_REQUEST['useruid'];
$db = 'army_enhance';
$dbArray = array(
	'army_type' => array('name'=>'兵种类型',),
	'level' => array('name'=>'等级','editable'=>1,),
	'exp' => array('name'=>'经验','editable'=>1,),
	// 'desc' => array('name'=>'描述',),
);

if($type){
	//修改
	if($type == 3)
	{
		$sql = "update $db set {$_REQUEST['vid']} = '{$_REQUEST['num']}' where uid = '{$_REQUEST['mUid']}' and army_type = '{$_REQUEST['mArmyType']}'";
		$page->execute($sql);

        adminLogUser($adminid,$_REQUEST['mUid'],$currentServer,array($_REQUEST['vid']=>$_REQUEST['num'],'army_type'=>$_REQUEST['mArmyType']));
	}
	//查看
// 	if($username)
// 		$sql = "select b.* from $db b inner join userprofile u on b.uid = u.uid and u.name = '{$username}'";	
// 	else
// 		$sql = "select * from $db where uid = '{$useruid}'";
	
	if($username){
		$account_list = cobar_getValidAccountList('name', $username);
		$uid = $account_list[0]['gameUid'];
		$sql = "select * from $db where uid = '$uid' ";
	}else{
		$sql = "select * from $db where uid = '{$useruid}'";
	}
	
	$result = $page->execute($sql,3);
	$items = array();
	if(!$result['error'] && $result['ret']['data']){
		$lang = loadLanguage();
		$ret = $result['ret']['data'];
		foreach ($ret as $key => $item) {
			$army_type=$item['army_type'];
			$items[$army_type]['uid'] = $item['uid'];
			$items[$army_type]['army_type'] = $item['army_type'];
			$items[$army_type]['level'] = $item['level'];
			$items[$army_type]['exp'] = $item['exp'];
		}
	}else{
		$error_msg = search($result);
		$items = array();
	}
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
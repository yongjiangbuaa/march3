<?php
!defined('IN_ADMIN') && exit('Access Denied');
//固定框架，双主键修改
$title = "科技数据";
$alert = "";
$type = $_REQUEST['type'];
if($_REQUEST['username'])
	$username = $_REQUEST['username'];
if($_REQUEST['useruid'])
	$useruid = $_REQUEST['useruid'];
$db = 'user_science';
$dbArray = array(
	'itemId' => array('name'=>'ID',),
	'name' => array('name'=>'名称',),
	'level' => array('name'=>'等级','editable'=>1,),
	'desc' => array('name'=>'描述',),
);
if($type){
	//修改
	if($type == 3)
	{
		$sql = "update $db set {$_REQUEST['vid']} = '{$_REQUEST['num']}' where uid = '{$_REQUEST['mUid']}' and itemId = '{$_REQUEST['mArmyId']}'";
		$page->execute($sql);

        adminLogUser($adminid,$_REQUEST['mUid'],$currentServer,array($_REQUEST['vid']=>$_REQUEST['num'],'science_itemId'=>$_REQUEST['mArmyId']));
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
	
	$sql .= " order by itemId asc";
	$result = $page->execute($sql,3);
	if(!$result['error'] && $result['ret']['data']){
		$lang = loadLanguage();
		$clintXml = loadXml('science','science');
		$items = $result['ret']['data'];
		foreach ($items as $key => $item) {
			$items[$key]['name'] = $lang[(int)$clintXml[$item['itemId']]['name']];
			$items[$key]['desc'] = $lang[(int)$clintXml[$item['itemId']]['description']];
		}
	}else{
		$error_msg = search($result);
		$items = array();
	}
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>

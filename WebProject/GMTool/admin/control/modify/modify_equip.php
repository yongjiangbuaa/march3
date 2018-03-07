<?php
!defined('IN_ADMIN') && exit('Access Denied');
$type = $_REQUEST['type'];
if($_REQUEST['username'])
	$username = $_REQUEST['username'];
if($_REQUEST['useruid'])
	$useruid = $_REQUEST['useruid'];
if($_REQUEST['clear'])
	$clear = 'yes';
//固定框架，单主键表，需修改插入部分
$db = 'user_equip';
$dbArray = array(
	'itemId' => array('name'=>'ID',),
	'name' => array('name'=>'名称',),
	'enname' => array('name'=>'英文名称',),
// 	'count' => array('name'=>'数量','editable'=>1,),
// 	'pos' => array('name'=>'位置',),
	'on' => array('name'=>'状态'),
	'type' => array('name'=>'类型'),
);
if(PRODUCT_SEVER_TYPE ===0 ){
	$clearall= '<input class="btn js-btn btn-primary" type="submit" value="一键清空(删除所有背包物品)" name="clear" style="color:#FF0000;"/>';
}

if($type){
	
	if($username){
		$account_list = cobar_getValidAccountList('name', $username);
		$useruid=$account_list[0]['gameUid'];
	}
	
	//添加
//	if($type == 2)
//	{
//		$uuid = getGuid();
//// 		if($username)
//// 		{
//// 			$sql = "select uid from userprofile where name = '{$username}'";
//// 			$tmp = $page->execute($sql);
//// 			$useruid = $tmp['ret']['data'][0]['uid'];
//// 		}
//		if($useruid){
//			$sql = "INSERT INTO `$db` (`uuid`, `uid`, `on`, `itemId`, `cost`) VALUES ('$uuid', '$useruid', '0', '{$_REQUEST['itemId']}', '')";
//			$leftNum = $_REQUEST['count'] - 1;
//			if ($leftNum) {
//				while ($leftNum > 0) {
//					$uuid = getGuid();
//					$sql .= ", ('$uuid', '$useruid', '0', '{$_REQUEST['itemId']}', '')";
//					$leftNum--;
//				}
//			}
//			$page->execute($sql);
//		}
//	}
	//修改
	if($type == 3)
	{
		$sql = "update $db set {$_REQUEST['vid']} = '{$_REQUEST['num']}' where uuid = '{$_REQUEST['uuid']}'";
		$page->execute($sql);
	}
	//删除
	if($type == 5)
	{
		$sql = "delete from $db where uuid = '{$_REQUEST['uuid']}'";
		$page->execute($sql);
		$sql = "delete from endless_equip where uuid = '{$_REQUEST['uuid']}'";
		$page->execute($sql);
	}
	if($clear == 'yes'){
		$sql = "delete from $db where uid = '{$useruid}'";
		$page->execute($sql);

		$loguser  = !empty($useruid)?$useruid:$username;
		adminLogUser($adminid,$loguser,$currentServer,array($k=>array('userid'=>$useruid),'option'=>'clear all equip'));
	}
	//查看
// 	if($username)
// 		$sql = "select b.* from $db b inner join userprofile u on b.uid = u.uid and u.name = '{$username}'";	
// 	else	
// 		$sql = "select * from $db where uid = '{$useruid}'";

	$sql = "select * from $db where uid = '{$useruid}'";

	$sql .= " order by itemId asc";
	$result = $page->execute($sql,3, true);
	if(!$result['error'] && $result['ret']['data']){
		$lang = loadLanguage();
		$enlang = loadLanguage('en');
		$clientXml = loadXml('equipment_new','equipment_new');
		$items = $result['ret']['data'];
		$statusName = array('储物箱中', '已装备', '锻造中');
		$equipType = array('领主装备', '大兵装备');
		foreach ($items as $key => $item) {
			$items[$key]['enname'] = $enlang[(int)$clientXml[$item['itemId']]['name']];
			$items[$key]['name'] = $lang[(int)$clientXml[$item['itemId']]['name']];
			$items[$key]['on'] = $statusName[$item['on']];
			$items[$key]['type'] = $equipType[$item['type']];
		}
	}else{
		$error_msg = search($result);
		$items = array();
	}
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
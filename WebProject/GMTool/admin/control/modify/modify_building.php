<?php
!defined('IN_ADMIN') && exit('Access Denied');
$type = $_REQUEST['type'];
if($_REQUEST['username'])
	$username = $_REQUEST['username'];
if($_REQUEST['useruid'])
	$useruid = $_REQUEST['useruid'];
//固定框架，单主键表，需修改插入部分
$db = 'user_building';
$dbArray = array(
	'uuid' => array('name'=>'建筑UID',),
	'itemId' => array('name'=>'ID',),
	'name' => array('name'=>'名称',),
	'enname' => array('name'=>'英文名称',),
	'level' => array('name'=>'等级','editable'=>1,),
	'pos' => array('name'=>'位置',),
);
$typelanguage = array(
    2=>'add',3=>'edit',5=>'delete',
);
if($type){
    $k = 'building_'.$typelanguage[$type];
    
    if($username){
	    	$account_list = cobar_getValidAccountList('name', $username);
	    	$useruid=$account_list[0]['gameUid'];
    }
    
	//添加building
	if($type == 2)
	{
		$uuid = getGuid();
// 		if($username)
// 		{
// 			$sql = "select uid from userprofile where name = '{$username}'";
// 			$tmp = $page->execute($sql);
// 			$uid = $tmp['ret']['data'][0]['uid'];
// 			if($uid){
// 				$sql = "insert into $db (uuid, uid, itemId, pos, level) values ('{$uuid}', '{$uid}', '{$_REQUEST['itemId']}', '{$_REQUEST['pos']}', '{$_REQUEST['level']}')";
//                 $useruid = $uid;
//             }
// 		}
// 		else
// 			$sql = "insert into $db (uuid, uid, itemId, pos, level) values ('{$uuid}', '{$useruid}', '{$_REQUEST['itemId']}', '{$_REQUEST['pos']}', '{$_REQUEST['level']}')";
		
		$sql = "insert into $db (uuid, uid, itemId, pos, level) values ('{$uuid}', '{$useruid}', '{$_REQUEST['itemId']}', '{$_REQUEST['pos']}', '{$_REQUEST['level']}')";
		
		$page->execute($sql);


        adminLogUser($adminid,$useruid,$currentServer,array($k=>array('uuid'=>$uuid,'itemId'=>$_REQUEST['itemId'],'pos'=>$_REQUEST['pos'],'level'=>$_REQUEST['level'])));
	}
	//修改building
	if($type == 3)
	{
		$sql = "update $db set {$_REQUEST['vid']} = '{$_REQUEST['num']}' where uuid = '{$_REQUEST['uuid']}'";
		$page->execute($sql);

        $loguser  = !empty($useruid)?$useruid:$username;
        adminLogUser($adminid,$loguser,$currentServer,array($k=>array('uuid'=>$_REQUEST['uuid'],$_REQUEST['vid']=>$_REQUEST['num'])));
	}
	//删除building
	if($type == 5)
	{
		$sql = "delete from $db where uuid = '{$_REQUEST['uuid']}'";
		$page->execute($sql);

        $loguser  = !empty($useruid)?$useruid:$username;
        adminLogUser($adminid,$loguser,$currentServer,array($k=>array('uuid'=>$_REQUEST['uuid'])));
	}

	//查看building
// 	if($username)
// 		$sql = "select b.* from $db b inner join userprofile u on b.uid = u.uid and u.name = '{$username}'";	
// 	else
// 		$sql = "select * from $db where uid = '{$useruid}'";

	$sql = "select * from $db where uid = '{$useruid}'";

	$sql .= " order by pos asc";
	$result = $page->execute($sql,3);
	if(!$result['error'] && $result['ret']['data']){
		$lang = loadLanguage();
		$enlang = loadLanguage('en');
		$clientXml = loadXml('building','building');
		$items = $result['ret']['data'];
		foreach ($items as $key => $building) {
			$items[$key]['enname'] = $enlang[(String)$clientXml[$building['itemId']]['name']];
			$items[$key]['name'] = $lang[(String)$clientXml[$building['itemId']]['name']];
		}
	}else{
		$error_msg = search($result);
		$items = array();
	}
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
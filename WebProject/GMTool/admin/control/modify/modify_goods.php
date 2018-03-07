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
$db = 'user_item';
$dbArray = array(
	'itemId' => array('name'=>'ID',),
	'name' => array('name'=>'名称',),
	'enname' => array('name'=>'英文名称',),
	'count' => array('name'=>'数量','editable'=>1,),
// 	'pos' => array('name'=>'位置',),
);
$typelanguage = array(
    2=>'add',3=>'edit',5=>'delete',
);

if(PRODUCT_SEVER_TYPE ===0 ){
	$clearall= '<input class="btn js-btn btn-primary" type="submit" value="一键清空(删除所有背包物品)" name="clear" style="color:#FF0000;"/>';
}

if($type){
    $k = 'item_'.$typelanguage[$type];
    
    if($username){
	    	$account_list = cobar_getValidAccountList('name', $username);
	    	$useruid=$account_list[0]['gameUid'];
    }
    
	//添加
	if($type == 2)
	{
		$uuid = getGuid();
// 		if($username)
// 		{
// 			$sql = "select uid from userprofile where name = '{$username}'";
// 			$tmp = $page->execute($sql);
// 			$useruid = $tmp['ret']['data'][0]['uid'];
// 		}
		if($_REQUEST['itemId']) {
			$itemId = trim($_REQUEST['itemId']);
		}
		if($useruid){
			$sql = "select * from $db where ownerId = '$useruid' and itemId = {$itemId}";
			$tmpItems = $page->execute($sql);
			if($tmpItems['ret']['data'][0]['uuid'])
				$sql = "update $db set count = count + {$_REQUEST['count']} where ownerId = '$useruid' and itemId = {$itemId}";
			else
				$sql = "insert into $db (uuid, ownerId, itemId, count, value) values ('{$uuid}', '{$useruid}', '{$itemId}', '{$_REQUEST['count']}', '0')";
			$page->execute($sql);

            adminLogUser($adminid,$useruid,$currentServer,array($k=>array('itemId'=>$itemId,'count'=>$_REQUEST['count'])));
		}
	}
	//修改
	if($type == 3)
	{
		$sql = "update $db set {$_REQUEST['vid']} = '{$_REQUEST['num']}' where uuid = '{$_REQUEST['uuid']}'";
		$page->execute($sql);

        $loguser  = !empty($useruid)?$useruid:$username;
        adminLogUser($adminid,$loguser,$currentServer,array($k=>array($_REQUEST['vid']=>$_REQUEST['num'],'uuid'=>$_REQUEST['uuid'])));

    }
	//删除
	if($type == 5)
	{
		$sql = "delete from $db where uuid = '{$_REQUEST['uuid']}'";
		$page->execute($sql);

        $loguser  = !empty($useruid)?$useruid:$username;
        adminLogUser($adminid,$loguser,$currentServer,array($k=>array('uuid'=>$_REQUEST['uuid'])));
	}
	if($clear == 'yes'){
		$sql = "delete from $db where ownerId = '{$useruid}'";
		$page->execute($sql);

		$loguser  = !empty($useruid)?$useruid:$username;
		adminLogUser($adminid,$loguser,$currentServer,array($k=>array('userid'=>$useruid)));
	}
	//查看
// 	if($username)
// 		$sql = "select b.* from $db b inner join userprofile u on b.ownerId = u.uid and u.name = '{$username}'";	
// 	else
// 		$sql = "select * from $db where ownerId = '{$useruid}'";

	$sql = "select * from $db where count>0 and ownerId = '{$useruid}'";

	$sql .= " order by itemId asc";
	$result = $page->execute($sql,3, true);
	if(!$result['error'] && $result['ret']['data']){
		$lang = loadLanguage();
		$enlang = loadLanguage('en');
		$clientXml = loadXml('goods','goods');
		$items = $result['ret']['data'];
		foreach ($items as $key => $item) {//$key 是0 ,1,2,3
			$items[$key]['enname'] = $enlang[(int)$clientXml[$item['itemId']]['name']];
			$items[$key]['name'] = $lang[(int)$clientXml[$item['itemId']]['name']];

			$num =  (int)($clientXml[$item['itemId']]['para']);
			$items[$key]['enname'] = str_replace('{0}',$num,$items[$key]['enname']);
			$items[$key]['name'] = str_replace('{0}',$num,$items[$key]['name']);
		}
	}else{
		$error_msg = search($result);
		$items = array();
	}
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
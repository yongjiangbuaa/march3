<?php
!defined('IN_ADMIN') && exit('Access Denied');
$type = $_REQUEST['type'];
if($_REQUEST['username'])
	$username = $_REQUEST['username'];
if($_REQUEST['useruid'])
	$useruid = $_REQUEST['useruid'];

//固定框架，单主键表，需修改插入部分
$db = 'user_dragon_pro';
$dbArray = array(
	'uuid' => array('name'=>'ID'),
	'uid' => array('name'=>'uid'),
	'dragonId' => array('name'=>'龙id'),
	'propertyid' => array('name'=>'属性id'),
	'level' => array('name'=>'等级','editable'=>0),
	'exp' => array('name'=>'经验值','editable'=>0),
);
$typelanguage = array(
    2=>'add',3=>'edit',5=>'delete',
);


if($type){
    $k = 'dragonpro_'.$typelanguage[$type];
    
    if($username){
		$account_list = cobar_getValidAccountList('name', $username);
		$useruid=$account_list[0]['gameUid'];
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
	//查看
// 	if($username)
// 		$sql = "select b.* from $db b inner join userprofile u on b.ownerId = u.uid and u.name = '{$username}'";	
// 	else
// 		$sql = "select * from $db where ownerId = '{$useruid}'";

	$sql = "select * from $db where uid = '{$useruid}'";

	$sql .= "order by dragonid desc,propertyid desc";
	$result = $page->execute($sql,3, true);
	if(!$result['error'] && $result['ret']['data']){
//		$lang = loadLanguage();
//		$enlang = loadLanguage('en');
//		$clientXml = loadXml('goods','goods');
		$items = $result['ret']['data'];
//		foreach ($items as $key => $item) {//$key 是0 ,1,2,3
//			$items[$key]['enname'] = $enlang[(int)$clientXml[$item['itemId']]['name']];
//			$items[$key]['name'] = $lang[(int)$clientXml[$item['itemId']]['name']];

//			$num =  (int)($clientXml[$item['itemId']]['para']);
//			$items[$key]['enname'] = str_replace('{0}',$num,$items[$key]['enname']);
//			$items[$key]['name'] = str_replace('{0}',$num,$items[$key]['name']);
//		}
	}else{
		$error_msg = search($result);
		$items = array();
	}
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
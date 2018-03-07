<?php
!defined('IN_ADMIN') && exit('Access Denied');
$type = $_REQUEST['type'];
if($_REQUEST['username'])
	$username = $_REQUEST['username'];
if($_REQUEST['useruid'])
	$useruid = $_REQUEST['useruid'];
//固定框架，单主键表，需修改插入部分
$db = 'user_hero';
$dbArray = array(
	'uuid' => array('name'=>'uuid',),
	'hero_id' => array('name'=>'英雄ID',),
	'level' => array('name'=>'等级','editable'=>1),
	'star' => array('name'=>'星级','editable'=>1),
	'exp' => array('name'=>'经验值','editable'=>1),
 	'status' => array('name'=>'状态(0-2)','editable'=>1,),
 	'base' => array('name'=>'基础值类型(0-4)',),
	'growth' => array('name'=>'成长值类型(0-4)'),
	'create_time' => array('name'=>'释放时间'),
	'next_train_available_time' => array('name'=>'驻守开始时间','editable'=>1),
	'locked' => array('name'=>'英雄锁定标示','editable'=>1),
);
$typelanguage = array(
	2=>'add',3=>'edit',5=>'delete',
);
if($type){
	$k = 'hero_'.$typelanguage[$type];

	if($username){
		$account_list = cobar_getValidAccountList('name', $username);
		$useruid=$account_list[0]['gameUid'];
	}
	
	//添加
	if($type == 2)
	{
		$uuid = getGuid();
		$createtime = time()*1000;
		if($useruid) {
			$sql = "INSERT INTO `$db` (`uuid`, `uid`, `hero_id`, `level`,`status`, `base`,`growth`,`create_time`,`next_train_available_time`)
 VALUES ('$uuid', '$useruid', '{$_REQUEST['heroid']}',1,'0', '0','0',$createtime,0)";

			$page->execute($sql);
			adminLogUser($adminid, $useruid, $currentServer, array($k => array('uuid' => $uuid)));
		}


	}
	//修改
	if($type == 3)
	{
		$sql = "update $db set {$_REQUEST['vid']} = '{$_REQUEST['num']}' where uuid = '{$_REQUEST['uuid']}'";
		$page->execute($sql);
		adminLogUser($adminid,$useruid,$currentServer,array($k=>array('uuid'=>$_REQUEST['uuid'],'name'=>$_REQUEST['vid'],'value'=>$_REQUEST['num'])));

	}
	//删除
	if($type == 5)
	{
		$sql = "delete from $db where uuid = '{$_REQUEST['uuid']}'";
		$page->execute($sql);
		adminLogUser($adminid, $useruid, $currentServer, array($k => array('uuid' => $_REQUEST['uuid'])));
	}

	//查看
// 	if($username)
// 		$sql = "select b.* from $db b inner join userprofile u on b.uid = u.uid and u.name = '{$username}'";	
// 	else	
// 		$sql = "select * from $db where uid = '{$useruid}'";

	$sql = "select * from $db where uid = '{$useruid}'";

	$sql .= " order by hero_id asc";
	$result = $page->execute($sql,3, true);
	if(!$result['error'] && $result['ret']['data']){
		$lang = loadLanguage();
		$enlang = loadLanguage('en');
		$clientXml = loadXml('hero','hero');
		$items = $result['ret']['data'];
		$statusName = array('空闲0', '出征1', '驻守2');
		foreach ($items as $key => $item) {
			$items[$key]['enname'] = $enlang[(int)$clientXml[$item['hero_id']]['name']];
			$items[$key]['name'] = $lang[(int)$clientXml[$item['hero_id']]['name']];
			$items[$key]['status'] = $statusName[$item['status']];
		}
	}else{
		$error_msg = search($result);
		$items = array();
	}

}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
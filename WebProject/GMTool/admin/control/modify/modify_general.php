<?php
!defined('IN_ADMIN') && exit('Access Denied');
$type = $_REQUEST['type'];
if($_REQUEST['username'])
	$username = $_REQUEST['username'];
if($_REQUEST['useruid'])
	$useruid = $_REQUEST['useruid'];
//固定框架，单主键表，需修改插入部分
$db = 'user_general';
$dbArray = array(
	'generalId' => array('name'=>'ID',),
	'name' => array('name'=>'名称',),
	'level' => array('name'=>'等级','editable'=>1,),
	'exp' => array('name'=>'经验','editable'=>1,),
	'time' => array('name'=>'招募时间',),
);
if($type){
	//添加general
	if($type == 2)
	{
		$currTime = microtime(true) * 1000;
		$uuid = getGUID();
		$ability = '[]';
		$generalXml = loadXml('general');
		foreach ($generalXml as $generalItem) {
			if($generalItem['id'] == $_REQUEST['generalId'] && $generalItem['ability'] != null){
				$ability = "[{\"id\":\"{$generalItem['ability']}\"}]";//[{"id":"50026"}]
				break;
			}
		}
		if($username)
		{
			$sql = "select uid from userprofile where name = '{$username}'";
			$tmp = $page->execute($sql,3);
			$uid = $tmp['ret']['data'][0]['uid'];
			if($uid)
				$sql = "insert into $db (uuid, uid, generalId, color, status, level, exp, time, ability) "
					."values ('{$uuid}', '{$uid}', '{$_REQUEST['generalId']}', '0', '0', '1', '0', '{$currTime}', '{$ability}')";
		}
		else 
			$sql = "insert into $db (uuid, uid, generalId, color, status, level, exp, time, ability) "
					."values ('{$uuid}', '{$useruid}', '{$_REQUEST['generalId']}', '0', '0', '1', '0', '{$currTime}', '{$ability}')";
		$page->execute($sql);
	}
	//修改general信息
	if($type == 3)
	{
		if($username)
		{
			$temp = "g." . $_REQUEST['vid'];
			$sql = "update $db g inner join userprofile u on u.uid = g.uid and u.name = '{$username}' set {$temp} = '{$_REQUEST['num']}' where uuid = '{$_REQUEST['generalId']}'";
		}
		else 
			$sql = "update $db set {$_REQUEST['vid']} = '{$_REQUEST['num']}' where uid = '{$useruid}' and uuid = '{$_REQUEST['generalId']}'";
		$page->execute($sql);
	}
	//删除general
	if($type == 5)
	{
		$sql = "delete from $db where uuid = '{$_REQUEST['guid']}'";
		$page->execute($sql);
	}
	//查看general信息
	if($username)
		$sql = "select g.* from $db g inner join userprofile u on u.uid = g.uid and u.name = '{$username}'";
	else 
		$sql = "select * from $db where uid = '{$useruid}'";
	$result = $page->execute($sql,3);
	if(!$result['error'] && $result['ret']['data']){
		$result = $result['ret']['data'];
		$lang = loadLanguage();
		$clientXml = loadXml('general','general');
		$items = $result;
		foreach ($items as $key => $value) {
			$items[$key]['time'] = date('Y-m-d H:i:s',$value['time']/1000);
			$items[$key]['name'] = $lang[(int)$clientXml[$value['generalId']]['name']];
		}
	}else{
		$error_msg = search($result);
		$items = array();
	}
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
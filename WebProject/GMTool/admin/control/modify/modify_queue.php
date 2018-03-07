<?php
!defined('IN_ADMIN') && exit('Access Denied');
$type = $_REQUEST['type'];
if($_REQUEST['username'])
	$username = $_REQUEST['username'];
if($_REQUEST['useruid'])
	$useruid = $_REQUEST['useruid'];
if($type){
	//添加
	if($type == 2)
	{
		if($username)
		{
			$sql = "select uid from userprofile where name = '{$username}'";
			$tmp = $page->execute($sql,3);
			$uid = $tmp['ret']['data'][0]['uid'];
			if($uid)
				$sql = "insert into user_building (uid, itemId, type, level, pos) values ('{$uid}', '{$_REQUEST['itemId']}', '{$_REQUEST['buildingType']}', '{$_REQUEST['level']}', '{$_REQUEST['pos']}')";
		}
		else
			$sql = "insert into user_building (uid, itemId, type, level, pos) values ('{$useruid}', '{$_REQUEST['itemId']}', '{$_REQUEST['buildingType']}', '{$_REQUEST['level']}', '{$_REQUEST['pos']}')";
		$page->execute($sql);	
	}

	//修改
	if($type == 3)
	{
		if($username)
		{
			$temp = "q." . $_REQUEST['vid'];
			$sql = "update user_queue q inner join userprofile u on u.uid = q.uid and u.name = '{$username}' set {$temp} = '{$_REQUEST['num']}' where qid = '{$_REQUEST['qid']}'";
		}
		else
			$sql = "update user_queue set {$_REQUEST['vid']} = '{$_REQUEST['num']}' where uid = '{$useruid}' and qid = '{$_REQUEST['qid']}'";
		$page->execute($sql);	
	}

	//删除
	if($type == 5)
	{
		if($username)
		{
			$sql = "delete from user_queue uq inner join userprofile u on uq.uid = u.uid  where u.name = '{$username}' and itemId = '{$_REQUEST['itemId']}'";
		}
		else
			$sql = "delete from user_queue where uid = '{$useruid}' and itemId = '{$_REQUEST['itemId']}'";
		$page->execute($sql);	
	}
	if($username)
		$sql = "select q.* from user_queue q inner join userprofile u on q.uid = u.uid and u.name = '{$username}'";	
	else
		$sql = "select * from user_queue where uid = '{$useruid}'";
	$result = $page->execute($sql,3);
	if(!$result['error'] && $result['ret']['data']){
		$queue = $result['ret']['data'][0];
	}else{
		$error_msg = search($result);
	}
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
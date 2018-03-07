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
$db = 'endless_equip';
$dbArray = array(
	'uid' => array('name'=>'玩家ID',),
	'equipId' => array('name'=>'装备ID',),
	'grade'  => array('name'=>'等级','editable'=>1,),
	'exp' => array('name'=>'经验','editable'=>1,),
	'power' => array('name'=>'战力',),
);

if($type){
	
	if($username){
		$account_list = cobar_getValidAccountList('name', $username);
		$useruid=$account_list[0]['gameUid'];
	}

	//修改
	if($type == 3)
	{
		$sql = "select * from user_equip where uuid = '{$_REQUEST['uuid']}'";
		$result = $page->execute($sql,3);
		$items = $result['ret']['data'];
		$item = $items[0];
		$sql1 = "select * from endless_equip where uuid = '{$_REQUEST['uuid']}'";
		$result1 = $page->execute($sql1,3);
		$items1 = $result1['ret']['data'];
		$item1 = $items1[0];
		$clintXml = loadXml('endless_equipment','endless_equipment');
		$maxExp = (int)$clintXml[$item1['equipId']]['needexp'];
		$sql_grade = "";
		$sql_exp = "";
		if($_REQUEST['vid'] == 'grade'){
			if($item['itemId'] = '107500'){
				$equipId = 114499 + (int)$_REQUEST['num'];
				$power = (int)$clintXml[$equipId]['power'];
				$sql_grade = "update $db set {$_REQUEST['vid']} = '{$_REQUEST['num']}',equipId = $equipId, power = $power where uuid = '{$_REQUEST['uuid']}'";
			}
			if($item['itemId'] = '1075001'){
				$equipId = 117499 + (int)$_REQUEST['num'];
				$power = (int)$clintXml[$equipId]['power'];
				$sql_grade = "update $db set {$_REQUEST['vid']} = '{$_REQUEST['num']}',equipId = $equipId, power = $power where uuid = '{$_REQUEST['uuid']}'";
			}
		}
		else if($_REQUEST['vid'] == 'exp'){
			if((int)$_REQUEST['num'] >= $maxExp){
				$maxExp = $maxExp - 1;
				$sql_exp = "update $db set {$_REQUEST['vid']} = $maxExp where uuid = '{$_REQUEST['uuid']}'";
			}else{
				$sql_exp = "update $db set {$_REQUEST['vid']} = '{$_REQUEST['num']}' where uuid = '{$_REQUEST['uuid']}'";
			}
		}
		$page->execute($sql_grade,3);
		$page->execute($sql_exp,3);
	}
	//删除
	if($type == 5)
	{
		$sql = "delete from $db where uuid = '{$_REQUEST['uuid']}'";
		$page->execute($sql);
		$sql = "delete from user_equip where uuid = '{$_REQUEST['uuid']}'";
		$page->execute($sql);
	}

	$sql = "select * from $db where uid = '{$useruid}'";
	$result = $page->execute($sql,3);		
	if(!$result['error'] && $result['ret']['data']){
		$items = $result['ret']['data'];
	}else{
		$error_msg = search($result);
		$items = array();
	}
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
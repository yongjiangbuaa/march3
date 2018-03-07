<?php
!defined('IN_ADMIN') && exit('Access Denied');
//固定框架，双主键修改，不可添加删除单行数据
$title = "玩家兵种数据";
$alert = '';
$type = $_REQUEST['type'];
if($_REQUEST['username'])
	$username = $_REQUEST['username'];
if($_REQUEST['useruid'])
	$useruid = $_REQUEST['useruid'];
$db = 'user_army';
$dbArray = array(
	'armyId' => array('name'=>'兵种ID',),
	'name' => array('name'=>'兵种名称',),
	'free' => array('name'=>'空闲',),
	'pve' => array('name'=>'PVE',),
	'march' => array('name'=>'出征',),
	'defence' => array('name'=>'防守',),
	'train' => array('name'=>'训练',),
	'dead' => array('name'=>'伤病',),
	'heal' => array('name'=>'正在治疗',),
	// 'desc' => array('name'=>'描述',),
);
if($type){
	//修改
	if($type == 3)
	{
		$alert = '修改禁止';
	}
	//查看
// 	if($username)
// 		$sql = "select b.* from $db b inner join userprofile u on b.uid = u.uid and u.name = '{$username}'";	
// 	else
// 		$sql = "select * from $db where uid = '{$useruid}'";
	//区分大小写
	if($username){
		$account_list = cobar_getValidAccountList('name', $username);
		$uid = $account_list[0]['gameUid'];
		$sql = "select * from $db where uid = '{$uid}'";
		$hSql = "select uid,armyId,dead,heal	from user_hospital where uid='{$uid}' order by armyId asc;";
	}else{
		$sql = "select * from $db where uid = '{$useruid}'";
		$hSql = "select uid,armyId,dead,heal	from user_hospital where uid='{$useruid}' order by armyId asc;";
	}
	
	$sql .= " order by armyId asc";
	$result = $page->execute($sql,3);
	$items = array();
	if(!$result['error'] && $result['ret']['data']){
		$lang = loadLanguage();
		$clintXml = loadXml('arms','arms');
		$ret = $result['ret']['data'];
		foreach ($ret as $key => $item) {
			$armyId=$item['armyId'];
			$items[$armyId]['armyId'] = $item['armyId'];
			$items[$armyId]['name'] = $lang[(int)$clintXml[$item['armyId']]['name']];
			$items[$armyId]['free'] = $item['free'];
			$items[$armyId]['pve'] = $item['pve'];
			$items[$armyId]['march'] = $item['march'];
			$items[$armyId]['defence'] = $item['defence'];
			$items[$armyId]['train'] = $item['train'];
			$items[$armyId]['dead'] = 0;
			$items[$armyId]['heal'] = 0;
			
			$items[$armyId]['desc'] = $lang[(int)$clintXml[$item['armyId']]['description']];
		}
	}else{
		$error_msg = search($result);
		$items = array();
	}
	
	$hResult = $page->execute($hSql,3);
	if(!$hResult['error'] && $hResult['ret']['data']){
		$hRet = $hResult['ret']['data'];
		foreach ($hRet as $key => $item) {
			$armyId=$item['armyId'];
			$items[$armyId]['dead'] = isset($item['dead'])?$item['dead']:0;
			$items[$armyId]['heal'] = isset($item['heal'])?$item['heal']:0;
		}
	}
	
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
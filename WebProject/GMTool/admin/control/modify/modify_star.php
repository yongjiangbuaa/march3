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
$db = 'user_star_equip';
$dbArray = array(
	'uid' => array('name'=>'玩家ID',),
	'starId' => array('name'=>'星辰ID','editable'=>1,),
	'rare'  => array('name'=>'稀有度',),
	'name' => array('name'=>'星辰名称',),
	'type' => array('name'=>'星辰功能',),
	'level' => array('name'=>'星辰等级',),
	'value' => array('name'=>'属性提升',),
	'exp' => array('name'=>'星辰经验',),
	'position' => array('name'=> '装备位置',),
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
	if($type == 2)
	{
		$uuid = getGuid();
		$acquire_time = time()*1000;
		if($useruid) {
			$sql = "INSERT INTO `$db` (`uuid`, `uid`, `starId`, `exp`,`position`, `acquire_time`)
 			VALUES ('$uuid', '$useruid', '{$_REQUEST['starId']}', '0','0',$acquire_time)";
			$page->execute($sql);
			adminLogUser($adminid, $useruid, $currentServer, array($k => array('uuid' => $uuid)));
		}


	}
	//修改
	if($type == 3)
	{
		$sql = "update $db set {$_REQUEST['vid']} = '{$_REQUEST['num']}',exp = 0 where uuid = '{$_REQUEST['uuid']}'";
		$page->execute($sql);
	}
	//删除
	if($type == 5)
	{
		$sql = "delete from $db where uuid = '{$_REQUEST['uuid']}'";
		$page->execute($sql);	
	}
	if($clear == 'yes'){
		$sql = "delete from $db where uid = '{$useruid}'";
		$page->execute($sql);

		$loguser  = !empty($useruid)?$useruid:$username;
		adminLogUser($adminid,$loguser,$currentServer,array($k=>array('userid'=>$useruid),'option'=>'clear all star'));
	}
	//查看
// 	if($username)
// 		$sql = "select b.* from $db b inner join userprofile u on b.uid = u.uid and u.name = '{$username}'";	
// 	else	
// 		$sql = "select * from $db where uid = '{$useruid}'";

	$sql = "select * from $db where uid = '{$useruid}'";

	$sql .= " order by position desc";
	$result = $page->execute($sql,3);
	if(!$result['error'] && $result['ret']['data']){
		$lang = loadLanguage();
		$clientXml = loadXml('stars','stars');
		$items = $result['ret']['data'];
		$quality = array();
		foreach ($items as $key => $item) {
			$items[$key]['name'] = $lang[(int)$clientXml[$item['starId']]['name']];
			$quality[$key] = (int)$clientXml[$item['starId']]['quality'];
			if($quality[$key] == 0){
				$quality[$key] = '白色';
			}
			if($quality[$key] == 1){
				$quality[$key] = '绿色';
			}
			if($quality[$key] == 2){
				$quality[$key] = '蓝色';
			}
			if($quality[$key] == 3){
				$quality[$key] = '紫色';
			}
			if($quality[$key] == 4){
				$quality[$key] = '金色';
			}
			$items[$key]['rare'] = $quality[$key];
			$items[$key]['type'] = $lang[(int)$clientXml[$item['starId']]['effect']];
			$items[$key]['level'] = (int)$clientXml[$item['starId']]['level'];
			$items[$key]['value'] = '+'.$clientXml[$item['starId']]['value'].'%';
			if( $items[$key]['position'] == 0){
				$items[$key]['position'] = '背包';
			}
			else{
				$items[$key]['position'] = '位置'.$items[$key]['position'];
			}
		}
	}else{
		$error_msg = search($result);
		$items = array();
	}
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
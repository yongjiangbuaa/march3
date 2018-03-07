<?php
!defined('IN_ADMIN') && exit('Access Denied');
//固定框架，双主键修改，不可添加删除单行数据
$title = "巨龙数据";
$alert = "请慎重修改此页数据";
$type = $_REQUEST['type'];
if($_REQUEST['username'])
	$username = $_REQUEST['username'];
if($_REQUEST['useruid'])
	$useruid = $_REQUEST['useruid'];
$db = 'user_dragon';
$dbArray = array(
	'dragonId' => array('name'=>'巨龙ID',),
	'name' => array('name'=>'巨龙名称',),
	'level' => array('name'=>'等级','editable'=>1,),
	'exp' => array('name'=>'当前经验','editable'=>1,),
	'type' => array('name'=>'类型(0龙蛋,1龙)','editable'=>1,),
	'hatchTime' => array('name'=>'孵化结束时间(分钟)','editable'=>1,),
	'energy' => array('name'=>'活力值','editable'=>1,),
	'status' => array('name'=>'状态(0,空闲;1,驻守;2,出征)','editable'=>1,),
	'startTime' => array('name'=>'驻守开始时间','editable'=>1,),
	'updateTime' => array('name'=>'活力值更新时间','editable'=>1,),
	'feedCdTime' => array('name'=>'喂养龙的CD时间','editable'=>1,),
);

if($type){
	//修改
	if($type == 3)
	{
		if($_REQUEST['vid']!='hatchTime') {
			if($_REQUEST['vid']=='updateTime'||$_REQUEST['vid']=='feedCdTime'){
				$_REQUEST['num'] = strtotime($_REQUEST['num'])*1000;
			}
			$sql_update = "update $db set {$_REQUEST['vid']} = '{$_REQUEST['num']}' where uid = '{$_REQUEST['mUid']}' and dragonId = '{$_REQUEST['mDragonId']}'";
			$page->execute($sql_update);
		}else{
			$_REQUEST['num'] = $_REQUEST['num']*60*1000;//$_REQUEST['num']为龙孵化的分钟  转化为毫秒存入数据库
			$sql_update = "update userprofile set dragonEggDurationTime = '{$_REQUEST['num']}' where uid = '{$_REQUEST['mUid']}'";
			$page->execute($sql_update);
		}

        adminLogUser($adminid,$_REQUEST['mUid'],$currentServer,array($_REQUEST['vid']=>$_REQUEST['num'],'dragonId'=>$_REQUEST['mDragonId']));
	}


	if($username){
		$account_list = cobar_getValidAccountList('name', $username);
		$uid = $account_list[0]['gameUid'];
		$sql = "select d.*,up.dragonEggDurationTime  from $db d left join userprofile up on d.uid=up.uid where d.uid = '$uid' ";
	}else{
		$sql = "select d.*,up.dragonEggDurationTime from $db d left join userprofile up on d.uid=up.uid where d.uid = '{$useruid}'";
	}
	$sql .= " order by dragonId asc";
	$result = $page->execute($sql,3);
	$items = array();
	if(!$result['error'] && $result['ret']['data']){
		$lang = loadLanguage();
		$clintXml = loadXml('dragon_client','dragon_client');
		$ret = $result['ret']['data'];
		foreach ($ret as $key => $item) {
			$dragonId=$item['dragonId'];
			$items[$dragonId]['uid'] = $item['uid'];
			$items[$dragonId]['dragonId'] = $item['dragonId'];
			$items[$dragonId]['name'] = ($lang[(int)$clintXml[$item['dragonId']]['name']]);
			$items[$dragonId]['level'] = $item['level'];
			$items[$dragonId]['exp'] = $item['exp'];
			$items[$dragonId]['type'] = $item['type'];
			$items[$dragonId]['hatchTime'] = $item['dragonEggDurationTime']/(60*1000);
			$items[$dragonId]['energy'] = $item['energy'];
			$items[$dragonId]['status'] = $item['status'];
			$items[$dragonId]['startTime'] = $item['startTime'];
			$items[$dragonId]['updateTime'] = $item['updateTime']?date('Y-m-d H:i:s', $item['updateTime']/1000):0;
			$items[$dragonId]['feedCdTime'] = $item['feedCdTime']?date('Y-m-d H:i:s', $item['feedCdTime']/1000):0;
		}
	}else{
		$error_msg = search($result);
		$items = array();
	}
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
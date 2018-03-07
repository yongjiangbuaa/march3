<?php
!defined('IN_ADMIN') && exit('Access Denied');
//固定框架，双主键修改，不可添加删除单行数据
$title = "巨龙数据";
$alert = '';
$type = $_REQUEST['type'];
if($_REQUEST['username'])
	$username = $_REQUEST['username'];
if($_REQUEST['useruid'])
	$useruid = $_REQUEST['useruid'];
$db = 'user_dragon';
$dbArray = array(
	'dragonId' => array('name'=>'巨龙ID',),
	'name' => array('name'=>'巨龙名称',),
	'level' => array('name'=>'等级','editable'=>0,),
	'exp' => array('name'=>'当前经验','editable'=>0,),
	'type' => array('name'=>'类型(0龙蛋,1龙)','editable'=>0,),
//	'hatchTime' => array('name'=>'孵化结束时间(分钟)','editable'=>0,),
	'energy' => array('name'=>'活力值','editable'=>0),
	'status' => array('name'=>'状态(0,空闲;1,驻守;2,出征)','editable'=>0),
	'fightPower' => array('name'=>'战力值','editable'=>0),
	'evolution' => array('name'=>'觉醒等级','editable'=>0),
	'solder1'  => array('name'=>'助战位1','editable'=>0),
	'solder2'  => array('name'=>'助战位2','editable'=>0),
	'solder3'  => array('name'=>'助战位3','editable'=>0),
	'state' => array('name'=>'交配状态','editable'=>0,),
	'remainTime' => array('name'=>'剩余时间','editable'=>0,),
	'startTime' => array('name'=>'驻守开始时间','editable'=>0),
	'updateTime' => array('name'=>'活力值更新时间','editable'=>0),
	'feedCdTime' => array('name'=>'喂养龙的CD时间','editable'=>0),
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
		$sql_apply_mate = "select * from user_dragon_mate where apply_uid='$uid' and state=1";
		$sql_accept_mate = "select * from user_dragon_mate where accept_uid='$uid' and state=1";
		$sql_solder1 = "select uds.*,d.dragonId from user_dragon_solder uds left join user_dragon d on uds.main_dragon_id=d.uuid where uds.uid='$uid'and pos=61111000";
		$sql_solder2 = "select uds.*,d.dragonId from user_dragon_solder uds left join user_dragon d on uds.main_dragon_id=d.uuid where uds.uid='$uid'and pos=61112000";
		$sql_solder3 = "select uds.*,d.dragonId from user_dragon_solder uds left join user_dragon d on uds.main_dragon_id=d.uuid where uds.uid='$uid'and pos=61113000";
	}else{
		$sql = "select d.*,up.dragonEggDurationTime from $db d left join userprofile up on d.uid=up.uid where d.uid = '{$useruid}'";
		$sql_apply_mate = "select * from user_dragon_mate where apply_uid='{$useruid}' and state=1";
		$sql_accept_mate = "select * from user_dragon_mate where accept_uid='{$useruid}' and state=1";
		$sql_solder1 = "select uds.*,d.dragonId from user_dragon_solder uds left join user_dragon d on uds.main_dragon_id=d.uuid where uds.uid='{$useruid}'and pos=61111000";
		$sql_solder2 = "select uds.*,d.dragonId from user_dragon_solder uds left join user_dragon d on uds.main_dragon_id=d.uuid where uds.uid='{$useruid}'and pos=61112000";
		$sql_solder3 = "select uds.*,d.dragonId from user_dragon_solder uds left join user_dragon d on uds.main_dragon_id=d.uuid where uds.uid='{$useruid}'and pos=61113000";
	}
	$sql .= " order by dragonId asc";
	$result = $page->execute($sql,3);
	$result_apply_mate = $page->execute($sql_apply_mate,3);
	$result_accept_mate = $page->execute($sql_accept_mate,3);
	$result_solder1 = $page->execute($sql_solder1,3);
	$result_solder2 = $page->execute($sql_solder2,3);
	$result_solder3 = $page->execute($sql_solder3,3);
	$items_solder1 = array();
	if(!$result_solder1['error'] && $result_solder1['ret']['data']){
		$ret_solder1 = $result_solder1['ret']['data'];
		foreach ($ret_solder1 as $key_solder1 => $item_solder1) {
			$dragonId_solder1 = $item_solder1['dragonId'];
			$items_solder1[$dragonId_solder1]['level'] = $item_solder1['level'];
			$items_solder1[$dragonId_solder1]['aide_dragon_id'] =$item_solder1['aide_dragon_id'];
		}
	}
	$items_solder2 = array();
	if(!$result_solder2['error'] && $result_solder2['ret']['data']){
		$ret_solder2 = $result_solder2['ret']['data'];
		foreach ($ret_solder2 as $key_solder2 => $item_solder2) {
			$dragonId_solder2 = $item_solder2['dragonId'];
			$items_solder2[$dragonId_solder2]['level'] = $item_solder2['level'];
			$items_solder2[$dragonId_solder2]['aide_dragon_id'] =$item_solder2['aide_dragon_id'];
		}
	}
	$items_solder3 = array();
	if(!$result_solder3['error'] && $result_solder3['ret']['data']){
		$ret_solder3 = $result_solder3['ret']['data'];
		foreach ($ret_solder3 as $key_solder3 => $item_solder3) {
			$dragonId_solder3 =  $item_solder3['dragonId'];
			$items_solder3[$dragonId_solder3]['level'] = $item_solder3['level'];
			$items_solder3[$dragonId_solder3]['aide_dragon_id'] =$item_solder3['aide_dragon_id'];
		}
	}
	$items_apply_mate = array();
	if(!$result_apply_mate['error'] && $result_apply_mate['ret']['data']){
		$ret_apply_mate = $result_apply_mate['ret']['data'];
		foreach ($ret_apply_mate as $keym_apply_mate => $item_apply_mate) {
			$dragonId_apply_mate = $item_apply_mate['apply_dragon_id'];
			$items_apply_mate[$dragonId_apply_mate]['state'] =($item_apply_mate['mate_start_time'] + $item_apply_mate['keep_time'] - time()*1000) > 0 ? '正在交配' :(( $item_apply_mate['apply_reward'] == 1) ? '空闲' : '未领取奖励');
			$items_apply_mate[$dragonId_apply_mate]['remainTime'] = ($item_apply_mate['mate_start_time'] + $item_apply_mate['keep_time'] - time()*1000) > 0 ? $format_time = gmstrftime('%H时%M分%S秒', ($item_apply_mate['mate_start_time'] + $item_apply_mate['keep_time'] - time()*1000)/1000) : '无';
		}
	}
	$items_accept_mate = array();
	if(!$result_accept_mate['error'] && $result_accept_mate['ret']['data']){
		$ret_accept_mate = $result_accept_mate['ret']['data'];
		foreach ($ret_accept_mate as $key_accept_mate => $item_accept_mate) {
			$dragonId_accept_mate = $item_accept_mate['accept_dragon_id'];
			$items_accept_mate[$dragonId_accept_mate]['state'] =($item_accept_mate['mate_start_time'] + $item_accept_mate['keep_time'] - time()*1000) > 0 ? '正在交配' : (($item_accept_mate['accept_reward'] == 1) ? '空闲' : '未领取奖励');
			$items_accept_mate[$dragonId_accept_mate]['remainTime'] = ($item_accept_mate['mate_start_time'] + $item_accept_mate['keep_time'] - time()*1000) > 0 ? $format_time = gmstrftime('%H时%M分%S秒',($item_accept_mate['mate_start_time'] + $item_accept_mate['keep_time'] - time()*1000)/1000) : '无';
		}
	}
	$items = array();
	if(!$result['error'] && $result['ret']['data']){
		$lang = loadLanguage();
		$clintXml = loadXml('dragon_client','dragon_client');
		$ret = $result['ret']['data'];
		$items_uuid = array();
		foreach ($ret as $key_uuid => $item_uuid) {
			$dragonId_uuid = $item_uuid['dragonId'];
			$items_uuid[$dragonId_uuid] = $item_uuid['uuid'];
		}
			foreach ($ret as $key => $item) {
			$dragonId = $item['dragonId'];
			$items[$dragonId]['uid'] = $item['uid'];
			$items[$dragonId]['dragonId'] = $item['dragonId'];
			$items[$dragonId]['name'] = ($lang[(int)$clintXml[$item['dragonId']]['name']]);
			$items[$dragonId]['level'] = $item['level'];
			$items[$dragonId]['exp'] = $item['exp'];
			$items[$dragonId]['type'] = $item['type'];
//			$items[$dragonId]['hatchTime'] = $item['dragonEggDurationTime']/(60*1000);
			$items[$dragonId]['energy'] = $item['energy'];
			$items[$dragonId]['fightPower'] = $item['fightPower'];
			$items[$dragonId]['evolution'] = $item['evolution'];
			if(!empty($items_solder1[$dragonId]['level'])){
				if(!empty($items_solder1[$dragonId]['aide_dragon_id'])) {
					foreach ($items_uuid as $key_uuid1 => $item_uuid1) {
						if ($items_solder1[$dragonId]['aide_dragon_id'] == $item_uuid1) {
							$items_solder1[$dragonId]['aide_dragon_id'] = $key_uuid1;
						}
					}
					$items[$dragonId]['solder1'] = $items_solder1[$dragonId]['level'] . ';' . $items_solder1[$dragonId]['aide_dragon_id'];
				}
				else{
					$items[$dragonId]['solder1'] = $items_solder1[$dragonId]['level'].';无';
				}
			}
			else{
				$items[$dragonId]['solder1'] = '未解锁';
			}
			if(!empty($items_solder2[$dragonId]['level'])){
				if(!empty($items_solder2[$dragonId]['aide_dragon_id'])) {
					foreach ($items_uuid as $key_uuid2 => $item_uuid2) {
						if ($items_solder2[$dragonId]['aide_dragon_id'] == $item_uuid2) {
							$items_solder2[$dragonId]['aide_dragon_id'] = $key_uuid2;
						}
					}
					$items[$dragonId]['solder2'] = $items_solder2[$dragonId]['level'] . ';' . $items_solder2[$dragonId]['aide_dragon_id'];
				}
				else{
					$items[$dragonId]['solder2'] = $items_solder2[$dragonId]['level'].';无';
				}
			}
			else{
				$items[$dragonId]['solder2'] = '未解锁';
			}
			if(!empty($items_solder3[$dragonId]['level'])){
				if(!empty($items_solder3[$dragonId]['aide_dragon_id'])) {
					foreach ($items_uuid as $key_uuid3 => $item_uuid3) {
						if ($items_solder3[$dragonId]['aide_dragon_id'] == $item_uuid3) {
							$items_solder3[$dragonId]['aide_dragon_id'] = $key_uuid3;
						}
					}
					$items[$dragonId]['solder3'] = $items_solder3[$dragonId]['level'] . ';' . $items_solder3[$dragonId]['aide_dragon_id'];
				}
				else{
					$items[$dragonId]['solder3'] = $items_solder3[$dragonId]['level'].';无';
				}
			}
			else{
				$items[$dragonId]['solder3'] = '未解锁';
			}
			if(!empty($items_apply_mate[$dragonId]['state'])){
				$items[$dragonId]['state'] = $items_apply_mate[$dragonId]['state'];
				$items[$dragonId]['remainTime'] = $items_apply_mate[$dragonId]['remainTime'];
			}
			else if(!empty($items_accept_mate[$dragonId]['state'])){
				$items[$dragonId]['state'] = $items_accept_mate[$dragonId]['state'];
				$items[$dragonId]['remainTime'] = $items_accept_mate[$dragonId]['remainTime'];
			}
			else{
					$items[$dragonId]['state'] = '空闲';
					$items[$dragonId]['remainTime'] = '无';
			}
			$items[$dragonId]['status'] = $item['status'];
			$items[$dragonId]['startTime'] = $item['startTime'];
			$items[$dragonId]['updateTime'] = $item['updateTime']?date('Y-m-d H:i:s', $item['updateTime']/1000):0;
			$items[$dragonId]['feedCdTime'] = $item['feedCdTime']?date('Y-m-d H:i:s', $item['feedCdTime']/1000):0;
		}

	}else{
		$alert = '用户不存在或不属于当前选择的服务器！';
	}
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
<?php
!defined('IN_ADMIN') && exit('Access Denied');
//固定框架，双主键修改，不可添加删除单行数据
$title = "玩家城堡皮肤数据";
$alert = '';
$type = $_REQUEST['type'];
if($_REQUEST['username'])
	$username = $_REQUEST['username'];
if($_REQUEST['useruid'])
	$useruid = $_REQUEST['useruid'];
if($_REQUEST['type2'])
	$type2 = $_REQUEST['type2'];

if(!empty($type2)){
	$type2 = intval($type2);
}else{
	$type2 = 100;
}

$db = 'user_state';
$dbArray = array(
	'stateId' => array('name'=>'皮肤ID',),
	'name' => array('name'=>'皮肤名称',),
	'startTime' => array('name'=>'开始时间',),
	'endTime' => array('name'=>'结束时间',),
	'count' => array('name'=>'数量',),
	'status' => array('name'=>'使用情况',),
);
//查user_state
if($username)
	$sql = "select uid,name from  userprofile where name = '{$username}'";
else
	$sql = "select uid,name from userprofile where uid = '{$useruid}'";
$result = $page->execute($sql,3);

if( $result['ret']['data']){
	$useruid = $result['ret']['data'][0]['uid'];
	//查user_state 每种装扮确定只能查回一条
 		$sql = "select stateId,startTime,endTime from user_state where uid = '{$useruid}' and type2=$type2";



	$result = $page->execute($sql,3);	
	if(!$result['error'] && $result['ret']['data']){
		foreach ($result['ret']['data'] as $key => $item) {
			$row = array();
			$stateId=$item['stateId'];
			 //if(strpos($skinIdName[$stateId],$SKIN_TYPE[$type2]) === false) continue;//兼容没配置名称的新道具
			$row['stateId'] = $stateId;
			if(empty($skinIdName[$stateId])) $row['name'] = 'para1='.$stateId." 名称暂时未配置。请到物品表查询。";
			else $row['name'] = $skinIdName[$stateId];
			$row['startTime'] = date('Y-m-d H:i:s',$item['startTime']/1000).','.$item['startTime']%1000;
			if($item['endTime']/1000 - time() > 10*365*86400 )  $row['endTime'] = '永久有效';
			else $row['endTime'] = date('Y-m-d H:i:s',$item['endTime']/1000).','.$item['endTime']%1000;
			$row['count'] = 1;
			$row['status'] = '正在使用';
			$items[] = $row;
		}
		
	}

	//avatar_storage
		$sql = "select avatarId,expirationTime from avatar_storage where uid = '{$useruid}'";
	$result = $page->execute($sql,3);
	if(!$result['error'] && $result['ret']['data'])
		$avatar_storage=$result['ret']['data'];
	foreach($avatar_storage as $item){
			$stateId=$item['avatarId'];
//			if(strpos($skinIdName[$stateId],$SKIN_TYPE[$type2]) === false) continue;
			if(count($items) > 0 && $stateId == $items[0]['stateId'] ) continue;//使用中的
			$row = array();
			$row['stateId'] = $stateId;
			$row['name'] = $skinIdName[$stateId];
			$row['startTime'] = '-';
			if($item['expirationTime'] == -1)  $row['endTime'] = '永久有效';
			else $row['endTime'] =date('Y-m-d H:i:s', $item['expirationTime']/1000).','.$item['expirationTime']%1000;
			$row['count'] = 1;
			$row['status'] = '放置中';
			$items[] = $row;
	}


	//查user_item
		$sql = "select itemId,count from user_item where ownerId = '{$useruid}'";
	$result = $page->execute($sql,3);
	if(!$result['error'] && $result['ret']['data'])
		$user_item = $result['ret']['data'] ;

	foreach($user_item as $item){
		if($item2Avatar[$item['itemId']]){
			$stateId=$item2Avatar[$item['itemId']];
//			if(strpos($skinIdName[$stateId],$SKIN_TYPE[$type2]) === false) continue;
			$row = array();
			$row['stateId'] = $stateId;
			$row['name'] = $skinIdName[$stateId];
			$row['startTime'] = '-';
			$row['endTime'] = '-';
			$row['count'] = $item['count'];
			$row['status'] = '未使用';
			$items[] = $row;
		}
	}
	if(empty($items)) $alert = "该玩家没有购买使用过皮肤！";
}else{
	$alert = '用户不存在或不属于当前选择的服务器！';
}

$eventOptions = '';
foreach ($SKIN_TYPE as $eventType => $eventName){
	$eventOptions .= "<option value={$eventType} ";
	if( $type2 === $eventType) $eventOptions .= "selected";
	$eventOptions .= ">{$eventName}</option>";
}
//if (!$privileges['dropdownlist_view']) {
//}else{
//	$selectEventCtl = '<br>
//	装扮类型
//	<select id="type2" name="type2"  onchange="">
//			'.$eventOptions.'
//	</select>
//	';
//}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>

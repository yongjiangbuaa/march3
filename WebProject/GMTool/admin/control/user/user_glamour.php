<?php
!defined('IN_ADMIN') && exit('Access Denied');
//固定框架，双主键修改，不可添加删除单行数据
$title = "魅力值礼物";
$type = $_REQUEST['type'];
if($_REQUEST['username'])
	$username = $_REQUEST['username'];
if($_REQUEST['useruid'])
	$useruid = $_REQUEST['useruid'];
$db = 'user_glamour_gift_record';
$dbArray = array(
	'from_uid' => array('name'=>'送礼人',),
	'to_uid' => array('name'=>'收礼人',),
	'item_id' => array('name'=>'物品ID',),
	'count' => array('name'=>'数量','editable'=>0,),
	'item_glamour' => array('name'=>'单个物品增加魅力值','editable'=>0,),
	'total_glamour' => array('name'=>'总魅力值','editable'=>0),
	'create_time' => array('name'=>'赠送时间','editable'=>0),
);

if($type){


	if($username){
		$account_list = cobar_getValidAccountList('name', $username);
		$uid = $account_list[0]['gameUid'];
		$sql = "select * from $db where from_uid = '$uid' ";
	}else{
		$sql = "select * from $db where from_uid = '{$useruid}'";
	}
	$sql .= " order by create_time desc";
	$result = $page->execute($sql,3);
	$items = array();
	if(!$result['error'] && $result['ret']['data']){;
		$ret = $result['ret']['data'];
		foreach ($ret as $key => $item) {
				$uuid = $item['uuid'];
				$items[$uuid]['from_uid'] = $item['from_uid'];
				$items[$uuid]['to_uid'] = $item['to_uid'];
				$items[$uuid]['item_id'] = $item['item_id'];
				$items[$uuid]['count'] = $item['count'];
				$items[$uuid]['item_glamour'] = $item['item_glamour'];
				$items[$uuid]['total_glamour'] = $item['total_glamour'];
				$items[$uuid]['create_time'] = date('Y-m-d H:i:s', $item['create_time']/1000);
		}
	}else{
		$error_msg = search($result);
		$items = array();
	}
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>

<?php
!defined('IN_ADMIN') && exit('Access Denied');
$showData = false;
$type = $_REQUEST['action'];
if($_REQUEST['username'])
	$username = $_REQUEST['username'];
if($_REQUEST['useruid'])
	$useruid = $_REQUEST['useruid'];
$headLine = "修改资源";
$headAlert = "需要在下线状态进行修改";
$dbArray = array(
	'uid' => array('name'=>'uid','uneditable'=>1,'note'=>''),
	'stone' => array('name'=>'秘银',),
	'wood' => array('name'=>'木材',),
	'iron' => array('name'=>'铁矿',),
	'food' => array('name'=>'粮食',),
	'silver' => array('name'=>'钢材',),
	'chip' => array('name'=>'筹码',),
	'diamond' => array('name'=>'金筹码',),
	'lastUpdateTime' => array('name'=>'刷新时间','uneditable'=>1),
);
if($type){
	
	if($username){
		$account_list = cobar_getValidAccountList('name', $username);
		$useruid=$account_list[0]['gameUid'];
	}
	
	if($type == 'edit') 
	{
		$replace = array();
		foreach($_REQUEST as $key => $value)
		{
			if(substr($key, 0, 5) == 'value' && $value !== '')
			{
				$replace[] = substr($key, 6) . " = '{$value}'";
			}
		}
// 		if($username)
// 			$sql = "select * from userprofile where name = '{$username}'";
// 		else
// 			$sql = "select * from userprofile where uid = '{$useruid}'";

		$sql = "select * from userprofile where uid = '{$useruid}'";

		$result = $page->execute($sql);
		if(!$result['error'] && $result['ret']['data']){
			$userId = $result['ret']['data'][0]['uid'];
			//先踢下线，然后更新数据库
			$ret = $page->webRequest('kickuser',array('uid'=>$userId));
			if($ret == 'ok'){
				$sql = "update user_resource uc inner join userprofile u on u.uid = uc.uid set ";
				$sql .= implode(',', $replace);
// 				if($username)
// 					$sql .= " where u.name = '{$username}'";
// 				else 
// 					$sql .= " where u.uid = '{$useruid}'";

				$sql .= " where u.uid = '{$useruid}'";
				
				$page->execute($sql);

                $action_params = $replace;
                adminLogUser($adminid,$userId,$currentServer,$action_params);
			}
		}
	}
// 	if($username)
// 		$sql = "select * from user_resource c inner join userprofile u on c.uid = u.uid and u.name = '{$username}'";
// 	else
// 		$sql = "select * from user_resource where uid = '{$useruid}'";

	$sql = "select * from user_resource where uid = '{$useruid}'";
	
	$result = $page->execute($sql,3);
	if(!$result['error'] && $result['ret']['data']){
		$item = $result['ret']['data'][0];
		$item['lastUpdateTime'] = date('Y-m-d H:i:s',$item['lastUpdateTime']/1000);
		$showData = true;
	}else{
		$error_msg = search($result);
	}
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
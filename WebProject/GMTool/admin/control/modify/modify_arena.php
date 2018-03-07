<?php
!defined('IN_ADMIN') && exit('Access Denied');
$showData = false;
$type = $_REQUEST['action'];
if($_REQUEST['username'])
	$username = $_REQUEST['username'];
if($_REQUEST['useruid'])
	$useruid = $_REQUEST['useruid'];
$headLine = "修改竞技场数据";
$headAlert = "需要在下线状态进行修改";
$dbArray = array(
	'uid' => array('name'=>'uid','uneditable'=>1,'note'=>''),
	'reputation' => array('name'=>'声望',),
	'replevel' => array('name'=>'爵位','uneditable'=>1,),
	'arenatimes' => array('name'=>'免费可挑战次数',),
	'bought' => array('name'=>'付费的挑战次数',),
	'buytimes' => array('name'=>'已购买次数',),
);
if($type){
	if($type == 'edit') 
	{
		$sql = "update arena uc inner join userprofile u on u.uid = uc.uid set ";
		$flag = true;
		foreach($_REQUEST as $key => $value)
		{
			if(substr($key, 0, 5) == 'value' && $value !== '')
			{
				if($flag)
				{
					$tmp = substr($key, 6) . " = " . "'{$value}'";
					$flag = false;
				}
				else
				{
					$tmp .= ", " . substr($key, 6) . " = " . "'{$value}'";
				}
			}
		}
		if($username)
			$sql .= $tmp . " where name = '{$username}'";
		else 
			$sql .= $tmp . " where u.uid = '{$useruid}'";
		$page->execute($sql);
	}
	if($username)
		$sql = "select * from arena c inner join userprofile u on c.uid = u.uid and u.name = '{$username}'";
	else
		$sql = "select * from arena where uid = '{$useruid}'";
	
	$result = $page->execute($sql,3);
	if(!$result['error'] && $result['ret']['data']){
		$item = $result['ret']['data'][0];
		$showData = true;
	}else{
		$error_msg = search($result);
	}
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
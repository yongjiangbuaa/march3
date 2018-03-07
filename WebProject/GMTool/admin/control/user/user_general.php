<?php
!defined('IN_ADMIN') && exit('Access Denied');
$type = $_REQUEST['type'];
if($_REQUEST['username'])
	$username = $_REQUEST['username'];
if($_REQUEST['useruid'])
	$useruid = $_REQUEST['useruid'];
if($type){
	//查看general信息
	if($username)
		$sql = "select g.* from user_general g inner join userprofile u on u.uid = g.uid and u.name = '{$username}'";
	else 
		$sql = "select * from user_general where uid = '{$useruid}'";
	$result = $page->execute($sql,3);
	if(!$result['error'] && $result['ret']['data']){
		$result = $result['ret']['data'];
		$generalView = $result;
		foreach ($generalView as $key => $value) {
			foreach ($value as $k => $v) {
				if(!$v)
					$generalView[$key][$k] = '-';
			}
			$generalView[$key]['time'] = date('Y-m-d H:i:s',$value['time']/1000);
		}
	}else{
		$error_msg = search($result);
	}
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
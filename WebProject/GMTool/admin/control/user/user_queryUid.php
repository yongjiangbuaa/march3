<?php
!defined('IN_ADMIN') && exit('Access Denied');
$showData = false;
$type = $_REQUEST['action'];
if($_REQUEST['username'])
	$username = $_REQUEST['username'];
$headLine = "根据用户名的关键字查询玩家信息";
$headAlert = "";
$dbindex=array(
		'server'=>'服',
		'uid'=>'UID',
		'name'=>'用户名',
		'deviceId'=>'设备Id',
		'level'=>'领主等级',
);

if ($type) {
	if (empty($username)){
		$headLine='请输入用户名';
	}else {
		$data=array();
	    $sql="select * from userprofile where name like '%$username%';";
		$ret=$page->execute($sql, 3);
	    
		foreach ($ret['ret']['data'] as $row){
			$one=array();
			foreach ($dbindex as $key=>$val){
				if ($key=='server'){
					$one[$key]=$currentServer;
				}else {
					$one[$key]=$row[$key];
				}
			}
			$data[]=$one;
		}
		if ($data){
			$showData=true;
		}else {
			$headAlert='没有查到相关数据';
		}
	}
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
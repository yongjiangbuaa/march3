<?php
!defined('IN_ADMIN') && exit('Access Denied');
$showData = false;
$headLine = "查看玩家的封号记录";
$headAlert = "";
$type = $_REQUEST['action'];
if($_REQUEST['useruid'])
	$useruid = $_REQUEST['useruid'];

$title=array('serverId'=>'服','uid'=>'uid','operator'=>'操作者','opDate'=>'操作时间','reason'=>'原因');

if ($type=='view') {
	if (empty($useruid)){
		$headAlert='玩家uid不能为空!';
	}else {
		$data=array();
		$sql="select * from ban_record where uid='$useruid' order by time desc;";
		$result=$page->globalExecute($sql, 3);
		foreach ($result['ret']['data'] as $curRow){
			$data[]=$curRow;
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
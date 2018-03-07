<?php
!defined('IN_ADMIN') && exit('Access Denied');
$showData = false;
$headLine = "查看玩家迁服记录";
$headAlert = "";
$type = $_REQUEST['action'];
if($_REQUEST['useruid'])
	$useruid = $_REQUEST['useruid'];

$title=array('uid'=>'uid','name'=>'用户名','src'=>'迁出服','dst'=>'迁入服','time'=>'时间');

if ($type=='view') {
	if (empty($useruid)){
		$headAlert='玩家uid不能为空!';
	}else {
		$data=array();
		$sql="select uid,name,src,dst,time from move_server_record where uid='$useruid' order by time desc;";
		$result=$page->globalExecute($sql, 3);
		foreach ($result['ret']['data'] as $curRow){
			$one=array();
			foreach ($title as $key=>$value){
				if ($key=='time'){
					$one[$key]=date('Y-m-d H:i:s',$curRow[$key]/1000);
				}else {
					$one[$key]=$curRow[$key];
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
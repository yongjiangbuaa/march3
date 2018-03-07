<?php
!defined('IN_ADMIN') && exit('Access Denied');
$showData = false;
$headAlert='';

if ($_REQUEST['appVersion']){
	$appVersion = $_REQUEST['appVersion'];
}

if ($_REQUEST['type']=='view') {
	$data=array();
	$nameArray=array();
	$pfArray=array();
	if (!isset($appVersion) || empty($appVersion)){
		$headAlert='游戏版本号不能为空!';
	}else {
		$sql="select name,pf,cdn_res_name,update_time from tbl_cdn_res where game_type=0 and res_type='lua' and game_version='$appVersion';";
		$ret=query_deploy($sql);
		foreach ($ret['ret']['data'] as $row){
			$data[$row['name']][$row['pf']]['cdn_res_name']=$row['cdn_res_name'];
			$data[$row['name']][$row['pf']]['update_time']=date('Y-m-d H:i:s',$row['update_time']);
			if (!in_array($row['name'], $nameArray)){
				$nameArray[]=$row['name'];
			}
			if (!in_array($row['pf'], $pfArray)){
				$pfArray[]=$row['pf'];
			}
		}
	}
	if ($data){
		$showData=true;
	}else {
		$headAlert='没有查到相关数据';
	}
}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>
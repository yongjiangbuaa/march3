<?php
!defined('IN_ADMIN') && exit('Access Denied');
global $servers;
$sttt = $_REQUEST['selectServer'];

$serverDiv=loadDiv($sttt);
$erversAndSidsArr=getSelectServersAndSids($sttt);
$selectServer=$erversAndSidsArr['withS'];
$selectId=$erversAndSidsArr['onlyNum'];

$showData=false;
$alertHeader='';

if ($_REQUEST['action'] == 'view') {

	$data=array();
	$totalNum=0;
	$sql="select count(uid) num from userprofile where picVer>2000000 and picVer<3000000;";
	foreach ($selectServer as $server=>$serValue){
		$result=$page->executeServer($server, $sql, 3);
		if($result['ret']['data'][0]['num']){
			$data[$server]=$result['ret']['data'][0]['num'];
			$totalNum+=$result['ret']['data'][0]['num'];
		}
	}
	if ($data){
		$showData=true;
	}else {
		$alertHeader="没有查询到相关数据信息";
	}
	
}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>
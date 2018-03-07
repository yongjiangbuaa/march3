<?php
!defined('IN_ADMIN') && exit('Access Denied');
$developer = in_array($_COOKIE['u'],$privilegeArr);
$showData=false;
$headAlert='';

$sttt = $_REQUEST['selectServer'];
$serverDiv=loadDiv($sttt);
$erversAndSidsArr=getSelectServersAndSids($sttt);
$selectServer=$erversAndSidsArr['withS'];
$selectServerids=$erversAndSidsArr['onlyNum'];

$dbIndex=array(
	'sid'=>'服ID',
	'days'=>'开服天数',
	'users'=>'总用户数',
	'paySum'=>'总充值',
	'newUsers'=>'新注册',
	'replay'=>'重玩',
	'relocation'=>'迁服',
	'daoliangDays'=>'导量天数',
	'tactics'=>'导量策略',
	'daoliangPeriod'=>'导量期间',
);

if ($_REQUEST['addStrategy']){
	$editSids=$_REQUEST['editSids'];
	$strategy=$_REQUEST['strategy']?$_REQUEST['strategy']:'';
	if (empty($editSids)){
		exit('编辑的服不能为空');
	}
	$editSids=trim($editSids);
	$editSids=str_replace('，', ',', $editSids);
	$sids=trim($editSids,',');
	$sql="update stat_allserver.stat_server_info set tactics='$strategy' where sid in($sids);";
	$result = query_infobright($sql);
	exit('保存成功');
}

//if($_REQUEST['analyze']=='user'){
	$data=array();
	$sids=implode(',', $selectServerids);
	$sql="select * from stat_allserver.stat_server_info where sid in($sids);";
	$result = query_infobright($sql);
	foreach ($result['ret']['data'] as $curRow){
		$item=array();
		if ($curRow['sid']>900000){
			continue;
		}
		foreach ($dbIndex as $key=>$val){
			$item[$key]=$curRow[$key]?$curRow[$key]:'';
		}
		if (!empty($item)){
			$data[$curRow['sid']]=$item;
		}
	}
	krsort($data);
	if ($data){
		$showData=true;
	}else {
		$headAlert='没有查到相关数据';
	}
//}


include( renderTemplate("{$module}/{$module}_{$action}") );
?>

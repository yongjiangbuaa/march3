<?php
!defined('IN_ADMIN') && exit('Access Denied');

global $servers;
$allServerFlag=false;

$sttt = $_REQUEST['selectServer'];
if(!isset($sttt)){
	$sttt = substr($currentServer,1);
}
$serverDiv=loadDiv($sttt);

$relogin = date('Y-m-d',time()-86400*15);

if($_REQUEST['action']){

	$erversAndSidsArr=getSelectServersAndSids($sttt);
	$selectServer=$erversAndSidsArr['withS'];
	$selectServerids=$erversAndSidsArr['onlyNum'];

	$wheresql = '1=1';

	if($_REQUEST['relogin_time']) {
		$relogin = $_REQUEST['relogin_time'];
		$lastonlinetime = strtotime($relogin)*1000;
		$wheresql .= " and lastOnlineTime < $lastonlinetime ";
	}

	if($_REQUEST['left_gold']) {
		$leftPayGold = $_REQUEST['left_gold'];
		$wheresql .= " and paidGold>$leftPayGold ";
	}

	$sql = "select uid ,from_unixtime(lastonlinetime/1000,'%Y-%m-%d') as lastdate,paidgold,paytotal,gold from userprofile where $wheresql and gmflag=0 and banTime < 2422569600000 and paidgold >0  order by paidgold desc ,lastdate desc " ;
	$data = array();
	if(in_array($_COOKIE['u'],$privilegeArr)){
		$tip = $sql;
	}
	foreach($selectServerids as $sid){
		if($sid == 0 )  {
			continue;
		}

		$sid = 's'.$sid;
		$result = $page->executeServer($sid,$sql,3);
		if($result['ret']['data']){
			foreach($result['ret']['data'] as $row){
				$data[$row['uid']] = $row;
			}
		}

	}
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
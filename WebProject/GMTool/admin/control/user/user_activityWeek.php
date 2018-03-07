<?php
!defined('IN_ADMIN') && exit('Access Denied');
if(!$_REQUEST['start'])
	$start = date("Y-m-d",time());

if($_REQUEST['analyze']=='user'){
	$activity_start = strtotime(20160229);
	$today = time();
	$days=round(($today-$activity_start)/86400)/7;
	if(($days%2)==0){
		$actHtml= "<h1>今天".date("Y-m-d",$today)."不是活动周。</h1>";
	}else{
		$actHtml= "<h1>今天".date("Y-m-d",$today)."是活动周。</h1>";
	}
	$actHtml .="<br>";
	$start = $_REQUEST['start_time'];
	$startTime = strtotime($start);
	$days=round(($startTime-$activity_start)/86400)/7;
	if(($days%2)==0){
		$actHtml.= "<h1>".date("Y-m-d",$startTime)."不是活动周。</h1>";
	}else{
		$actHtml.= "<h1>".date("Y-m-d",$startTime)."是活动周。</h1>";
	}
}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>
<?php
!defined('IN_ADMIN') && exit('Access Denied');
if(!$_REQUEST['start'])
	$start = date("Y-m-d",time()-86400*3);
if(!$_REQUEST['end'])
	$end = date("Y-m-d",time());
global $servers;
// foreach ($_REQUEST as $server=>$value)
// {
// 	if($servers[$server] && $value == 'on'){
// 		$selectServer[] = $server;
// 		$selectServerids[] = substr($server, 1);
// 	}
// }

$sttt = $_REQUEST['selectServer'];
$serverDiv=loadDiv($sttt);
$erversAndSidsArr=getSelectServersAndSids($sttt);
$selectServer=$erversAndSidsArr['withS'];
$selectId=$erversAndSidsArr['onlyNum'];

if($_REQUEST['analyze']=='user'){
	$start = $_REQUEST['start_time'];
	$end = $_REQUEST['end_time'];
	$startTime = strtotime($start)*1000;
	$endTime = strtotime($end)*1000;
	$dateLink = array();
	$temp=$startTime;
	while($temp <= $endTime){
		$tempDate = date('Y-m-d',$temp/1000);
		$temp += 86400000;
		$dateLink[$tempDate] = $tempDate;
	}
	krsort($dateLink);
	//$actNameLink = getActiviyDefine();
	$lang = loadLanguage();
	$clientXml = loadXml('events',false);
	foreach ($selectServer as $server=>$serInfo){
		foreach ($dateLink as $actTime){
			$tempTime=$actTime;
			$actStaTime=strtotime(date('Y-m-d',strtotime($tempTime)))*1000;
			$actEndTime=strtotime(date('Y-m-d',strtotime($tempTime)))*1000+ 86400000;
			$actSql = "select id from server_score where beginTime >= $actStaTime and beginTime < $actEndTime;";
			$actResult = $page->executeServer($server,$actSql,3);
			$actname = '';
			foreach ($actResult['ret']['data'] as $actCurRow){
				$actname .= ' '.$lang[(int)$clientXml[$actCurRow['id']]['name']];
			}
			$actAll[$server][$actTime] = trim($actname);
		}
	}
	$actHtml = "<table class='listTable' style='text-align:center'><thead><th>活动</th>";
	foreach($dateLink as $dateValue){
		$actHtml .= "<th>$dateValue</th>";
	}
	$actHtml .= "</thead>";
	foreach ($actAll as $serverKey=>$dateActname){
		$actHtml .= "<tbody><tr class='listTr'>";
		$actHtml .= "<td>$serverKey</td>";
		foreach ($dateActname as $date=>$actName){
			if(!$actName){
				$actName = '-';
			}
			$actHtml .= "<td>$actName</td>";
		}
		$actHtml .= "</tr></tbody>";
	}
	$actHtml .= "</table>";
}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>
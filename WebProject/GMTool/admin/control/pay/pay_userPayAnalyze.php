<?php
!defined('IN_ADMIN') && exit('Access Denied');
$start = date("Y-m-d",time()-86400*4);
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
$selectServerids=$erversAndSidsArr['onlyNum'];

if(!$_REQUEST['start'])
	$start = date("Y-m-d",time()-86400*7);
if(!$_REQUEST['end'])
	$end = date("Y-m-d",time());
if (isset($_REQUEST['start'])) {
	$start = date('Ymd',strtotime($_REQUEST['start']));
	$end = date('Ymd',strtotime($_REQUEST['end']));
	$sids=implode(',', $selectServerids);
	
	$sql = "select * from stat_allserver.pay_payAnalyze_7day where sid in ($sids) and date >=$start and date <=$end order by date desc;";
	$ret = query_infobright($sql);
	foreach ($ret['ret']['data'] as $cruRow){
		$totalUsers[$cruRow['date']] +=$cruRow['totalUsers'];
		$loseUsers[$cruRow['date']] +=$cruRow['loseUsers'];
		$silenceUsers[$cruRow['date']] +=$cruRow['silenceUsers'];
		$repeatUsers[$cruRow['date']] +=$cruRow['repeatUsers'];
		$firsetUsers[$cruRow['date']] +=$cruRow['firsetUsers'];
		$day[$cruRow['date']]['r1'] +=$cruRow['r1'];
		$day[$cruRow['date']]['r2'] +=$cruRow['r2'];
		$day[$cruRow['date']]['r3'] +=$cruRow['r3'];
		$day[$cruRow['date']]['r4'] +=$cruRow['r4'];
		$day[$cruRow['date']]['r5'] +=$cruRow['r5'];
		$day[$cruRow['date']]['r6'] +=$cruRow['r6'];
		$day[$cruRow['date']]['r7'] +=$cruRow['r7'];
	}
	
	$start=date('Y-m-d',strtotime($start));
	$end=date('Y-m-d',strtotime($end));
}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>
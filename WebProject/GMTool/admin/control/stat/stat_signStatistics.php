<?php
!defined('IN_ADMIN') && exit('Access Denied');
$showData = false;
$type = $_REQUEST['action'];
if(!$_REQUEST['start_time'])
	$start = date("Y-m-d",time()-86400*7);
if(!$_REQUEST['end_time'])
	$end = date("Y-m-d",time());
global $servers;

$sttt = $_REQUEST['selectServer'];
$serverDiv=loadDiv($sttt);
$erversAndSidsArr=getSelectServersAndSids($sttt);
$selectServer=$erversAndSidsArr['withS'];
$selectServerids=$erversAndSidsArr['onlyNum'];

if ($type=='view') {
	$start = substr($_REQUEST['start_time'],0,10);
	$end = substr($_REQUEST['end_time'],0,10);
	$startTime = date("Ymd",strtotime($start));
	$endTime = date("Ymd",strtotime($end));
	$sids = implode(',', $selectServerids);
	$sql="select date,day,sum(request) request,sum(signCount) signCount,sum(dau) dau from stat_allserver.stat_sign where date between $startTime and $endTime and sid in($sids) group by date,day order by date desc,day;";
	$result = query_infobright($sql);
	$data=array();
	$dates=array();
	foreach ($result['ret']['data'] as $curRow){
		$da=$curRow['date'];
		$data[$curRow['date']][$curRow['day']]['request']+=$curRow['request'];
		$data[$curRow['date']][$curRow['day']]['signCount']+=$curRow['signCount'];
		$data[$curRow['date']][$curRow['day']]['dau']+=$curRow['dau'];
		if(in_array($da, $dates)){
			continue;
		}
		$dates[]=$da;
	}
	if(!$data){
		$headAlert="数据查询失败!";
	}else {
		$html = "格式为发request的次数/全服签到总次数/当日活跃人数.<br><table class='listTable' style='text-align:center'><thead><th>签到情况</th>";
		foreach ($dates as $daValue){
			$html .="<th>$daValue</th>";
		}
		$html.="</thead>";
		for($i=1;$i<=7;$i++){
			$html .="<tr><td>".$i."签</td>";
			foreach ($dates as $daValue){
				$html .="<td><font color='blue'>".$data[$daValue][$i]['request']."</font>/<strong>".$data[$daValue][$i]['signCount']."</strong>/<font color='red'>".$data[$daValue][$i]['dau']."</font></td>";
			}
			$html .= "</tr>";
		}
		$html .="</table>";
	}
}
	
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
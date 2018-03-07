<?php
!defined('IN_ADMIN') && exit('Access Denied');
ini_set('memory_limit', '216M');
if(!$_REQUEST['startDate']){
	$startDate = date("Y-m-d",time()-86400*7);
}
if(!$_REQUEST['endDate'])
	$endDate = date("Y-m-d",time());
global $servers;
$allServerFlag=false;

$sttt = $_REQUEST['selectServer'];
$serverDiv=loadDiv($sttt);
$erversAndSidsArr=getSelectServersAndSids($sttt);
$selectServer=$erversAndSidsArr['withS'];
$selectServerids=$erversAndSidsArr['onlyNum'];

if($_REQUEST['allServers']){
	$allServerFlag =true;
}
if($_REQUEST['analyze']=='user'){
	$sids = implode(',', $selectServerids);
	$whereSql=" where sid in ($sids) ";
	$startDate = substr($_REQUEST['startDate'],0,10);
	$sDdate= date('Ymd',strtotime($startDate));
	$endDate = substr($_REQUEST['endDate'],0,10);
	$eDate =date('Ymd',strtotime($endDate)+86400);
	$whereSql .= " and date >=$sDdate and date <= $eDate ";
	//转盘统计
	$sql="select sid,date,sum(countUsers) uCount,sum(sumCost) costCount from stat_allserver.stat_rotaryTable_out $whereSql group by sid, date order by sid, date desc;";
	$result = query_infobright($sql);
	$outEventAll=array();
	$outTotalEvent=array();
	$outDates = array();
	foreach ($result['ret']['data'] as $curRow){
		$server='s'.$curRow['sid'];
		$yIndex = $curRow['date'];
		if(!in_array($yIndex, $outDates)){
			$outDates[]=$yIndex;
		}
		$outEventAll[$server][$yIndex]['uCount'] = $curRow['uCount'];
		
		$outTotalEvent[$yIndex]['uCount'] += $curRow['uCount'];
			
		$outEventAll[$server][$yIndex]['costCount'] = $curRow['costCount'];
			
		$outTotalEvent[$yIndex]['costCount'] += $curRow['costCount'];
	}
	
	//翻牌统计
	//$sql="select sid,date,lotteryId,position, sum(pcounts) pTimes,sum(sumCost) costSum from stat_allserver.stat_rotaryTable_in $whereSql group by sid, date,lotteryId,position order by sid, date desc,lotteryId,position;";
	$sql="select date,lotteryId,position, sum(pcounts) pTimes,sum(sumCost) costSum from stat_allserver.stat_rotaryTable_in $whereSql group by date,lotteryId,position order by date desc,lotteryId,position;";
	$result = query_infobright($sql);
// 	$inEventAll=array();
	$inTotalEvent=array();
	$inDates = array();
	foreach ($result['ret']['data'] as $curRow){
		//$server='s'.$curRow['sid'];
		$yIndex = $curRow['date'];
		if(!in_array($yIndex, $inDates)){
			$inDates[]=$yIndex;
		}
// 		$inEventAll[$server][$yIndex][$curRow['lotteryId']][$curRow['position']]['pTimes'] = $curRow['pTimes'];
		
		$inTotalEvent[$yIndex][$curRow['lotteryId']][$curRow['position']]['pTimes'] += $curRow['pTimes'];
		
// 		$inEventAll[$server][$yIndex][$curRow['lotteryId']][$curRow['position']]['costSum'] = $curRow['costSum'];
		
		$inTotalCost[$yIndex][$curRow['lotteryId']]['costSum'] += $curRow['costSum'];
		//unset($server);
		unset($yIndex);
	}
	
	$html = "<table class='listTable' style='text-align:center'><thead><th>日期</th><th colspan='2'>合计</th>";
	foreach ($selectServer as $server=>$serInfo){
		$th1 .="<th colspan='2'>$server</th>";
		$th2 .="<th>转动人数</th><th>铜币花费</th>";
	}
	if(!$allServerFlag){
		$html .=$th1 ."</thead><thead><th></th><th>转动人数</th><th>铜币花费</th>" .$th2 ."</thead>";
	}else {
		$html .="</thead><thead><th></th><th>转动人数</th><th>铜币花费</th></thead>";
	}
	rsort($outDates);
	foreach($outDates as $date){
			
		$html .="<tbody><tr><td>$date</td><td>".$outTotalEvent[$date]['uCount']."</td><td>".$outTotalEvent[$date]['costCount']."</td>";
		if(!$allServerFlag){
			foreach ($selectServer as $server=>$serInfo){
				$html .="<td>". $outEventAll[$server][$date]['uCount'] ."</td><td>". $outEventAll[$server][$date]['costCount'] ."</td>";
			}
		}
		$html .="</tr></tbody>";
	}
	$html .= "</table><br>";
	
	
	$html .= "<div><table class='listTable' style='text-align:center'><thead><th>日期</th><th>策略Id</th>";
	for($i=1;$i<=9;$i++){
		$html .="<th>位置$i</th>";
	}
	$html .="<th>总花费</th></thead>";
	rsort($inDates);
	foreach ($inDates as $date){
		foreach ($inTotalEvent[$date] as $lotteryId=>$posValue){
			$html .= "<tr><td>$date</td><td>$lotteryId</td>";
			for($j=1;$j<=9;$j++){
				$html .= "<td>".$posValue[$j]['pTimes']."</td>";
			}
			$html .= "<td>".$inTotalCost[$date][$lotteryId]['costSum']."</td></tr>";
		}
	}
	$html .= "</table><br><br><br></div>";
}



include( renderTemplate("{$module}/{$module}_{$action}") );
?>
<?php
!defined('IN_ADMIN') && exit('Access Denied');
if(!$_REQUEST['startDate']){
	$startDate = date("Y-m-d",time()-86400*7);
}
if(!$_REQUEST['endDate'])
	$endDate = date("Y-m-d",time());
if($_REQUEST['analyze']=='update'){
	
}
global $servers;

$sttt = $_REQUEST['selectServer'];
$serverDiv=loadDiv($sttt);
$erversAndSidsArr=getSelectServersAndSids($sttt);
$selectServer=$erversAndSidsArr['withS'];
$selectServerids=$erversAndSidsArr['onlyNum'];

$spendArray=array(
	'1'=>'0-1000',
	'2'=>'1000-10000',
	'3'=>'10000-40000',
	'4'=>'40000-100000',
	'5'=>'100000-200000',
	'6'=>'200000-1000000',
	'7'=>'1000000-2000000',
	'8'=>'2000000 以上',
);

if($_REQUEST['analyze']=='user'){
	
	$sids = implode(',', $selectServerids);
	$whereSql=" where sid in ($sids) ";
	$startDate = substr($_REQUEST['startDate'],0,10);
	$sDdate= date('Ymd',strtotime($startDate));
	$endDate = substr($_REQUEST['endDate'],0,10);
	$eDate =date('Ymd',strtotime($endDate));
	$whereSql .= " and date >=$sDdate and date <= $eDate ";
	
	$sql="select * from stat_allserver.stat_lost_payUsers $whereSql;";
	
	$result = query_infobright($sql);
	$dateArray=array();
	$blevelArray=array();
	$payLevelArray=array();
	foreach ($result['ret']['data'] as $curRow){
	
		if(!in_array($curRow['date'], $dateArray)){
			$dateArray[]=$curRow['date'];
		}
		if(!in_array($curRow['blevel'], $blevelArray)){
			$blevelArray[]=$curRow['blevel'];
		}
		if(!in_array($curRow['payLevel'], $payLevelArray)){
			$payLevelArray[]=$curRow['payLevel'];
		}
		
		$total[$curRow['date']][$curRow['blevel']][$curRow['payLevel']]+=$curRow['cnt'];
		
	}
	rsort($dateArray);
	sort($blevelArray);
	sort($payLevelArray);
	
	//表头  数据
	$html='';
	foreach ($dateArray as $d){
		$html .="<strong>日期:$d</strong><br>";
		$html .= "<table class='listTable' style='text-align:center'><thead><th>支付等级／城堡等级</th>";
		foreach ($blevelArray as $bl){
			$html .= "<th>$bl</th>";
		}
		$html .= "</thead>";
		
		foreach ($payLevelArray as $pl){
			$html .= "<tr><td>".$spendArray[$pl]."</td>";
			foreach ($blevelArray as $bl){
				$html .= "<td>".$total[$d][$bl][$pl]."</td>";
			}
			$html .= "</tr>";
		}
		$html .= "</table><br>";
	}
	
		/*
		 * 
		$html = "<table class='listTable' style='text-align:center'><thead><th>日期</th><th>大本等级</th><th>支付等级</th><th>流失人数</th></thead>";
		foreach ($dateArray as $d){
			foreach ($blevelArray as $bl){
				foreach ($payLevelArray as $pl){
					$html.="<tr><td>$d</td><td>$bl</td><td>$pl</td><td>".$total[$d][$bl][$pl]."</td></tr>";
				}
			}
		}
		
		$html .= "</table><br>";
		*
		*/
	
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
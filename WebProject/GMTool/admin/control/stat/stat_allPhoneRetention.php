<?php
!defined('IN_ADMIN') && exit('Access Denied');
if(!$_REQUEST['startDate']){
	$startDate = date("Y-m-d",time()-86400*7);
}
if(!$_REQUEST['endDate'])
	$endDate = date("Y-m-d",time());
global $servers;

$sttt = $_REQUEST['selectServer'];
$serverDiv=loadDiv($sttt);
$erversAndSidsArr=getSelectServersAndSids($sttt);
$selectServer=$erversAndSidsArr['withS'];
$selectServerids=$erversAndSidsArr['onlyNum'];

if($_REQUEST['analyze']=='user'){
	$startDate = substr($_REQUEST['startDate'],0,10);
	$sDdate= date('Ymd',strtotime($startDate));
	$endDate = substr($_REQUEST['endDate'],0,10);
	$eDate =date('Ymd',strtotime($endDate)+86400);
	$sids = implode(',', $selectServerids);
	$dayArr = array(1,3,7,30);
	foreach ($dayArr as $day) {
		$rfields[] = "sum(".'r'.$day.") as ".'r'.$day;
	}
	$fields = implode(',', $rfields);
	$sql = "select date,model,sum(reg_all) regAll,$fields from stat_allserver.stat_retention_allPhone where reg_all>0 and date between $sDdate and $eDate and sid in($sids) group by date,model;";
	$result = query_infobright($sql);
	$dateRetention=array();
	$remainData=array();
	$dateArray=array();
	foreach ($result['ret']['data'] as $curRow){
		$dateRetention[$curRow['date']][$curRow['model']] += $curRow['regAll'];
		foreach ($dayArr as $day) {
			$count = $curRow['r'.$day]?$curRow['r'.$day]:0;
			$remainData[$curRow['date']][$day][$curRow['model']] += $count;
		}
		if(in_array($curRow['date'], $dateArray)){
			continue;
		}
		$dateArray[]=$curRow['date'];
	}
	rsort($dateArray);
	
	
	$html = "<table class='listTable' style='text-align:center'><thead><th>日期</th><th>注册</th><th>第1天登录</th><th>留存(%)</th><th>第3天登录</th><th>留存(%)</th><th>第7天登录</th><th>留存(%)</th><th>第30天登录</th><th>留存(%)</th></thead>";
	foreach ($dateArray as $dateValue){
		$html .="<tr><td>$dateValue</td>";
		$html.="<td valign=top><table class='listTable' style='text-align:center'>";
		foreach ($dateRetention[$dateValue] as $keyModel=>$value){
			$html.="<tr><td>$keyModel</td><td>$value</td></tr>";
		}
		$html .="</table></td>";
		foreach ($dayArr as $day) {
			$h1='';
			$html.="<td valign=top><table class='listTable' style='text-align:center'>";
			foreach ($remainData[$dateValue][$day] as $keyModel=>$value){
				$html.="<tr><td>$keyModel</td><td>$value</td></tr>";
				$r=intval($value / $dateRetention[$dateValue][$keyModel] *10000)/100;
				$r=number_format($r,2);
				$h1 .="<tr><td>$keyModel</td><td>$r</td></tr>";
			}
			$html .="</table></td>";
			$html.="<td valign=top><table class='listTable' style='text-align:center'>".$h1."</table></td>";
		}
		$html.="</tr>";
	}
	$html.="</table>";
	
}


include( renderTemplate("{$module}/{$module}_{$action}") );
?>
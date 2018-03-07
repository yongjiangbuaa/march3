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
$allServerFlag=false;
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

if (!$_REQUEST['selectCountry']) {
	$currCountry = 'ALL';
}else{
	$currCountry = $_REQUEST['selectCountry'];
}

if($_REQUEST['analyze']=='user'){
	
	$startDate = substr($_REQUEST['startDate'],0,10);
	$sDdate= date('Ymd',strtotime($startDate));
	$endDate = substr($_REQUEST['endDate'],0,10);
	$eDate =date('Ymd',strtotime($endDate));
	$whereSql .= " where date >=$sDdate and date <= $eDate ";
	if($currCountry&&$currCountry!='ALL'){
		$whereSql .=" and country='$currCountry' ";
	}
	$dateArray=array();
	$countryArray=array();
	$data=array();
	$sql= "select date,country,cnt from gp_installs $whereSql order by date desc,cnt desc;";
//	$link=mysqli_connect('localhost','root','password','global');
	$link = get_stats_global_connection();
	$res = mysqli_query($link,$sql);
	while ($curRow = mysqli_fetch_assoc($res)){
		if(!in_array($curRow['date'], $dateArray)){
			$dateArray[]=$curRow['date'];
		}
		if(!in_array($curRow['country'], $countryArray)){
			$countryArray[]=$curRow['country'];
		}
		$data[$curRow['date']][$curRow['country']] = $curRow['cnt'];
		$data[$curRow['date']]['total'] += $curRow['cnt'];
	}
	
	sort($dateArray);
	//表头  数据
	$html = "<table class='listTable' style='text-align:center'><thead><th>日期</th><th>国家</th><th>注册数</th></thead>";
	foreach ($data as $date=>$countryVal){
		$html .="<tr style='background-color: lavender;'><td>$date</td><td>合计</td><td>".$data[$date]['total']."</td></tr>";
		foreach ($countryVal as $country=>$val){
			if ($country=='total'){
				continue;
			}
			if (isset($data[$date][$country])){
				$html .="<tr><td>$date</td><td>$country</td><td>".$data[$date][$country]."</td></tr>";
			}
		}
	}
	$html .= "</table><br>";
	
	
	$showData=array();
	$dateList=array();
	foreach ($data as $date=>$countryValue){
		foreach ($countryValue as $country=>$val){
			if ($country=='total'){
				continue;
			}
			$showData[$country][$date] = array('x'=>$date,'y'=>$val);
			$total[$country] += $val;
			if (!in_array($date, $dateList)){
				$dateList[]=$date;
			}
		}
	}
	$dateStr=implode(',', $dateList);
	$dateStr='['.$dateStr.']';
	//$op_msg = search($total);
	
		
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
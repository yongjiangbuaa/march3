<?php
!defined('IN_ADMIN') && exit('Access Denied');
if(!$_REQUEST['startDate']){
	$startDate = date("Y-m-d",time()-86400*5);
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
if (!$_REQUEST['selectPf']) {
	$currPf = 'ALL';
}else{
	$currPf = $_REQUEST['selectPf'];
}
if (!$_REQUEST['selectReferrer']) {
	$currReferrer = 'ALL';
}else{
	$currReferrer = $_REQUEST['selectReferrer'];
}
if($_REQUEST['allServers']){
	$allServerFlag =true;
}

if($_REQUEST['event']=='output'){
	/* $serverStr=$_REQUEST['serverStr'];
	$serverStr=trim($serverStr,'|');
	$startDate=$_REQUEST['startDate'];
	$endDate=$_REQUEST['endDate'];
	$selectCountry=$_REQUEST['selectCountry'];
	$selectPf=$_REQUEST['selectPf'];
	$temp=explode('|', $serverStr);
	foreach ($temp as $server){
		$selectServer[] = $server;
		$selectServerids[] = substr($server, 1);
	} */
	$sids = implode(',', $selectServerids);
	$whereSql=" where sid in ($sids) ";
	$startDate = substr($_REQUEST['startDate'],0,10);
	$sDdate= date('Ymd',strtotime($startDate));
	$endDate = substr($_REQUEST['endDate'],0,10);
	$eDate =date('Ymd',strtotime($endDate)+86400);
	$whereSql .= " and date >=$sDdate and date <= $eDate ";
	if($currCountry&&$currCountry!='ALL'){
		$whereSql .=" and country='$currCountry' ";
	}
	if($currPf&&$currPf!='ALL'){
		$whereSql .=" and pf='$currPf' ";
	}else if ($currPf=='ALL' && $_COOKIE['u']=='xiaomi'){
		$whereSql .=" and pf in('cn_360','cn_am','cn_anzhi','cn_baidu','cn_dangle','cn_ewan','cn_huawei','cn_kugou','cn_kupai','cn_lenovo','cn_mi','cn_mihy','cn_mzw','cn_nearme','cn_pps','cn_pptv','cn_sogou','cn_toutiao','cn_uc','cn_vivo','cn_wdj','cn_wyx','cn_youku','cn_oppo','cn_sy37','cn_mz','tencent') ";
	}
	if($currReferrer&&$currReferrer!='ALL'){
		$whereSql .=" and referrer='$currReferrer' ";
	}
//	if($_COOKIE['u']!='yaoduo'){
		$sql= "select sid,date,sum(dau) s_dau,sum(reg) s_reg,sum(replay) s_replay,sum(relocation) s_relocation,sum(paid_dau) as paid_dau,sum(totalDeviceDau) as totalDeviceDau,sum(deviceDau) as olddeviceDau from stat_allserver.stat_dau_daily_pf_country_referrer $whereSql group by sid, date order by sid, date desc;";
//	}else {
//		$sql= "select sid,date,sum(dau) s_dau,sum(reg) s_reg,sum(paid_dau) as paid_dau,sum(totalDeviceDau) as totalDeviceDau,sum(deviceDau) as olddeviceDau from stat_allserver.stat_dau_daily_pf_country_v2 $whereSql group by sid, date order by sid, date desc;";
//	}
	//exit($sql);
	$eventAll = array();//合计
	$dates = array();
	$result = query_infobright($sql);
	foreach ($result['ret']['data'] as $curRow){
	
		$server=$curRow['sid'];
		$yIndex = $curRow['date'];
		$eventAll[$server][$yIndex]['dau'] = $curRow['s_dau'];
	
		$eventAll[$server][$yIndex]['paid_dau'] = $curRow['paid_dau'];

		$eventAll[$server][$yIndex]['totalDeviceDau'] = $curRow['totalDeviceDau'];

		$eventAll[$server][$yIndex]['deviceDau'] = $curRow['olddeviceDau'];
			
		$eventAll[$server][$yIndex]['sdau'] = $curRow['s_dau'] - $curRow['s_reg']-intval($curRow['s_replay'])-intval($curRow['s_relocation']);
		
		$eventAll[$server][$yIndex]['reg'] = $curRow['s_reg'];
		
		$eventAll[$server][$yIndex]['replay'] = intval($curRow['s_replay']);
			
		$eventAll[$server][$yIndex]['relocation'] = intval($curRow['s_relocation']);
	
		//$eventAll[$server][$yIndex]['date'] = $yIndex;
	}
	$title = array('8%'=>'服','20%'=>'日期','------','日活跃','------','机器码DAU','------','老机器码DAU','------','付费DAU','------','老玩家','------','新注册','------','重玩','------','迁入');
	
	//导入PHPExcel类
	require ADMIN_ROOT . "/include/PHPExcel.php";
	// Create new PHPExcel object
	$objPHPExcel = new PHPExcel();
	// Set properties
	$objPHPExcel->getProperties()
	->setCreator("Maarten Balliauw")
	->setLastModifiedBy("Maarten Balliauw")
	->setTitle("Office 2007 XLSX Test Document")
	->setSubject("Office 2007 XLSX Test Document")
	->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
	->setKeywords("office 2007 openxml php")
	->setCategory("Test result file");
	$titleIndex = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE');
	//set title
	$Excel = $objPHPExcel->setActiveSheetIndex(0);
	$row = 1;
	//set data
	$line = 0;
	foreach ($title as $width=>$value){
		if(strlen($value) != mb_strlen($value)){
			$width = (strlen($value) + iconv_strlen($value))* 1.1 * 8.26/22;
			$objPHPExcel->getActiveSheet()->getColumnDimension($titleIndex[$line])->setWidth($width);
		}else{
			$objPHPExcel->getActiveSheet()->getColumnDimension($titleIndex[$line])->setAutoSize(true);
		}
		$Excel->setCellValue($titleIndex[$line++].''.$row,$value);
	}
	$row++;
	foreach ($eventAll as $serverKey=>$dateValue){
		foreach ($dateValue as $dateKey=>$value){
			$Excel->setCellValue($titleIndex[0].''.$row, $serverKey);
			$Excel->setCellValue($titleIndex[1].''.$row,$dateKey);
			$Excel->setCellValue($titleIndex[2].''.$row,'------');
			$Excel->setCellValue($titleIndex[3].''.$row,$value['dau']);
			$Excel->setCellValue($titleIndex[4].''.$row,'------');
			$Excel->setCellValue($titleIndex[5].''.$row,$value['totalDeviceDau']);
			$Excel->setCellValue($titleIndex[6].''.$row,'------');
			$Excel->setCellValue($titleIndex[7].''.$row,$value['olddeviceDau']);
			$Excel->setCellValue($titleIndex[8].''.$row,'------');
			$Excel->setCellValue($titleIndex[9].''.$row,$value['paid_dau']);
			$Excel->setCellValue($titleIndex[10].''.$row,'------');
			$Excel->setCellValue($titleIndex[11].''.$row,$value['sdau']);
			$Excel->setCellValue($titleIndex[12].''.$row,'------');
			$Excel->setCellValue($titleIndex[13].''.$row,$value['reg']);
			$Excel->setCellValue($titleIndex[14].''.$row,'------');
			$Excel->setCellValue($titleIndex[15].''.$row,$value['replay']);
			$Excel->setCellValue($titleIndex[16].''.$row,'------');
			$Excel->setCellValue($titleIndex[17].''.$row,$value['relocation']);
			$row++;
		}
	}
	//filename
	$file_name = '日活跃统计';
	// Rename sheet
	$objPHPExcel->getActiveSheet()->setTitle($file_name);
	// Set active sheet index to the first sheet, so Excel opens this as the first sheet
	$objPHPExcel->setActiveSheetIndex(0);
	// Redirect output to a client鈥檚 web browser (Excel5)
	header('Content-Type: application/vnd.ms-excel');
	header("Content-Disposition: attachment;filename={$file_name}.xls");
	header('Cache-Control: max-age=0');

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$objWriter->save('php://output');
	exit();
}

if($_REQUEST['analyze']=='user'){
// 	$start = $_REQUEST['startDate']?strtotime($_REQUEST['startDate'])*1000:0;
// 	$end  = strtotime($_REQUEST['endDate'])*1000 + 86400000;
// 	$sql = "select count(distinct uid) as count,date_format(from_unixtime(`time`/1000),'%Y-%m-%d') as date from stat_login where (`time`>={$start} and `time`<{$end}) group by date order by time desc";
// 	$ssql = "select count(distinct l.uid) as count,date_format(from_unixtime(l.time/1000),'%Y-%m-%d') as date from stat_login l inner join stat_reg r on l.uid = r.uid "
// 			."where l.time>=$start and l.time<$end and  cast(l.time/86400000 as UNSIGNED )  !=  cast(r.time/86400000 as UNSIGNED ) group by date order by null";
	
	$sids = implode(',', $selectServerids);
	$whereSql=" where sid in ($sids) ";
	$startDate = substr($_REQUEST['startDate'],0,10);
	$sDdate= date('Ymd',strtotime($startDate));
	$endDate = substr($_REQUEST['endDate'],0,10);
	$eDate =date('Ymd',strtotime($endDate)+86400);
	$whereSql .= " and date >=$sDdate and date <= $eDate ";
	if($currCountry&&$currCountry!='ALL'){
		$whereSql .=" and country='$currCountry' ";
	}
	if($currPf&&$currPf!='ALL'){
		$whereSql .=" and pf='$currPf' ";
	}else if ($currPf=='ALL' && $_COOKIE['u']=='xiaomi'){
		$whereSql .=" and pf in('cn_360','cn_am','cn_anzhi','cn_baidu','cn_dangle','cn_ewan','cn_huawei','cn_kugou','cn_kupai','cn_lenovo','cn_mi','cn_mihy','cn_mzw','cn_nearme','cn_pps','cn_pptv','cn_sogou','cn_toutiao','cn_uc','cn_vivo','cn_wdj','cn_wyx','cn_youku','cn_oppo','cn_sy37','cn_mz','tencent') ";
	}
	if($currReferrer&&$currReferrer!='ALL'){
		$whereSql .=" and referrer='$currReferrer' ";
	}
//	if($_COOKIE['u']!='yaoduo'){
		$sql= "select sid,date,sum(dau) s_dau,sum(reg) s_reg,sum(replay) s_replay,sum(relocation) s_relocation,sum(paid_dau) as paid_dau,sum(totalDeviceDau) as totalDeviceDau,sum(deviceDau) as olddeviceDau from stat_allserver.stat_dau_daily_pf_country_referrer $whereSql group by sid, date order by sid, date desc;";
//	}
		$totalEvent = array();//合计
		$allReg = array(); //新注册合计
		$dates = array();
		$result = query_infobright($sql);
		foreach ($result['ret']['data'] as $curRow){
		
			$server='s'.$curRow['sid'];
			$yIndex = $curRow['date'];
			if(!in_array($yIndex, $dates)){
				$dates[]=$yIndex;
			}
			$eventAll[$server][$yIndex]['dau'] = $curRow['s_dau'];
		
			$totalEvent[$yIndex]['dau'] += $curRow['s_dau'];
			
			$eventAll[$server][$yIndex]['paid_dau'] = $curRow['paid_dau'];
			
			$totalEvent[$yIndex]['paid_dau'] += $curRow['paid_dau'];

			$eventAll[$server][$yIndex]['totalDeviceDau'] = $curRow['totalDeviceDau'];

			$totalEvent[$yIndex]['totalDeviceDau'] += $curRow['totalDeviceDau'];

			$eventAll[$server][$yIndex]['olddeviceDau'] = $curRow['olddeviceDau'];
			
			$totalEvent[$yIndex]['olddeviceDau'] += $curRow['olddeviceDau'];
		
			$eventAll[$server][$yIndex]['sdau'] = $curRow['s_dau'] - $curRow['s_reg']-intval($curRow['s_replay'])-intval($curRow['s_relocation']);
		
			$totalEvent[$yIndex]['sdau'] += $curRow['s_dau'] - $curRow['s_reg']-intval($curRow['s_replay'])-intval($curRow['s_relocation']);
		
			$eventAll[$server][$yIndex]['reg'] = $curRow['s_reg'];
		
			$totalEvent[$yIndex]['reg'] += $curRow['s_reg'];
			
			$eventAll[$server][$yIndex]['replay'] = intval($curRow['s_replay']);
			
			$totalEvent[$yIndex]['replay'] += intval($curRow['s_replay']);
			
			$eventAll[$server][$yIndex]['relocation'] = intval($curRow['s_relocation']);
			
			$totalEvent[$yIndex]['relocation'] += intval($curRow['s_relocation']);
		
			$allReg[$server] += $curRow['s_reg'];
		
			$eventAll[$server][$yIndex]['date'] = $yIndex;
		}
		$totalAllReg=array_sum($allReg);
		$allReg['合计']=$totalAllReg;

		
		$startDateTs =  strtotime($startDate) * 1000;
		$endDateTs =  (strtotime($endDate)+86400) * 1000;
		$sql="select sid, date,sum(payTotle) as payTotle,sum(payUsers) as payUsers,sum(payTimes) as payTimes,sum(dau) as dau,sum(firstPay) as firstPay from stat_allserver.pay_analyze_pf_country_referrer_new $whereSql GROUP BY date,sid order by sid, date desc;";
		$result = query_infobright($sql);
		$totalMoney = array();
		$totalTimes = array();
		$totalUser = array();
		$payData=array();
		foreach ($result['ret']['data'] as $curRow){
			$server='s'.$curRow['sid'];
			$yIndex = $curRow['date'];
			$payData[$server][$yIndex]['total'] += $curRow['payTotle'];
			$totalMoney[$yIndex] += $curRow['payTotle'];
			$payData[$server][$yIndex]['uniquePay'] = $curRow['payUsers'];
			$totalUser[$yIndex] += $curRow['payUsers'];
			$payData[$server][$yIndex]['totalPay'] = $curRow['payTimes'];
			$totalTimes[$yIndex] += $curRow['payTimes'];
			$payFirst[$server][$yIndex] = $curRow['firstPay'];
			$totalFirst[$yIndex] += $curRow['firstPay'];
		}
		
	//表头  数据
	//if($_COOKIE['u']=='yaoduo'){
		$html = "<table class='listTable' style='text-align:center'><thead><th></th><th colspan='8'>合计</th>";
		foreach ($selectServer as $server=>$serInfo){
			if($server == 's0') continue;
			$th1 .="<th colspan='8'>$server</th>";
			$th2 .="<th>日活跃</th><th>机器码DAU</th><th>老机器码DAU</th><th>付费DAU</th><th>老玩家</th><th>新注册</th><th>重玩</th><th>迁入</th>";
			$th3 .="<th colspan='8'><font color='red'>".$allReg[$server]."</font></th>";
		}
		if(!$allServerFlag){
			$html .=$th1 ."</thead><thead><th><font color='red'>新注册<br>合计</font></th><th colspan='8'><font color='red'>".$allReg['合计']."</font></th>$th3</thead><thead><th>日期</th><th>日活跃</th><th>机器码DAU</th><th>老机器码DAU</th><th>付费DAU</th><th>老玩家</th><th>新注册</th><th>重玩</th><th>迁入</th>" .$th2 ."</thead>";
		}else {
			$html .="<thead><th><font color='red'>新注册<br>合计</font></th><th colspan='8'><font color='red'>".$allReg['合计']."</font></th></thead><thead><th>日期</th><th>日活跃</th><th>机器码DAU</th><th>老机器码DAU</th><th>付费DAU</th><th>老玩家</th><th>新注册</th><th>重玩</th><th>迁入</th></thead>";
		}
		rsort($dates);
		foreach($dates as $date){
			
			$html .="<tbody><tr><td>$date</td><td>".$totalEvent[$date]['dau']."</td><td>".$totalEvent[$date]['totalDeviceDau']."</td><td>".$totalEvent[$date]['olddeviceDau']."</td><td>".$totalEvent[$date]['paid_dau']."</td><td>".$totalEvent[$date]['sdau']."</td><td>".$totalEvent[$date]['reg']."</td><td>".$totalEvent[$date]['replay']."</td><td>".$totalEvent[$date]['relocation']."</td>";
			if(!$allServerFlag){
				foreach ($selectServer as $server=>$serInfo){
					if($server == 's0') continue;
					$html .="<td>". $eventAll[$server][$date]['dau'] ."</td><td>". $eventAll[$server][$date]['totalDeviceDau'] ."</td><td>". $eventAll[$server][$date]['olddeviceDau'] ."</td><td>". $eventAll[$server][$date]['paid_dau'] ."</td><td>". $eventAll[$server][$date]['sdau'] ."</td><td>". $eventAll[$server][$date]['reg'] ."</td><td>". $eventAll[$server][$date]['replay'] ."</td><td>". $eventAll[$server][$date]['relocation'] ."</td>";
				}
			}
			$html .="</tr></tbody>";
		}
		$html .= "</table><br>";
		
// 	}else {
// 		$html = "<table class='listTable' style='text-align:center'><thead><th></th><th colspan='4'>合计</th>";
// 		foreach ($selectServer as $server){
// 			$th1 .="<th colspan='4'>$server</th>";
// 			$th2 .="<th>日活跃</th><th>机器码Dau</th><th>老玩家</th><th>新注册</th>";
// 			$th3 .="<th colspan='4'><font color='red'>".$allReg[$server]."</font></th>";
// 		}
// 		$html .=$th1 ."</thead><thead><th><font color='red'>新注册<br>合计</font></th><th colspan='4'><font color='red'>".$allReg['合计']."</font></th>$th3</thead><thead><th>日期</th><th>日活跃</th><th>机器码Dau</th><th>老玩家</th><th>新注册</th>" .$th2 ."</thead>";
// 		rsort($dates);
// 		foreach($dates as $date){
// 			$html .="<tbody><tr><td>$date</td><td>".$totalEvent[$date]['dau']."</td><td>".$totalEvent[$date]['deviceDau']."</td><td>".$totalEvent[$date]['sdau']."</td><td>".$totalEvent[$date]['reg']."</td>";
// 			foreach ($selectServer as $server){
// 				$html .="<td>". $eventAll[$server][$date]['dau'] ."</td><td>".$eventAll[$server][$date]['deviceDau']."</td><td>". $eventAll[$server][$date]['sdau'] ."</td><td>". $eventAll[$server][$date]['reg'] ."</td>";
// 			}
// 			$html .="</tr></tbody>";
// 		}
// 		$html .= "</table><br>";
		
		//
		$html .= "<div><table class='listTable' style='text-align:center'><thead><th></th><th colspan='5'>合计</th>";
		foreach ($payData as $server=>$data){
			$th4 .="<th colspan='5'>$server</th>";
			$th5 .="<th>总金额</th><th>付费人数</th><th>付费次数</th><th>ARPU</th><th>首充人数</th>";
		}
		if(!$allServerFlag){
			$html .=$th4 ."</thead><thead><th>日期</th><th>总金额</th><th>付费人数</th><th>付费次数</th><th>ARPU</th><th>首充人数</th>" .$th5 ."</thead>";
		}else {
			$html .="<thead><th>日期</th><th>总金额</th><th>付费人数</th><th>付费次数</th><th>ARPU</th><th>首充人数</th></thead>";
		}
		krsort($totalUser);
		foreach ($totalUser as $dateKey=>$payInfo){
			$html .= "<tr><td>$dateKey</td><td>$totalMoney[$dateKey]</td><td>$totalUser[$dateKey]</td><td>$totalTimes[$dateKey]</td><td>".round($totalMoney[$dateKey] / $totalUser[$dateKey],2)."</td><td>".$totalFirst[$dateKey]."</td>";
			if(!$allServerFlag){
				foreach ($payData as $server=>$data){
					$html .="<td>".$data[$dateKey]['total']."</td><td>".$data[$dateKey]['uniquePay']."</td><td>".$data[$dateKey]['totalPay']."</td><td>".round($data[$dateKey]['total'] / $data[$dateKey]['uniquePay'],2)."</td><td>".$payFirst[$server][$dateKey]."</td>";
				}
			}
			$html .="</tr>";
		}
		$html .= "</table><br><br><br></div>";
	//}
	
	
	//echo $html;
	//exit();
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
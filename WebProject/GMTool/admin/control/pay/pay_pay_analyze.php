<?php
!defined('IN_ADMIN') && exit('Access Denied');
if(!$_REQUEST['start'])
	$start = date("Y-m-d",time()-86400*7);
if(!$_REQUEST['end'])
	$end = date("Y-m-d",time());
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
if($_REQUEST['allServers']){
	$allServerFlag =true;
}

if($_REQUEST['event']=='output'){
	$start = $_REQUEST['start_time'];
	$end = $_REQUEST['end_time'];
	$startYmd=date('Ymd',strtotime($start));
	$endYmd=date('Ymd',strtotime($end));
	$whereSql='';
	if($currCountry&&$currCountry!='ALL'){
		$whereSql .=" and country='$currCountry' ";
	}
	if($currPf&&$currPf!='ALL'){
		$whereSql .=" and pf='$currPf' ";
	}elseif ($currPf=='ALL' && $_COOKIE['u']=='xiaomi'){
		$whereSql .= " and pf in('cn_360','cn_am','cn_anzhi','cn_baidu','cn_dangle','cn_ewan','cn_huawei','cn_kugou','cn_kupai','cn_lenovo','cn_mi','cn_mihy','cn_mzw','cn_nearme','cn_pps','cn_pptv','cn_sogou','cn_toutiao','cn_uc','cn_vivo','cn_wdj','cn_wyx','cn_youku','cn_oppo','cn_sy37','cn_mz','tencent') ";
	}
	$sids = implode(',', $selectServerids);
	$sql = "select sid, date,sum(payTotle) as payTotle,sum(payUsers) as payUsers,sum(payTimes) as payTimes,sum(dau) as dau,sum(firstPay) as firstPay from stat_allserver.pay_analyze_pf_country_referrer_new where sid in($sids) and date>=$startYmd and date <=$endYmd $whereSql GROUP BY date,sid;";
	$result = query_infobright($sql);
	$dates=array();
	$serverMap=array();
	foreach ($result['ret']['data'] as $row){
		$server = $row['sid'];
		$date = $row['date'];
		if(!in_array($date, $dates)){
			$dates[]=$date;
		}
		if(!in_array($server, $serverMap)){
			$serverMap[]=$server;
		}
		$payTotle[$date][$server]=$row['payTotle'];
		$payUsers[$date][$server]=$row['payUsers'];
		$payTimes[$date][$server]=$row['payTimes'];
		$dau[$date][$server]=$row['dau'];
		$firstPay[$date][$server]=$row['firstPay'];
	}
	rsort($dates);
	sort($serverMap);
	$title = array('20%'=>'日期','8%'=>'服','------','付费总值','------','DAU','------','付费用户数','------','付费次数','------','首充人数','------','付费渗透率','------','ARPU');
	
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
	foreach ($dates as $dateValue){
		foreach ($serverMap as $serverValue){
			$Excel->setCellValue($titleIndex[0].''.$row, $dateValue);
			$Excel->setCellValue($titleIndex[1].''.$row,$serverValue);
			$Excel->setCellValue($titleIndex[2].''.$row,'------');
			$Excel->setCellValue($titleIndex[3].''.$row,$payTotle[$dateValue][$serverValue]);
			$Excel->setCellValue($titleIndex[4].''.$row,'------');
			$Excel->setCellValue($titleIndex[5].''.$row,$dau[$dateValue][$serverValue]);
			$Excel->setCellValue($titleIndex[6].''.$row,'------');
			$Excel->setCellValue($titleIndex[7].''.$row,$payUsers[$dateValue][$serverValue]);
			$Excel->setCellValue($titleIndex[8].''.$row,'------');
			$Excel->setCellValue($titleIndex[9].''.$row,$payTimes[$dateValue][$serverValue]);
			$Excel->setCellValue($titleIndex[10].''.$row,'------');
			$Excel->setCellValue($titleIndex[11].''.$row,$firstPay[$dateValue][$serverValue]);
			$Excel->setCellValue($titleIndex[12].''.$row,'------');
			$Excel->setCellValue($titleIndex[13].''.$row,intval($payUsers[$dateValue][$serverValue]*10000/$dau[$dateValue][$serverValue] )/100 ."%");
			$Excel->setCellValue($titleIndex[14].''.$row,'------');
			$Excel->setCellValue($titleIndex[15].''.$row,intval($payTotle[$dateValue][$serverValue]*100/$payUsers[$dateValue][$serverValue] )/100);
			$row++;
		}
	}
	//filename
	$file_name = '支付分析统计';
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
	$start = $_REQUEST['start_time'];
	$end = $_REQUEST['end_time'];
	$startYmd=date('Ymd',strtotime($start));
	$endYmd=date('Ymd',strtotime($end));
	$whereSql='';
	if($currCountry&&$currCountry!='ALL'){
		$whereSql .=" and country='$currCountry' ";
	}
	if($currPf&&$currPf!='ALL'){
		$whereSql .=" and pf='$currPf' ";
	}elseif ($currPf=='ALL' && $_COOKIE['u']=='xiaomi'){
		$whereSql .= " and pf in('cn_360','cn_am','cn_anzhi','cn_baidu','cn_dangle','cn_ewan','cn_huawei','cn_kugou','cn_kupai','cn_lenovo','cn_mi','cn_mihy','cn_mzw','cn_nearme','cn_pps','cn_pptv','cn_sogou','cn_toutiao','cn_uc','cn_vivo','cn_wdj','cn_wyx','cn_youku','cn_oppo','cn_sy37','cn_mz','tencent') ";
	}
	$sids = implode(',', $selectServerids);
	$sql = "select sid, date,sum(payTotle) as payTotle,sum(payUsers) as payUsers,sum(payTimes) as payTimes,sum(dau) as dau,sum(firstPay) as firstPay from stat_allserver.pay_analyze_pf_country_referrer_new where sid in($sids) and date>=$startYmd and date <=$endYmd $whereSql GROUP BY date,sid;";
	$result = query_infobright($sql);
	$dates=array();
	$serverMap=array();
	foreach ($result['ret']['data'] as $row){
		$server = 's'.$row['sid'];
		$date = $row['date'];
		if(!in_array($date, $dates)){
			$dates[]=$row['date'];
		}
		if(!in_array($row['sid'], $serverMap)){
			$serverMap[]=$row['sid'];
		}
		$payTotle[$date][$server]=$row['payTotle'];
		$payUsers[$date][$server]=$row['payUsers'];
		$payTimes[$date][$server]=$row['payTimes'];
		$dau[$date][$server]=$row['dau'];
		$firstPay[$date][$server]=$row['firstPay'];
	}
	rsort($dates);
	rsort($serverMap);
	
	foreach ($dates as $dateValue) {
		$allPayTotal = array_sum($payTotle[$dateValue]);
		$payTotle[$dateValue]['合计'] = $allPayTotal;
		$allPayUsers = array_sum($payUsers[$dateValue]);
		$payUsers[$dateValue]['合计'] = $allPayUsers;
		$allPayTimes = array_sum($payTimes[$dateValue]);
		$payTimes[$dateValue]['合计'] = $allPayTimes;
		$allDau = array_sum($dau[$dateValue]);
		$dau[$dateValue]['合计'] = $allDau;
		$allFirstPay = array_sum($firstPay[$dateValue]);
		$firstPay[$dateValue]['合计'] = $allFirstPay;
	}
	$serverMap[]='合计';
	
	$title = array('4%'=>'日期','服','付费总值','DAU','付费用户数','付费次数','首充人数','付费渗透率','ARPU');

	//付费金额
	//付费用户数
	//付费次数
	//ARPU
	$html = "<div style='float:left;width:95%;height:auto;text-align:center;overflow-x:auto;overflow-y:auto;'><table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
	foreach ($title as $width=>$value){
		if(is_numeric($width)){
			$width = "2%";
		}
		$html .= "<th width=$width>" . $value . "</th>";
	}
	$html .= "</tr>";
	foreach ($dates as $date)
	{
		if($allServerFlag){
			$html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
			$html .= "<td>" . $date . "</td>";
			$html .= "<td>" . '合计' . "</td>";
			$html .= "<td>" . $payTotle[$date]['合计'] . "</td>";
			$html .= "<td>" . $dau[$date]['合计'] . "</td>";
			$html .= "<td>" . $payUsers[$date]['合计'] . "</td>";
			$html .= "<td>" . $payTimes[$date]['合计'] . "</td>";
			$html .= "<td>" . $firstPay[$date]['合计'] . "</td>";
			$html .= "<td>" . intval($payUsers[$date]['合计']*10000/$dau[$date]['合计'] )/100 ."%". "</td>";
			$html .= "<td>" . intval($payTotle[$date]['合计']*100/$payUsers[$date]['合计'] )/100 . "</td>";
			$html .= "</tr>";
			continue;
		}
		foreach ($serverMap as $serverKey){
			if($serverKey=='合计'){
				$html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff' style=".'"font-weight: bold; color: rgb(119, 125, 237);"'.">";
			}else{
				$html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
				$serverKey='s'.$serverKey;
			}
			$html .= "<td>" . $date . "</td>";
			$html .= "<td>" . $serverKey . "</td>";
			$html .= "<td>" . $payTotle[$date][$serverKey] . "</td>";
			$html .= "<td>" . $dau[$date][$serverKey] . "</td>";
			$html .= "<td>" . $payUsers[$date][$serverKey] . "</td>";
			$html .= "<td>" . $payTimes[$date][$serverKey] . "</td>";
			$html .= "<td>" . $firstPay[$date][$serverKey] . "</td>";
			$html .= "<td>" . intval($payUsers[$date][$serverKey]*10000/$dau[$date][$serverKey] )/100 ."%". "</td>";
			$html .= "<td>" . intval($payTotle[$date][$serverKey]*100/$payUsers[$date][$serverKey] )/100 . "</td>";
			$html .= "</tr>";
			
		}
		
	}
	$html .= "</table></div><br/>";
	/* echo $html;
	exit(); */
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
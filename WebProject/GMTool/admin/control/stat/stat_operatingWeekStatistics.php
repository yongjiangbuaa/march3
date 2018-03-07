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

if (!$_REQUEST['selectCountry']) {
	$currCountry[] = 'ALL';
}else{
	$currCountry = $_REQUEST['selectCountry'];
}
if (!$_REQUEST['selectPf']) {
	$currPf = '';
}else{
	$currPf = $_REQUEST['selectPf'];
	/*选择平台*/
}

$showData=false;
$alertHeader="";

if($_REQUEST['event']=='output'){
$sids = implode(',', $selectServerids);//把服务器字符串按照','劈开
	$whereSql=" where sid in ($sids) ";//定义条件 sid
	$whereSql1=" where sid in ($sids) ";//定义条件 sid
	$startDate = substr($_REQUEST['startDate'],0,10);
	$sDdate= date('Ymd',strtotime($startDate));
	$startweek=date("oW",strtotime($sDdate));
	$endDate = substr($_REQUEST['endDate'],0,10);
	$eDate =date('Ymd',strtotime($endDate)+86400);
	$endweek=date("oW",strtotime($eDate));
	$whereSql .= " and date >=$sDdate and date <= $eDate ";//wheresql拼接日期
	$whereSql1 .= " and week>=$startweek and week<= $endweek ";//wheresql拼接日期
	if($currCountry&&(!in_array('ALL', $currCountry))){
		$countries=implode("','", $currCountry);
		$whereSql .=" and country in('$countries') ";//wheresql拼接国家
		$whereSql1 .=" and country in('$countries') ";
	}
	$sql="select country,pf,week,sum(dau) s_dau,sum(reg) s_reg,sum(paid_dau) as paid_dau,sum(deviceDau) as deviceDau from stat_allserver.stat_dau_daily_pf_country_new_week $whereSql1 group by country,pf,week;";
	$result = query_infobright($sql);
	$eventAll=array();
	$pfData=array();
	$dateArray=array();
	$countryArray=array();
	foreach ($result['ret']['data'] as $curRow){
		$country=strtoupper($curRow['country']);
		$pf=$curRow['pf'];
		$dateIndex=$curRow['week'];
		if(!in_array($dateIndex, $dateArray)){
			$dateArray[]=$dateIndex;
		}
		if(!in_array($country, $countryArray)){
			$countryArray[]=$country;
		}
		
		if(in_array('ALL', $currCountry)){
			$country='合计';
		}
	
		$eventAll[$dateIndex][$country]['dau'] += $curRow['s_dau'];
	
		$eventAll[$dateIndex][$country]['paid_dau'] += $curRow['paid_dau'];
			
		$eventAll[$dateIndex][$country]['deviceDau'] += $curRow['deviceDau'];
			
		$eventAll[$dateIndex][$country]['sdau'] += $curRow['s_dau'] - $curRow['s_reg'];
	
		$eventAll[$dateIndex][$country]['reg'] += $curRow['s_reg'];
	
		if (in_array($pf, $currPf) && !empty($pf)){
			$pfData[$dateIndex][$country][$pf]['dau'] += $curRow['s_dau'];
				
			$pfData[$dateIndex][$country][$pf]['paid_dau'] += $curRow['paid_dau'];
	
			$pfData[$dateIndex][$country][$pf]['deviceDau'] += $curRow['deviceDau'];
	
			$pfData[$dateIndex][$country][$pf]['sdau'] += $curRow['s_dau'] - $curRow['s_reg'];
				
			$pfData[$dateIndex][$country][$pf]['reg'] += $curRow['s_reg'];
		}
	}
	
	$sql = "select country,pf,week,sum(payTotle) as payTotle,sum(payUsers) as payUsers,sum(payTimes) as payTimes,sum(dau) as dau,sum(firstPay) as firstPay from stat_allserver.pay_analyze_pf_country_week $whereSql1 GROUP BY country,pf,week;";
	$result = query_infobright($sql);
	foreach ($result['ret']['data'] as $row){
		$country=strtoupper($row['country']);
		$pf=$row['pf'];
		$dateIndex=$row['week'];
		if(!in_array($dateIndex, $dateArray)){
			$dateArray[]=$dateIndex;
		}
		if(!in_array($country, $countryArray)){
			$countryArray[]=$country;
		}
		
		if(in_array('ALL', $currCountry)){
			$country='合计';
		}
	
		$eventAll[$dateIndex][$country]['payTotle'] += $row['payTotle'];
	
		$eventAll[$dateIndex][$country]['payUsers'] += $row['payUsers'];
	
		$eventAll[$dateIndex][$country]['payTimes'] += $row['payTimes'];
	
		$eventAll[$dateIndex][$country]['firstPay'] += $row['firstPay'];
	
		if (in_array($pf, $currPf) && !empty($pf)){
			$pfData[$dateIndex][$country][$pf]['payTotle'] += $row['payTotle'];
	
			$pfData[$dateIndex][$country][$pf]['payUsers'] += $row['payUsers'];
				
			$pfData[$dateIndex][$country][$pf]['payTimes'] += $row['payTimes'];
				
			$pfData[$dateIndex][$country][$pf]['firstPay'] += $row['firstPay'];
		}
	}
	
	$dayArr = array(1,3,7,30);
	foreach ($dayArr as $day) {
		$rfields[] = "sum(".'r'.$day.") as ".'r'.$day;
	}
	/* $fields = implode(',', $rfields);
	$sql = "select country,pf,date,sum(reg_all) regAll,$fields from stat_allserver.stat_retention_daily_pf_country_new $whereSql and reg_all>0  group by country,pf,date;";
	$ret = query_infobright($sql);
	foreach ($ret['ret']['data'] as $row) {
		$country=strtoupper($row['country']);
		$pf=$row['pf'];
		$dateIndesx=$row['date'];
		if(!in_array($dateIndesx, $dateArray)){
			$dateArray[]=$dateIndesx;
		}
		if(!in_array($country, $countryArray)){
			$countryArray[]=$country;
		}
		
		if(in_array('ALL', $currCountry)){
			$country='合计';
		}
	
		foreach ($dayArr as $day) {
			$count = $row['r'.$day]?$row['r'.$day]:0;
			$eventAll[$dateIndesx][$country][$day]['count'] += $count;
			if (in_array($pf, $currPf)){
				$pfData[$dateIndesx][$country][$pf][$day]['count'] += $count;
			}
		}
	}
	 */
	if(in_array('ALL', $currCountry)){
		unset($countryArray);
		$countryArray[]='合计';
	}
	
	foreach ($eventAll as $dateKey=>$countryVal){
		foreach ($countryVal as $countryKey=>$value){
			$eventAll[$dateKey][$countryKey]['filter']=intval($eventAll[$dateKey][$countryKey]['payUsers']*10000/$eventAll[$dateKey][$countryKey]['dau'] )/100 ."%";
			$eventAll[$dateKey][$countryKey]['ARPU']=intval($eventAll[$dateKey][$countryKey]['payTotle']*100/$eventAll[$dateKey][$countryKey]['payUsers'] )/100;
			foreach ($currPf as $pfVal){
				$pfData[$dateKey][$countryKey][$pfVal]['filter']=intval($pfData[$dateKey][$countryKey][$pfVal]['payUsers']*10000/$pfData[$dateKey][$countryKey][$pfVal]['dau'] )/100 ."%";
				$pfData[$dateKey][$countryKey][$pfVal]['ARPU']=intval($pfData[$dateKey][$countryKey][$pfVal]['payTotle']*100/$pfData[$dateKey][$countryKey][$pfVal]['payUsers'] )/100;
			}
			foreach ($dayArr as $day) {
				$eventAll[$dateKey][$countryKey][$day]['rate']=intval($eventAll[$dateKey][$countryKey][$day]['count']/$eventAll[$dateKey][$countryKey]['reg']*10000)/100;
				foreach ($currPf as $pfVal){
					$pfData[$dateKey][$countryKey][$pfVal][$day]['rate']=intval($pfData[$dateKey][$countryKey][$pfVal][$day]['count']*10000/$pfData[$dateKey][$countryKey][$pfVal]['reg'] )/100;
				}
			}
		}
	}
	
	foreach ($currPf as $pf){
		$strPf .=$pf.',';
	}
	$titleStr='开始日期,结束日期,国家,日活跃,'.$strPf.'机器码DAU,'.$strPf.'付费DAU,'.$strPf.'老玩家,'.$strPf.'新注册,'.$strPf.'付费总值,'.$strPf.'付费用户,'.$strPf.'付费次数,'.$strPf.'首充人数,'.$strPf.'渗透率,'.$strPf.'ARPU,'.$strPf;
	$titleStr=rtrim($titleStr,',');
	$title=explode(',', $titleStr);
	
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
// 	$titleIndex = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');
	//set title
	$Excel = $objPHPExcel->setActiveSheetIndex(0);
	$row = 1;
	//set data
	$line = 0;
	foreach ($title as $width=>$value){
		if(strlen($value) != mb_strlen($value)){
			$width = (strlen($value) + iconv_strlen($value))* 1.1 * 8.26/22;
			$objPHPExcel->getActiveSheet()->getColumnDimension(getNameFromNumber($line))->setWidth($width);
		}else{
			$objPHPExcel->getActiveSheet()->getColumnDimension(getNameFromNumber($line))->setAutoSize(true);
		}
		$Excel->setCellValue(getNameFromNumber($line++).''.$row,$value);
	}
	$row++;
	function getTimeOfWeek($year=2008,$week=32,$dir=0)
	{
		$wday=4-date('w',mktime(0,0,0,1,4,$year))+1;
		return strtotime(sprintf("+%d weeks",$week-($dir?0:1)),mktime(0,0,0,1,$wday,$year))-($dir?1:0);
	}
	
	foreach ($countryArray as $counVal){
		foreach ($dateArray as $dateVal){
			$starNewDateE[$dateval]=date("Y-m-d",getTimeOfWeek(substr($dateVal,0, 4),substr($dateVal,4, 2)-1,0));
			$endNewDateE[$dateval]=date("Y-m-d",getTimeOfWeek(substr($dateVal,0, 4),substr($dateVal,4, 2)-1,1));
			$Excel->setCellValue(getNameFromNumber(0).''.$row, $starNewDateE[$dateval]);
			$Excel->setCellValue(getNameFromNumber(1).''.$row, $endNewDateE[$dateval]);
			$Excel->setCellValue(getNameFromNumber(2).''.$row,$counVal);
			$Excel->setCellValue(getNameFromNumber(3).''.$row,$eventAll[$dateVal][$counVal]['dau']);
			$length=count($currPf);
			for ($i=0;$i<$length;$i++){
				$Excel->setCellValue(getNameFromNumber(4+$i).''.$row,$pfData[$dateVal][$counVal][$currPf[$i]]['dau']);
			}
			
			$Excel->setCellValue(getNameFromNumber(4+$length).''.$row,$eventAll[$dateVal][$counVal]['deviceDau']);
			$length=count($currPf);
			for ($i=0;$i<$length;$i++){
				$Excel->setCellValue(getNameFromNumber(5+$length+$i).''.$row,$pfData[$dateVal][$counVal][$currPf[$i]]['deviceDau']);
			}
			
			$Excel->setCellValue(getNameFromNumber(5+$length*2).''.$row,$eventAll[$dateVal][$counVal]['paid_dau']);
			$length=count($currPf);
			for ($i=0;$i<$length;$i++){
				$Excel->setCellValue(getNameFromNumber(6+$length*2+$i).''.$row,$pfData[$dateVal][$counVal][$currPf[$i]]['paid_dau']);
			}
			
			$Excel->setCellValue(getNameFromNumber(6+$length*3).''.$row,$eventAll[$dateVal][$counVal]['sdau']);
			$length=count($currPf);
			for ($i=0;$i<$length;$i++){
				$Excel->setCellValue(getNameFromNumber(7+$length*3+$i).''.$row,$pfData[$dateVal][$counVal][$currPf[$i]]['sdau']);
			}
			
			$Excel->setCellValue(getNameFromNumber(7+$length*4).''.$row,$eventAll[$dateVal][$counVal]['reg']);
			$length=count($currPf);
			for ($i=0;$i<$length;$i++){
				$Excel->setCellValue(getNameFromNumber(8+$length*4+$i).''.$row,$pfData[$dateVal][$counVal][$currPf[$i]]['reg']);
			}
			
			$Excel->setCellValue(getNameFromNumber(8+$length*5).''.$row,$eventAll[$dateVal][$counVal]['payTotle']);
			$length=count($currPf);
			for ($i=0;$i<$length;$i++){
				$Excel->setCellValue(getNameFromNumber(9+$length*5+$i).''.$row,$pfData[$dateVal][$counVal][$currPf[$i]]['payTotle']);
			}
			
			$Excel->setCellValue(getNameFromNumber(9+$length*6).''.$row,$eventAll[$dateVal][$counVal]['payUsers']);
			$length=count($currPf);
			for ($i=0;$i<$length;$i++){
				$Excel->setCellValue(getNameFromNumber(10+$length*6+$i).''.$row,$pfData[$dateVal][$counVal][$currPf[$i]]['payUsers']);
			}
			

			$Excel->setCellValue(getNameFromNumber(10+$length*7).''.$row,$eventAll[$dateVal][$counVal]['payTimes']);
			$length=count($currPf);
			for ($i=0;$i<$length;$i++){
				$Excel->setCellValue(getNameFromNumber(11+$length*7+$i).''.$row,$pfData[$dateVal][$counVal][$currPf[$i]]['payTimes']);
			}
			
			$Excel->setCellValue(getNameFromNumber(11+$length*8).''.$row,$eventAll[$dateVal][$counVal]['firstPay']);
			$length=count($currPf);
			for ($i=0;$i<$length;$i++){
				$Excel->setCellValue(getNameFromNumber(12+$length*8+$i).''.$row,$pfData[$dateVal][$counVal][$currPf[$i]]['firstPay']);
			}
			
			$Excel->setCellValue(getNameFromNumber(12+$length*9).''.$row,$eventAll[$dateVal][$counVal]['filter']);
			$length=count($currPf);
			for ($i=0;$i<$length;$i++){
				$Excel->setCellValue(getNameFromNumber(13+$length*9+$i).''.$row,$pfData[$dateVal][$counVal][$currPf[$i]]['filter']);
			}
			
			$Excel->setCellValue(getNameFromNumber(13+$length*10).''.$row,$eventAll[$dateVal][$counVal]['ARPU']);
			$length=count($currPf);
			for ($i=0;$i<$length;$i++){
				$Excel->setCellValue(getNameFromNumber(14+$length*10+$i).''.$row,$pfData[$dateVal][$counVal][$currPf[$i]]['ARPU']);
			}
			
			/* $Excel->setCellValue(getNameFromNumber(13+$length*11).''.$row,$eventAll[$dateVal][$counVal][1]['rate']);
			$length=count($currPf);
			for ($i=0;$i<$length;$i++){
				$Excel->setCellValue(getNameFromNumber(14+$length*11+$i).''.$row,$pfData[$dateVal][$counVal][$currPf[$i]][1]['rate']);
			}
			
			$Excel->setCellValue(getNameFromNumber(14+$length*12).''.$row,$eventAll[$dateVal][$counVal][3]['rate']);
			$length=count($currPf);
			for ($i=0;$i<$length;$i++){
				$Excel->setCellValue(getNameFromNumber(15+$length*12+$i).''.$row,$pfData[$dateVal][$counVal][$currPf[$i]][3]['rate']);
			}
			
			$Excel->setCellValue(getNameFromNumber(15+$length*13).''.$row,$eventAll[$dateVal][$counVal][7]['rate']);
			$length=count($currPf);
			for ($i=0;$i<$length;$i++){
				$Excel->setCellValue(getNameFromNumber(16+$length*13+$i).''.$row,$pfData[$dateVal][$counVal][$currPf[$i]][7]['rate']);
		 	}
			
			$Excel->setCellValue(getNameFromNumber(16+$length*14).''.$row,$eventAll[$dateVal][$counVal][30]['rate']);
			$length=count($currPf);
			for ($i=0;$i<$length;$i++){
				$Excel->setCellValue(getNameFromNumber(17+$length*14+$i).''.$row,$pfData[$dateVal][$counVal][$currPf[$i]][30]['rate']);
			} */
			
			$row++;
		}
	}
	//filename
	$file_name = '运营数据统计(周)';
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

function getNameFromNumber($num) {
	$numeric = $num % 26;
	$letter = chr(65 + $numeric);
	$num2 = intval($num / 26);
	if ($num2 > 0) {
		return getNameFromNumber($num2 - 1) . $letter;
	} else {
		return $letter;
	}
}

if($_REQUEST['event']=='user'){
	$sids = implode(',', $selectServerids);//把服务器字符串按照','劈开
	$whereSql=" where sid in ($sids) ";//定义条件 sid
	$whereSql1=" where sid in ($sids) ";//定义条件 sid
	$startDate = substr($_REQUEST['startDate'],0,10);
	$sDdate= date('Ymd',strtotime($startDate));
	$startweek=date("oW",strtotime($sDdate));
	$endDate = substr($_REQUEST['endDate'],0,10);
	$eDate =date('Ymd',strtotime($endDate)+86400);
	$endweek=date("oW",strtotime($eDate));
	$whereSql .= " and date >=$sDdate and date <= $eDate ";//wheresql拼接日期
	$whereSql1 .= " and week>=$startweek and week<= $endweek ";//wheresql拼接日期
	if($currCountry&&(!in_array('ALL', $currCountry))){
		$countries=implode("','", $currCountry);
		$whereSql .=" and country in('$countries') ";//wheresql拼接国家
		$whereSql1 .=" and country in('$countries') ";
	}
	$sql="select country,pf,week,sum(dau) s_dau,sum(reg) s_reg,sum(paid_dau) as paid_dau,sum(deviceDau) as deviceDau from stat_allserver.stat_dau_daily_pf_country_new_week $whereSql1 group by country,pf,week;";
	$result = query_infobright($sql);//获得结果集
	//echo $sql;
	//print_r($result);
	$eventAll=array();
	$pfData=array();//平台数组
	$dateArray=array();//时间数组
	$countryArray=array();//国家数组
	foreach ($result['ret']['data'] as $curRow){
		$country=strtoupper($curRow['country']);//把字符串转换成大写,一条数据的国家
		$pf=$curRow['pf'];//平台
		$dateIndex=$curRow['week'];//日期
		if(!in_array($dateIndex, $dateArray)){
			$dateArray[]=$dateIndex;
		}
		if(!in_array($country, $countryArray)){
			$countryArray[]=$country;
		}
		
		if(in_array('ALL', $currCountry)){
			$country='合计';
		}
		
		$eventAll[$dateIndex][$country]['dau'] += $curRow['s_dau'];
		
		$eventAll[$dateIndex][$country]['paid_dau'] += $curRow['paid_dau'];
			
		$eventAll[$dateIndex][$country]['deviceDau'] += $curRow['deviceDau'];
			
		$eventAll[$dateIndex][$country]['sdau'] += $curRow['s_dau'] - $curRow['s_reg'];
		
		$eventAll[$dateIndex][$country]['reg'] += $curRow['s_reg'];
		
		if (in_array($pf, $currPf) && !empty($pf)){
			$pfData[$dateIndex][$country][$pf]['dau'] += $curRow['s_dau'];
			
			$pfData[$dateIndex][$country][$pf]['paid_dau'] += $curRow['paid_dau'];
				
			$pfData[$dateIndex][$country][$pf]['deviceDau'] += $curRow['deviceDau'];
				
			$pfData[$dateIndex][$country][$pf]['sdau'] += $curRow['s_dau'] - $curRow['s_reg'];
			
			$pfData[$dateIndex][$country][$pf]['reg'] += $curRow['s_reg'];
		}
	}

	$sql = "select country,pf,week,sum(payTotle) as payTotle,sum(payUsers) as payUsers,sum(payTimes) as payTimes,sum(dau) as dau,sum(firstPay) as firstPay from stat_allserver.pay_analyze_pf_country_week $whereSql1 GROUP BY country,pf,week;";
	$result = query_infobright($sql);
//	print_r($result);
	foreach ($result['ret']['data'] as $row){
		$country=strtoupper($row['country']);
		$pf=$row['pf'];
		$dateIndex=$row['week'];
		if(!in_array($dateIndex, $dateArray)){
			$dateArray[]=$dateIndex;
		}
		if(!in_array($country, $countryArray)){
			$countryArray[]=$country;
		}
		
		if(in_array('ALL', $currCountry)){
			$country='合计';
		}
		
		$eventAll[$dateIndex][$country]['payTotle'] += $row['payTotle'];
		
		$eventAll[$dateIndex][$country]['payUsers'] += $row['payUsers'];
		
		$eventAll[$dateIndex][$country]['payTimes'] += $row['payTimes'];
		
		$eventAll[$dateIndex][$country]['firstPay'] += $row['firstPay'];
		
		if (in_array($pf, $currPf) && !empty($pf)){
			$pfData[$dateIndex][$country][$pf]['payTotle'] += $row['payTotle'];
				
			$pfData[$dateIndex][$country][$pf]['payUsers'] += $row['payUsers'];
			
			$pfData[$dateIndex][$country][$pf]['payTimes'] += $row['payTimes'];
			
			$pfData[$dateIndex][$country][$pf]['firstPay'] += $row['firstPay'];
		}
	}
	
	$dayArr = array(1,3,7,30);
	foreach ($dayArr as $day) {
		$rfields[] = "sum(".'r'.$day.") as ".'r'.$day;
	}
	$fields = implode(',', $rfields);
	$sql = "select country,pf,date,sum(reg_all) regAll,$fields from stat_allserver.stat_retention_daily_pf_country_new $whereSql1 and reg_all>0  group by country,pf,date;";
	$ret = query_infobright($sql);
	foreach ($ret['ret']['data'] as $row) {
		$country=strtoupper($row['country']);
		$pf=$row['pf'];
		$dateIndex=$row['date'];
		if(!in_array($dateIndex, $dateArray)){
			$dateArray[]=$dateIndex;
		}
		if(!in_array($country, $countryArray)){
			$countryArray[]=$country;
		}
		
		if(in_array('ALL', $currCountry)){
			$country='合计';
		}
		
		foreach ($dayArr as $day) {
			$count = $row['r'.$day]?$row['r'.$day]:0;
			$eventAll[$dateIndex][$country][$day]['count'] += $count;
			if (in_array($pf, $currPf) && !empty($pf)){
				$pfData[$dateIndex][$country][$pf][$day]['count'] += $count;
			}
		}
	}
	if(in_array('ALL', $currCountry)){
		unset($countryArray);
		$countryArray[]='合计';
	}
	
	function getTimeOfWeek($year=2008,$week=32,$dir=0)
	{
		$wday=4-date('w',mktime(0,0,0,1,4,$year))+1;
		return strtotime(sprintf("+%d weeks",$week-($dir?0:1)),mktime(0,0,0,1,$wday,$year))-($dir?1:0);
	}
	
	foreach ($eventAll as $dateKey=>$countryVal){
		
		foreach ($countryVal as $countryKey=>$value){
			$starNewDate[$dateKey]=date("Y-m-d",getTimeOfWeek(substr($dateKey,0, 4),substr($dateKey,4, 2)-1,0));
			$endNewDate[$dateKey]=date("Y-m-d",getTimeOfWeek(substr($dateKey,0, 4),substr($dateKey,4, 2)-1,1));
			$eventAll[$dateKey][$countryKey]['filter']=intval($eventAll[$dateKey][$countryKey]['payUsers']*10000/$eventAll[$dateKey][$countryKey]['dau'] )/100 ."%";
			$eventAll[$dateKey][$countryKey]['ARPU']=intval($eventAll[$dateKey][$countryKey]['payTotle']*100/$eventAll[$dateKey][$countryKey]['payUsers'] )/100;
			foreach ($currPf as $pfVal){
				
				$pfData[$dateKey][$countryKey][$pfVal]['filter']=intval($pfData[$dateKey][$countryKey][$pfVal]['payUsers']*10000/$pfData[$dateKey][$countryKey][$pfVal]['dau'] )/100 ."%";
				$pfData[$dateKey][$countryKey][$pfVal]['ARPU']=intval($pfData[$dateKey][$countryKey][$pfVal]['payTotle']*100/$pfData[$dateKey][$countryKey][$pfVal]['payUsers'] )/100;
			}
			foreach ($dayArr as $day) {
				$eventAll[$dateKey][$countryKey][$day]['rate']=intval($eventAll[$dateKey][$countryKey][$day]['count']/$eventAll[$dateKey][$countryKey]['reg']*10000)/100;
				foreach ($currPf as $pfVal){
					$pfData[$dateKey][$countryKey][$pfVal][$day]['rate']=intval($pfData[$dateKey][$countryKey][$pfVal][$day]['count']*10000/$pfData[$dateKey][$countryKey][$pfVal]['reg'] )/100;
				}
			}
		}
	}
	if ($eventAll){
		rsort($dateArray);
		$showData=true;
	}else {
		$alertHeader='没有查询到相关数据';
	}
	
}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>

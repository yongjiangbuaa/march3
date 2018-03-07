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
}
$showData=false;
$alertHeader="";

if($_REQUEST['event']=='output'){
	$sids = implode(',', $selectServerids);
	$whereSql=" where sid in ($sids) ";
	$startDate = substr($_REQUEST['startDate'],0,10);
	$sDdate= date('Ymd',strtotime($startDate));
	$endDate = substr($_REQUEST['endDate'],0,10);
	$eDate =date('Ymd',strtotime($endDate)+86400);
	$whereSql .= " and date >=$sDdate and date <= $eDate ";
	if($currCountry&&(!in_array('ALL', $currCountry))){
		$countries=implode("','", $currCountry);
		$whereSql .=" and country in('$countries') ";
	}
	/* 	$sql="select country,pf,date,sum(dau) s_dau,sum(reg) s_reg,sum(paid_dau) as paid_dau,sum(deviceDau) as deviceDau from stat_allserver.stat_dau_daily_pf_country_new $whereSql group by country,pf,date;"; */
	$sql="select country,pf,date,sum(dau) s_dau,sum(reg) s_reg from stat_allserver.stat_dau_daily_pf_country_new $whereSql group by country,pf,date;";
	$result = query_infobright($sql);
	$eventAll=array();
	$pfData=array();
	$dateArray=array();
	$countryArray=array();
	foreach ($result['ret']['data'] as $curRow){
		$country=strtoupper($curRow['country']);
		$pf=$curRow['pf'];
		$dateIndesx=$curRow['date'];
		if(!in_array($dateIndesx, $dateArray)){
			$dateArray[]=$dateIndesx;
		}
		if(!in_array($country, $countryArray)){
			$countryArray[]=$country;
		}

		if(in_array('ALL', $currCountry)){
			$country='合计';
		}

		$eventAll[$dateIndesx][$country]['dau'] += $curRow['s_dau'];

		$eventAll[$dateIndesx][$country]['paid_dau'] += $curRow['paid_dau'];

		/* $eventAll[$dateIndesx][$country]['deviceDau'] += $curRow['deviceDau']; */

		$eventAll[$dateIndesx][$country]['sdau'] += $curRow['s_dau'] - $curRow['s_reg'];

		$eventAll[$dateIndesx][$country]['reg'] += $curRow['s_reg'];

		if (in_array($pf, $currPf)){
			$pfData[$dateIndesx][$country][$pf]['dau'] += $curRow['s_dau'];/**老用户DAU*/
				
			$pfData[$dateIndesx][$country][$pf]['paid_dau'] += $curRow['paid_dau'];

			/* $pfData[$dateIndesx][$country][$pf]['deviceDau'] += $curRow['deviceDau']; */

			$pfData[$dateIndesx][$country][$pf]['sdau'] += $curRow['s_dau'] - $curRow['s_reg'];

			$pfData[$dateIndesx][$country][$pf]['reg'] += $curRow['s_reg'];
		}
	}
	$sql = "select country,pf,date,sum(payTotle) as payTotle,sum(payUsers) as payUsers,sum(firstPay) as firstPay from stat_allserver.pay_analyze_pf_country $whereSql GROUP BY country,pf,date;";
	/* $sql = "select country,pf,date,sum(payTotle) as payTotle,sum(payUsers) as payUsers,sum(payTimes) as payTimes,sum(dau) as dau,sum(firstPay) as firstPay from stat_allserver.pay_analyze_pf_country $whereSql GROUP BY country,pf,date;"; */
	$result = query_infobright($sql);
	foreach ($result['ret']['data'] as $row){
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
		$eventAll[$dateIndesx][$country]['payTotle'] += $row['payTotle'];
		$eventAll[$dateIndesx][$country]['firstPay'] += $row['firstPay'];
		$eventAll[$dateIndesx][$country]['payUsers'] += $row['payUsers'];

		if (in_array($pf, $currPf)){

			$pfData[$dateIndesx][$country][$pf]['payTotle'] += $row['payTotle'];
			$pfData[$dateIndesx][$country][$pf]['firstPay'] += $row['firstPay'];
			$pfData[$dateIndesx][$country][$pf]['payUsers'] += $row['payUsers'];
		}
	}
	/*自己添加的*/
	/**
	 * regDevice:新注册设备数
	 * firstDayPay(新注册当日付费人数)
	 * oldPayDAU 老付费玩儿家当日登陆
	 * newTotalPay 首冲付费总额
	 *
	 * 新注册付费率（新注册付费人数/新注册用户）， 	firstDayPayRate=(firstDayPay/reg);
	 * 老玩家首冲人数，(首冲人数-新注册付费人数) 	oldUserFirstPay=(firstPay-firstDayPay)
	 * 老玩家首冲付费率(老玩家首冲人数/老用户dau)，	oldUserFirstPayRate=(oldUserFirstPay/sdau)
	 * 老付费玩家付费，(总付费-首冲付费)			oldPayNewPay=(payTotle-newTotalPay)
	 * 老付费玩家(总付费用户数-首充人数)			oldPayUser=(payUser-firstPay)
	 * 老玩家付费率(老付费玩家付费人数/老付费玩家登录) 	oldUserPayRate=(oldPayUser/oldPayDAU)
	 *
	 */
	$sql = "select country,pf,date,sum(regDevice) as regDevice,sum(firstDayPay) as firstDayPay,sum(oldPayDAU) as oldPayDAU,sum(newTotalPay) as newTotalPay from stat_allserver.pay_ratio_analyze_pf_country_referrer_appVersion $whereSql GROUP BY country,pf,date;";
	$result = query_infobright($sql);
	foreach ($result['ret']['data'] as $row){
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
		$eventAll[$dateIndesx][$country]['regDevice'] += $row['regDevice'];/*新注册设备数*/
		$eventAll[$dateIndesx][$country]['firstDayPay'] += $row['firstDayPay'];/*新注册当日付费人数*/
		$eventAll[$dateIndesx][$country]['newTotalPay'] += $row['newTotalPay'];/*首冲付费总额*/
		$eventAll[$dateIndesx][$country]['oldPayDAU'] += $row['oldPayDAU'];/*老付费玩儿家当日登陆*/

		if (in_array($pf, $currPf)){
			$pfData[$dateIndesx][$country][$pf]['regDevice'] += $row['regDevice'];
			$pfData[$dateIndesx][$country][$pf]['firstDayPay'] += $row['firstDayPay'];
			$pfData[$dateIndesx][$country][$pf]['newTotalPay'] += $row['newTotalPay'];
			$pfData[$dateIndesx][$country][$pf]['oldPayDAU'] += $row['oldPayDAU'];
		}
	}

	if(in_array('ALL', $currCountry)){
		unset($countryArray);
		$countryArray[]='合计';
	}
	foreach ($eventAll as $dateKey=>$countryVal){
		foreach ($countryVal as $countryKey=>$value){
				
			$eventAll[$dateKey][$countryKey]['firstDayPayRate']=intval($eventAll[$dateKey][$countryKey]['firstDayPay']*10000/$eventAll[$dateKey][$countryKey]['reg'] )/100 ."%";
			$eventAll[$dateKey][$countryKey]['oldUserFirstPay']=$eventAll[$dateKey][$countryKey]['firstPay']-$eventAll[$dateKey][$countryKey]['firstDayPay'] ;
			$eventAll[$dateKey][$countryKey]['oldUserFirstPayRate']=intval($eventAll[$dateKey][$countryKey]['oldUserFirstPay']*10000/$eventAll[$dateKey][$country]['sdau'])/100 ."%";
			$eventAll[$dateKey][$countryKey]['oldPayNewPay']=$eventAll[$dateKey][$country]['payTotle'] -$eventAll[$dateKey][$country]['newTotalPay'];
			$eventAll[$dateKey][$countryKey]['oldPayUser']=$eventAll[$dateKey][$countryKey]['payUsers']-$eventAll[$dateKey][$countryKey]['firstPay'];
			$eventAll[$dateKey][$countryKey]['oldUserPayRate']=intval($eventAll[$dateKey][$countryKey]['oldPayUser']*10000/$eventAll[$dateKey][$countryKey]['oldPayDAU'] )/100  ."%";
				
			foreach ($currPf as $pfVal){
				$pfData[$dateKey][$countryKey][$pfVal]['firstDayPayRate']=intval($pfData[$dateKey][$countryKey][$pfVal][$pfVal]['firstDayPay']*10000/$pfData[$dateKey][$countryKey][$pfVal]['reg'] )/100  ."%";
				$pfData[$dateKey][$countryKey][$pfVal]['oldUserFirstPay']=$pfData[$dateKey][$countryKey][$pfVal]['firstPay']-$pfData[$dateKey][$countryKey][$pfVal]['firstDayPay'];
				$pfData[$dateKey][$countryKey][$pfVal]['oldUserFirstPayRate']=intval($pfData[$dateKey][$countryKey][$pfVal]['oldUserFirstPay']*10000/$pfData[$dateKey][$country][$pfVal]['sdau']) /100 ."%";
				$pfData[$dateKey][$countryKey][$pfVal]['oldPayNewPay']=$pfData[$dateKey][$country][$pfVal]['payTotle']-$pfData[$dateKey][$country][$pf][$pfVal]['newTotalPay'];
				$pfData[$dateKey][$countryKey][$pfVal]['oldPayUser']=$pfData[$dateKey][$countryKey][$pfVal]['payUsers']-$pfData[$dateKey][$countryKey][$pfVal]['firstPay'] ;
				$pfData[$dateKey][$countryKey][$pfVal]['oldUserPayRate']=intval($pfData[$dateKey][$countryKey][$pfVal]['oldPayUser']*10000/$pfData[$dateKey][$countryKey][$pfVal]['oldPayDAU'] ) /100 ."%";
					
				/* $pfData[$dateKey][$countryKey][$pfVal]['filter']=intval($pfData[$dateKey][$countryKey][$pfVal]['payUsers']*10000/$pfData[$dateKey][$countryKey][$pfVal]['dau'] )/100 ."%";
				 $pfData[$dateKey][$countryKey][$pfVal]['ARPU']=intval($pfData[$dateKey][$countryKey][$pfVal]['payTotle']*100/$pfData[$dateKey][$countryKey][$pfVal]['payUsers'] )/100; */
			}
				
		}
	}
	foreach ($currPf as $pf){
		$strPf .=$pf.',';
	}
	$titleStr='日期,国家,日活跃,'.$strPf.'老用户DAU,'.$strPf.'新注册用户数,'.$strPf.'新注册设备数,'.$strPf.'新注册当日付费人数,'.$strPf.'新注册当日付费率,'.$strPf.'老玩儿家首充人数,'.$strPf.'老玩儿家首冲付费率,'.$strPf.'首充人数,'.$strPf.'老付费玩儿家当日登陆,'.$strPf.'老付费玩儿家付费,'.$strPf.'老付费玩儿家付费率,'.$strPf;
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
	foreach ($countryArray as $counVal){
		foreach ($dateArray as $dateVal){
			$Excel->setCellValue(getNameFromNumber(0).''.$row, $dateVal);
			$Excel->setCellValue(getNameFromNumber(1).''.$row,$counVal);
			$Excel->setCellValue(getNameFromNumber(2).''.$row,$eventAll[$dateVal][$counVal]['dau']);
			$length=count($currPf);
			for ($i=0;$i<$length;$i++){
				$Excel->setCellValue(getNameFromNumber(3+$i).''.$row,$pfData[$dateVal][$counVal][$currPf[$i]]['dau']);
			}
				
			$Excel->setCellValue(getNameFromNumber(3+$length).''.$row,$eventAll[$dateVal][$counVal]['sdau']);
			$length=count($currPf);
			for ($i=0;$i<$length;$i++){
				$Excel->setCellValue(getNameFromNumber(4+$length+$i).''.$row,$pfData[$dateVal][$counVal][$currPf[$i]]['sdau']);
			}
				
			$Excel->setCellValue(getNameFromNumber(4+$length*2).''.$row,$eventAll[$dateVal][$counVal]['reg']);
			$length=count($currPf);
			for ($i=0;$i<$length;$i++){
				$Excel->setCellValue(getNameFromNumber(5+$length*2+$i).''.$row,$pfData[$dateVal][$counVal][$currPf[$i]]['reg']);
			}
				
			$Excel->setCellValue(getNameFromNumber(5+$length*3).''.$row,$eventAll[$dateVal][$counVal]['regDevice']);
			$length=count($currPf);
			for ($i=0;$i<$length;$i++){
				$Excel->setCellValue(getNameFromNumber(6+$length*3+$i).''.$row,$pfData[$dateVal][$counVal][$currPf[$i]]['regDevice']);
			}
				
			$Excel->setCellValue(getNameFromNumber(6+$length*4).''.$row,$eventAll[$dateVal][$counVal]['firstDayPay']);
			$length=count($currPf);
			for ($i=0;$i<$length;$i++){
				$Excel->setCellValue(getNameFromNumber(7+$length*4+$i).''.$row,$pfData[$dateVal][$counVal][$currPf[$i]]['firstDayPay']);
			}
				
			$Excel->setCellValue(getNameFromNumber(7+$length*5).''.$row,$eventAll[$dateVal][$counVal]['firstDayPayRate']);
			$length=count($currPf);
			for ($i=0;$i<$length;$i++){
				$Excel->setCellValue(getNameFromNumber(8+$length*5+$i).''.$row,$pfData[$dateVal][$counVal][$currPf[$i]]['firstDayPayRate']);
			}
				
			$Excel->setCellValue(getNameFromNumber(8+$length*6).''.$row,$eventAll[$dateVal][$counVal]['oldUserFirstPay']);
			$length=count($currPf);
			for ($i=0;$i<$length;$i++){
				$Excel->setCellValue(getNameFromNumber(9+$length*6+$i).''.$row,$pfData[$dateVal][$counVal][$currPf[$i]]['oldUserFirstPay']);
			}
				

			$Excel->setCellValue(getNameFromNumber(9+$length*7).''.$row,$eventAll[$dateVal][$counVal]['oldUserFirstPayRate']);
			$length=count($currPf);
			for ($i=0;$i<$length;$i++){
				$Excel->setCellValue(getNameFromNumber(10+$length*7+$i).''.$row,$pfData[$dateVal][$counVal][$currPf[$i]]['oldUserFirstPayRate']);
			}
				
			$Excel->setCellValue(getNameFromNumber(10+$length*8).''.$row,$eventAll[$dateVal][$counVal]['oldPayDAU']);
			$length=count($currPf);
			for ($i=0;$i<$length;$i++){
				$Excel->setCellValue(getNameFromNumber(11+$length*8+$i).''.$row,$pfData[$dateVal][$counVal][$currPf[$i]]['oldPayDAU']);
			}
				
			$Excel->setCellValue(getNameFromNumber(11+$length*9).''.$row,$eventAll[$dateVal][$counVal]['oldPayNewPay']);
			$length=count($currPf);
			for ($i=0;$i<$length;$i++){
				$Excel->setCellValue(getNameFromNumber(12+$length*9+$i).''.$row,$pfData[$dateVal][$counVal][$currPf[$i]]['oldPayNewPay']);
			}
				
			$Excel->setCellValue(getNameFromNumber(12+$length*10).''.$row,$eventAll[$dateVal][$counVal]['oldUserPayRate']);
			$length=count($currPf);
			for ($i=0;$i<$length;$i++){
				$Excel->setCellValue(getNameFromNumber(13+$length*10+$i).''.$row,$pfData[$dateVal][$counVal][$currPf[$i]]['oldUserPayRate']);
			}
				
			$Excel->setCellValue(getNameFromNumber(13+$length*11).''.$row,$eventAll[$dateVal][$counVal][1]['rate']);
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
			}
				
			$row++;
		}
	}
	//filename
	$file_name = '运营数据统计';
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
	$sids = implode(',', $selectServerids);
	$whereSql=" where sid in ($sids) ";
	$startDate = substr($_REQUEST['startDate'],0,10);
	$sDdate= date('Ymd',strtotime($startDate));
	$endDate = substr($_REQUEST['endDate'],0,10);
	$eDate =date('Ymd',strtotime($endDate)+86400);
	$whereSql .= " and date >=$sDdate and date <= $eDate ";
	if($currCountry&&(!in_array('ALL', $currCountry))){
		$countries=implode("','", $currCountry);
		$whereSql .=" and country in('$countries') ";
	}
	/* 	$sql="select country,pf,date,sum(dau) s_dau,sum(reg) s_reg,sum(paid_dau) as paid_dau,sum(deviceDau) as deviceDau from stat_allserver.stat_dau_daily_pf_country_new $whereSql group by country,pf,date;"; */
	$sql="select country,pf,date,sum(dau) s_dau,sum(reg) s_reg from stat_allserver.stat_dau_daily_pf_country_new $whereSql group by country,pf,date;";
	$result = query_infobright($sql);
	$eventAll=array();
	$pfData=array();
	$dateArray=array();
	$countryArray=array();
	foreach ($result['ret']['data'] as $curRow){
		$country=strtoupper($curRow['country']);
		$pf=$curRow['pf'];
		$dateIndesx=$curRow['date'];
		if(!in_array($dateIndesx, $dateArray)){
			$dateArray[]=$dateIndesx;
		}
		if(!in_array($country, $countryArray)){
			$countryArray[]=$country;
		}

		if(in_array('ALL', $currCountry)){
			$country='合计';
		}

		$eventAll[$dateIndesx][$country]['dau'] += $curRow['s_dau'];

		$eventAll[$dateIndesx][$country]['paid_dau'] += $curRow['paid_dau'];
			
		/* $eventAll[$dateIndesx][$country]['deviceDau'] += $curRow['deviceDau']; */
			
		$eventAll[$dateIndesx][$country]['sdau'] += $curRow['s_dau'] - $curRow['s_reg'];

		$eventAll[$dateIndesx][$country]['reg'] += $curRow['s_reg'];

		if (in_array($pf, $currPf)){
			$pfData[$dateIndesx][$country][$pf]['dau'] += $curRow['s_dau'];/**老用户DAU*/
				
			$pfData[$dateIndesx][$country][$pf]['paid_dau'] += $curRow['paid_dau'];

			/* $pfData[$dateIndesx][$country][$pf]['deviceDau'] += $curRow['deviceDau']; */

			$pfData[$dateIndesx][$country][$pf]['sdau'] += $curRow['s_dau'] - $curRow['s_reg'];
				
			$pfData[$dateIndesx][$country][$pf]['reg'] += $curRow['s_reg'];
		}
	}
	$sql = "select country,pf,date,sum(payTotle) as payTotle,sum(payUsers) as payUsers,sum(firstPay) as firstPay from stat_allserver.pay_analyze_pf_country $whereSql GROUP BY country,pf,date;";
	/* $sql = "select country,pf,date,sum(payTotle) as payTotle,sum(payUsers) as payUsers,sum(payTimes) as payTimes,sum(dau) as dau,sum(firstPay) as firstPay from stat_allserver.pay_analyze_pf_country $whereSql GROUP BY country,pf,date;"; */
	$result = query_infobright($sql);
	foreach ($result['ret']['data'] as $row){
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
		$eventAll[$dateIndesx][$country]['payTotle'] += $row['payTotle'];
		$eventAll[$dateIndesx][$country]['firstPay'] += $row['firstPay'];
		$eventAll[$dateIndesx][$country]['payUsers'] += $row['payUsers'];

		if (in_array($pf, $currPf)){
				
			$pfData[$dateIndesx][$country][$pf]['payTotle'] += $row['payTotle'];
			$pfData[$dateIndesx][$country][$pf]['firstPay'] += $row['firstPay'];
			$pfData[$dateIndesx][$country][$pf]['payUsers'] += $row['payUsers'];
		}
	}
	/*自己添加的*/
	/**
	 * regDevice:新注册设备数
	 * firstDayPay(新注册当日付费人数)
	 * oldPayDAU 老付费玩儿家当日登陆
	 * newTotalPay 首冲付费总额
	 *
	 * 新注册付费率（新注册付费人数/新注册用户）， 	firstDayPayRate=(firstDayPay/reg);
	 * 老玩家首冲人数，(首冲人数-新注册付费人数) 	oldUserFirstPay=(firstPay-firstDayPay)
	 * 老玩家首冲付费率(老玩家首冲人数/老用户dau)，	oldUserFirstPayRate=(oldUserFirstPay/sdau)
	 * 老付费玩家付费，(总付费-首冲付费)			oldPayNewPay=(payTotle-newTotalPay)
	 * 老付费玩家(总付费用户数-首充人数)			oldPayUser=(payUser-firstPay)
	 * 老玩家付费率(老付费玩家付费人数/老付费玩家登录) 	oldUserPayRate=(oldPayUser/oldPayDAU)
	 *
	 */
	$sql = "select country,pf,date,sum(regDevice) as regDevice,sum(firstDayPay) as firstDayPay,sum(oldPayDAU) as oldPayDAU,sum(newTotalPay) as newTotalPay from stat_allserver.pay_ratio_analyze_pf_country_referrer_appVersion $whereSql GROUP BY country,pf,date;";
	$result = query_infobright($sql);
	foreach ($result['ret']['data'] as $row){
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
		$eventAll[$dateIndesx][$country]['regDevice'] += $row['regDevice'];/*新注册设备数*/
		$eventAll[$dateIndesx][$country]['firstDayPay'] += $row['firstDayPay'];/*新注册当日付费人数*/
		$eventAll[$dateIndesx][$country]['newTotalPay'] += $row['newTotalPay'];/*首冲付费总额*/
		$eventAll[$dateIndesx][$country]['oldPayDAU'] += $row['oldPayDAU'];/*老付费玩儿家当日登陆*/

		if (in_array($pf, $currPf)){
			$pfData[$dateIndesx][$country][$pf]['regDevice'] += $row['regDevice'];
			$pfData[$dateIndesx][$country][$pf]['firstDayPay'] += $row['firstDayPay'];
			$pfData[$dateIndesx][$country][$pf]['newTotalPay'] += $row['newTotalPay'];
			$pfData[$dateIndesx][$country][$pf]['oldPayDAU'] += $row['oldPayDAU'];
		}
	}

	if(in_array('ALL', $currCountry)){
		unset($countryArray);
		$countryArray[]='合计';
	}
	foreach ($eventAll as $dateKey=>$countryVal){
		foreach ($countryVal as $countryKey=>$value){
				
			$eventAll[$dateKey][$countryKey]['firstDayPayRate']=intval($eventAll[$dateKey][$countryKey]['firstDayPay']*10000/$eventAll[$dateKey][$countryKey]['reg'] )/100 ."%";
			$eventAll[$dateKey][$countryKey]['oldUserFirstPay']=$eventAll[$dateKey][$countryKey]['firstPay']-$eventAll[$dateKey][$countryKey]['firstDayPay'] ;
			$eventAll[$dateKey][$countryKey]['oldUserFirstPayRate']=intval($eventAll[$dateKey][$countryKey]['oldUserFirstPay']*10000/$eventAll[$dateKey][$country]['sdau'])/100 ."%";
			$eventAll[$dateKey][$countryKey]['oldPayNewPay']=$eventAll[$dateKey][$country]['payTotle'] -$eventAll[$dateKey][$country]['newTotalPay'];
			$eventAll[$dateKey][$countryKey]['oldPayUser']=$eventAll[$dateKey][$countryKey]['payUsers']-$eventAll[$dateKey][$countryKey]['firstPay'];
			$eventAll[$dateKey][$countryKey]['oldUserPayRate']=intval($eventAll[$dateKey][$countryKey]['oldPayUser']*10000/$eventAll[$dateKey][$countryKey]['oldPayDAU'] )/100  ."%";
				
			foreach ($currPf as $pfVal){
				$pfData[$dateKey][$countryKey][$pfVal]['firstDayPayRate']=intval($pfData[$dateKey][$countryKey][$pfVal][$pfVal]['firstDayPay']*10000/$pfData[$dateKey][$countryKey][$pfVal]['reg'] )/100  ."%";
				$pfData[$dateKey][$countryKey][$pfVal]['oldUserFirstPay']=$pfData[$dateKey][$countryKey][$pfVal]['firstPay']-$pfData[$dateKey][$countryKey][$pfVal]['firstDayPay'];
				$pfData[$dateKey][$countryKey][$pfVal]['oldUserFirstPayRate']=intval($pfData[$dateKey][$countryKey][$pfVal]['oldUserFirstPay']*10000/$pfData[$dateKey][$country][$pfVal]['sdau']) /100 ."%";
				$pfData[$dateKey][$countryKey][$pfVal]['oldPayNewPay']=$pfData[$dateKey][$country][$pfVal]['payTotle']-$pfData[$dateKey][$country][$pf][$pfVal]['newTotalPay'];
				$pfData[$dateKey][$countryKey][$pfVal]['oldPayUser']=$pfData[$dateKey][$countryKey][$pfVal]['payUsers']-$pfData[$dateKey][$countryKey][$pfVal]['firstPay'] ;
				$pfData[$dateKey][$countryKey][$pfVal]['oldUserPayRate']=intval($pfData[$dateKey][$countryKey][$pfVal]['oldPayUser']*10000/$pfData[$dateKey][$countryKey][$pfVal]['oldPayDAU'] ) /100 ."%";
					
				/* $pfData[$dateKey][$countryKey][$pfVal]['filter']=intval($pfData[$dateKey][$countryKey][$pfVal]['payUsers']*10000/$pfData[$dateKey][$countryKey][$pfVal]['dau'] )/100 ."%";
				 $pfData[$dateKey][$countryKey][$pfVal]['ARPU']=intval($pfData[$dateKey][$countryKey][$pfVal]['payTotle']*100/$pfData[$dateKey][$countryKey][$pfVal]['payUsers'] )/100; */
			}
				
		}
	}
	/* print_r($eventAll); */
	if ($eventAll){
		rsort($dateArray);
		$showData=true;
	}else {
		$alertHeader='没有查询到相关数据';
	}

}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>


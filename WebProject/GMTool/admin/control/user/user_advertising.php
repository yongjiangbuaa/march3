<?php
!defined('IN_ADMIN') && exit('Access Denied');
if(!$_REQUEST['startDate']){
	$startDate = date("Y-m-d 00:00:00",time()-86400*7);
}
if(!$_REQUEST['endDate'])
	$endDate = date("Y-m-d 23:59:59",time());

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

if($_REQUEST['event']=='output'){
	$startTime = strtotime($_REQUEST['startDate'])*1000;
	$endTime = strtotime($_REQUEST['endDate'])*1000;
	$whereSql = " where time >=$startTime and time <= $endTime ";
	if($currCountry&&$currCountry!='ALL'){
		$whereSql .=" and country='$currCountry' ";
	}
	if ($currPf && $currPf!='ALL'){
		if ($currPf=='ios'){
			$sql= "select device,time,tracker,country from addata $whereSql and tracker='Organic' and binary upper(device) = binary device;";
		}else {
			$sql= "select device,time,tracker,country from addata $whereSql and tracker='Organic' and binary lower(device) = binary device;";
		}
		
	}
	
	//exit($sql);
	$eventAll = array();
	$result = $page->globalExecute($sql, 3);
	foreach ($result['ret']['data'] as $curRow){
		
		$one=array();
		$one['device']=$curRow['device'];
		$one['time']=$curRow['time']?date('Y-m-d H:i:s',$curRow['time']/1000):0;
		$one['tracker']=$curRow['tracker'];
		$one['country']=$curRow['country'];
		
		$eventAll[]=$one;
	}
	$title = array('10%'=>'设备','10%'=>'日期','tracker','国家');

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
	foreach ($eventAll as $value){
		$Excel->setCellValue($titleIndex[0].''.$row, $value['device']);
		$Excel->setCellValue($titleIndex[1].''.$row,$value['time']);
		$Excel->setCellValue($titleIndex[2].''.$row,$value['tracker']);
		$Excel->setCellValue($titleIndex[3].''.$row,$value['country']);
		$row++;
	}
	//filename
	if ($currPf=='ios'){
		$file_name = 'ios广告装载相关数据'.date('Ymd');
	}elseif ($currPf=='android'){
		$file_name = 'android广告装载相关数据'.date('Ymd');
	}else {
		$file_name = '广告装载相关数据'.date('Ymd');
	}
	
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
	
	
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
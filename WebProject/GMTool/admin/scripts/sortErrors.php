<?php
// exec("/usr/local/cok/SFS2X/scripts/error.sh");
require_once "./../include/PHPExcel.php";
$filePath = './../onlinelog/logError.txt';
if(is_file($filePath)){
	$sortData = array();
	$fileData = file($filePath);
	$lastError = null;
	foreach ( $fileData as $line ) {
		$lineData= explode('|', $line);
		$date = $lineData[0];
		$errorInfo = explode('--', $lineData[6]);
		$cmd = explode('CMD:', $errorInfo[0]);
		$exception = explode('{', $errorInfo[5]);
		
		if($lastError != $errorInfo[0].$errorInfo[2])
			$sortData[$date][$cmd[1]][$errorInfo[4]][$exception[0]]++;
		$lastError = $errorInfo[0].$errorInfo[2];
	}
	//导入PHPExcel类
	require_once "./../include/PHPExcel.php";
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
	$ret = array();
	$xlsTitle = array('日期','命令','错误','错误信息','次数');
	$titleIndex = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE');
	//set title
	$Excel = $objPHPExcel->setActiveSheetIndex(0);
	$row = 1;
	$line = 0;
	foreach ($xlsTitle as $width=>$value){
// 		if(strlen($value) != mb_strlen($value)){
			$width = (strlen($value) + iconv_strlen($value))* 1.1 * 8.26/22;
			$objPHPExcel->getActiveSheet()->getColumnDimension($titleIndex[$line])->setWidth($width);
// 		}else{
			$objPHPExcel->getActiveSheet()->getColumnDimension($titleIndex[$line])->setAutoSize(true);
// 		}
		$Excel->setCellValue($titleIndex[$line++].''.$row,$value);
	}
	//set data
	foreach ($sortData as $date=>$dateData){
		$everyDay = $sort = array();
		foreach ($dateData as $cmd=>$cmdData){
			foreach ($cmdData as $error=>$errorData){
				foreach ($errorData as $errorInfo=>$count){
					$everyDay[] = array($cmd,$error,$errorInfo,$count);
					$sort[] = $count;
				}
			}
		}
		array_multisort($sort,SORT_DESC,$everyDay);
		foreach ($everyDay as $everyDayData){
			$row++;
			$Excel->setCellValue($titleIndex[0].''.$row,$date);
			$Excel->setCellValue($titleIndex[1].''.$row,$everyDayData[0]);
			$Excel->setCellValue($titleIndex[2].''.$row,$everyDayData[1]);
			$Excel->setCellValue($titleIndex[3].''.$row,$everyDayData[2]);
			$Excel->setCellValue($titleIndex[4].''.$row,$everyDayData[3]);
		}
		$row++;
	}
	//filename
	$file_name = 'errorLog';
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
function search($data){
	if(!is_array($data) && !is_object($data))
		return $data;
	$result = "";
	foreach ($data as $key=>$value)
	{
		if(!is_array($value))
		{
			$result .= str_repeat("&nbsp;",$tab*4).(string)$key." => ".(string)$value."<br />";
		}
		else
		{
			$result .= str_repeat("&nbsp;",$tab*4).(string)$key." => <br />";
			$result .= search($value,$tab+1);
		}
	}
	return $result;
}
?>
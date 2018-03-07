<?php
!defined('IN_ADMIN') && exit('Access Denied');
if(!$_REQUEST['start_time']){
	$start = date("Y-m-d",time()-86400*30);
}else {
	$start = $_REQUEST['start_time'];
}
if(!$_REQUEST['end_time']){
	$end = date("Y-m-d",time());
}else {
	$end = $_REQUEST['end_time'];
}
$showData=false;
$alertHead='';
$displayCountryArr=array(
	'US',
	'JP',
	'CN',
	'KR',
	'TW',
	'RU',
	'HK',
	'MO',
	'GB',
	'DE',
	'FR',
	'TR',
	'AE',
	'AU',
	'NZ',
	'IT',
	'ES',
	'NO',
	'IR',
	'ID',
	'SG',
	'MY',
	'TH',
	'VN',
	'BR',
	'SA',
);
$dimensionArray=array(
		'国家',
		'一级渠道',
);
$titleArray=array(
	'cost'=>'花费',
	'install'=>'安装',
	'cpi'=>'cpi',
);
$dbCol=array(
	'cost',
	'install',
);
	
	$startYmd=date('Y-m-d',strtotime($start));
	$endYmd=date('Y-m-d',strtotime($end));
	$link = get_ad_connection();
	$colStr='';
	$split='';
	foreach ($dbCol as $col){
		$colStr.=$split."sum($col) as $col";
		$split=',';
	}
	$currdimension=$_REQUEST['dimension'];
	$param='';
	switch ($currdimension) {
		case 0:
			$param='country';
			$sql="select date,os,country,$colStr from adInstallDataWithoutChannelSecond where date between '$startYmd' and '$endYmd' and channelTop!='Organic' group by date,os,country order by os,country;";
			break;
		case 1:
			$param='channelTop';
			$sql="select date,os,channelTop,$colStr from adInstallDataWithoutChannelSecond where date between '$startYmd' and '$endYmd' group by os,channelTop order by date,os,channelTop;";
			break;
	}
	$totalTemp=array();
//	echo $sql;
    $res = mysqli_query($link,$sql);
    $osArray=array();
    $paramArray=array();
    $dateArray=array();
    while ($row = mysqli_fetch_assoc($res)){
    		if (!isset($dateArray[date("Ymd",strtotime($row['date']))])){
    			$dateArray[date("Ymd",strtotime($row['date']))]=$row['date'];
    		}
    		if (!in_array($row['os'], $osArray)){
    			$osArray[]=$row['os'];
    		}
    		if (!in_array($row[$param], $paramArray) && (!in_array($row[$param], $displayCountryArr) ) ){
    			$paramArray[]=$row[$param];
    		}
    		$one=array();
		$one['os']=$row['os'];
		$one[$param]=$row[$param];
		foreach ($dbCol as $col){
			$one[$col]=$row[$col];
		}
		$one['cpi']=number_format($one['cost']/$one['install'],2);
		$totalTemp[$row['date']][$row[$param]][$row['os']]=$one;
    }
    if ($param=='country'){
    		$paramArray=$displayCountryArr+$paramArray;
    }
    $total=array();
    mysqli_close($link);
    $total=$totalTemp;
    krsort($dateArray);
    if ($_REQUEST['event']!='output'){
	    if ($total){
	    		$showData=true;
	    }else {
	    		$alertHead='没有查到相关数据';
	    }
    }else {
    		$titleStr='';
    		$prefix='';
    		foreach ($addTitle as $k=>$v){
    			$titleStr.=$prefix.$v;
    			$prefix=',';
    		}
    		foreach ($titleArray as $tk=>$tv){
    			$titleStr .=$prefix.$tv;
    			$prefix=',';
    		}
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
    		foreach ($total as $dbData){
    			$i=0;
    			foreach ($addTitle as $k=>$v){
    				if ($k=='country' && $countryList[$dbData[$k]]){
    					$Excel->setCellValue(getNameFromNumber($i).''.$row, $countryList[$dbData[$k]]);
    				}else {
    					$Excel->setCellValue(getNameFromNumber($i).''.$row, $dbData[$k]);
    				}
    				$i++;
    			}
    			$i=count($addTitle);
    			foreach ($titleArray as $tk=>$tv){
    				$Excel->setCellValue(getNameFromNumber($i).''.$row, $dbData[$tk]);
    				$i++;
    			}
    			$row++;
    		}
    		//filename
    		$file_name = '投放数据';
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

include( renderTemplate("{$module}/{$module}_{$action}") );
?>
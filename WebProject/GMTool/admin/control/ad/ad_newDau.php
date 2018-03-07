<?php
!defined('IN_ADMIN') && exit('Access Denied');
if(!$_REQUEST['start_time']){
	$start = date("Y-m-d",time()-86400*30);
}else {
	$start = $_REQUEST['start_time'];
}
if(!$_REQUEST['start_time']){
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

$titleArray=array(
	'date'=>'日期',
	'dau'=>'日活跃',
	'devicedau'=>'机器码DAU	',
	'paydau'=>'付费DAU',
	'old'=>'老玩家',
	'reg'=>'新注册',
	'replay'=>'重玩',
	'move'=>'迁服',
);

if($_REQUEST['display']){
	$date = date('Y-m-d',strtotime($_REQUEST['date']));
	$param=$_REQUEST['param'];
	if ($param=='country'){
		$displayTitle=array(
			'country'=>'国家'
		);
		$displaySort=$displayCountryArr;
		$regSql="select country,valueType,value from user_basic_country where date='$date';";
		$remainSql="select country,valueType,value from user_dau_country where date='$date';";
	}elseif ($param=='pf'){
		$displayTitle=array(
			'pf'=>'平台'
		);
		$displaySort=$pfList;
		unset($displaySort['ALL']);
		$displaySort=array_keys($displaySort);
		$regSql="select pf,valueType,value from user_basic_pf where date='$date';";
		$remainSql="select pf,valueType,value from user_dau_pf where date='$date';";
	}elseif ($param=='server'){
		$displayTitle=array(
			'server'=>'服'
		);
		$regSql="select server,valueType,value from user_basic_server where date='$date';";
		$remainSql="select server,valueType,value from user_dau_server where date='$date';";
	}
	$displayTitle=$displayTitle+$titleArray;
	unset($displayTitle['date']);
	$displayTitle=array('date'=>'日期')+$displayTitle;
	$link = get_ad_connection();
	$dataTemp=array();
	$res = mysqli_query($link,$regSql);
	while ($row = mysqli_fetch_assoc($res)){
		$dataTemp[strtoupper($row[$param])][$row['valueType']]+=$row['value'];
	}
	$res = mysqli_query($link,$remainSql);
	while ($row = mysqli_fetch_assoc($res)){
		$dataTemp[strtoupper($row[$param])][$row['valueType']]+=$row['value'];
	}
	mysqli_close($link);
	foreach ($dataTemp as $key=>&$dbVal){
		$dbVal['date']=$date;
		$dbVal[$param]=$key;
		$dbVal['old']=$dbVal['dau']-$dbVal['reg']-$dbVal['replay']-$dbVal['move'];
	}
	$data=array();
	if ($param!='server'){
		foreach ($displaySort as $cou){
			foreach ($dataTemp as $dbK=>$dbVal){
				if (strtolower($cou)==strtolower($dbK)){
					$data[]=$dbVal;
					break;
				}
			}
		}
		foreach ($dataTemp as $dbK=>$dbVal){
			if (!in_array($dbK,$displaySort)){
				$data[]=$dbVal;
			}
		}
	}else{
		$data=$dataTemp;
		krsort($data);
	}
	
	if ($_REQUEST['event']=='output'){
		$titleStr='';
		$prefix='';
		foreach ($displayTitle as $tk=>$tv){
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
		foreach ($data as $dbData){
			$i=0;
			foreach ($displayTitle as $k=>$v){
				if ($k=='country' && $countryList[$dbData[$k]]){
					$Excel->setCellValue(getNameFromNumber($i).''.$row, $countryList[$dbData[$k]]);
				}else {
					$Excel->setCellValue(getNameFromNumber($i).''.$row, $dbData[$k]);
				}
				$i++;
			}
			$row++;
		}
		//filename
		if($param=='country'){
			$file_name = '每日活跃(新)-国家';
		}elseif ($param=='pf'){
			
			$file_name = '每日活跃(新)-平台';
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
	}else {
		$disHtml = "<div style='padding-left: 10%;'><table class='listTable' style='text-align:center;width:90%'><thead>";
		$disHtml .= "<tr>";
		foreach ($displayTitle as $k=>$v){
			$disHtml .= "<th>$v</th>";
		}
		$disHtml .= "</tr>";
		foreach ($data as $dbData){
			$disHtml .= "<tr>";
			foreach ($displayTitle as $k=>$v){
				if ($k=='country' && $countryList[$dbData[$k]]){
					$disHtml .= "<td>".$countryList[$dbData[$k]]."</td>";
				}else {
					$disHtml .= "<td>".$dbData[$k]."</td>";
				}
			}
			$disHtml .= "</tr>";
		}
		$disHtml .="</table><br>";
		$disHtml .='<input class="display:none;" type="hidden" value="'.$date.'" id="display_date" name="display_date"/>';
		$disHtml .='<input class="display:none;" type="hidden" value="'.$param.'" id="display_param" name="display_param"/>';
		$disHtml.="</div>";
		echo $disHtml;
	}
	exit();
}

	$startYmd=date('Y-m-d',strtotime($start));
	$endYmd=date('Y-m-d',strtotime($end));
	$link = get_ad_connection();
	$sql="select date,valueType,value from user_basic_total where date between '$startYmd' and '$endYmd';";
	$total=array();
	$dateArray=array();
    $res = mysqli_query($link,$sql);
    while ($row = mysqli_fetch_assoc($res)){
    		$date=date('Ymd',strtotime($row['date']));
    		if (!in_array($date, $dateArray)){
    			$dateArray[]=$date;
    		}
	    $total[$date][$row['valueType']]=$row['value'];
    }
    $sql="select date,valueType,value from user_dau_total where date between '$startYmd' and '$endYmd';";
    $res = mysqli_query($link,$sql);
    while ($row = mysqli_fetch_assoc($res)){
	    	$date=date('Ymd',strtotime($row['date']));
	    $total[$date][$row['valueType']]=$row['value'];
    }
    foreach ($total as $key=>&$dbVal){
    		$dbVal['date']=date('Y-m-d',strtotime($key));
    		$dbVal['old']=$dbVal['dau']-$dbVal['reg']-$dbVal['replay']-$dbVal['move'];
    }
    mysqli_close($link);
    krsort($total);
    if ($_REQUEST['event']!='output'){
	    if ($total){
	    		$showData=true;
	    }else {
	    		$alertHead='没有查到相关数据';
	    }
    }else {
	    	$titleStr='';
	    	$prefix='';
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
	    		foreach ($titleArray as $tk=>$tv){
	    			$Excel->setCellValue(getNameFromNumber($i).''.$row, $dbData[$tk]);
	    			$i++;
	    		}
	    		$row++;
	    	}
	    	//filename
	    	$file_name = '每日活跃(新)';
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
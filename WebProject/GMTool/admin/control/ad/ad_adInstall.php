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
foreach ($countryList as $key=>$value) {
	$key = strtolower($key);
	$countryList[$key] = $value;
}
$dimensionArray=array(
		'os',
		'国家',
		'一级渠道',
		'二级渠道'
);
$titleArray=array(
	'install'=>'安装',
	'cost'=>'花费',
	'cpi'=>'cpi',
	'reg'=>'注册',
	'pay1'=>'1日充值',
	'roi1'=>'1日roi(%)',
	'pay3'=>'3日充值',
	'roi3'=>'3日roi(%)',
	'pay7'=>'7日充值',
	'roi7'=>'7日roi(%)',
	'pay14'=>'14日充值',
	'roi14'=>'14日roi(%)',
	'pay30'=>'30日充值',
	'roi30'=>'30日roi(%)',
	'pay60'=>'60日充值',
	'roi60'=>'60日roi(%)',
	'rate1'=>'1日留存率(%)',
	'rate3'=>'3日留存率(%)',
	'rate7'=>'7日留存率(%)', 
	'rate14'=>'14日留存率(%)',
	'rate30'=>'30日留存率(%)',
	'rate60'=>'60日留存率(%)',
);
$dbCol=array(
	'install',
	'cost',
	'reg',
	'pay1',
	'pay3',
	'pay7',
	'remain1',
	'remain3',
	'remain7',
	'pay14',
	'pay30',
	'pay60',
	'remain14',
	'remain30',
	'remain60',
);
if($_REQUEST['display']){
	$start = $_REQUEST['start'];
	$end = $_REQUEST['end'];
	$startYmd=date('Y-m-d',strtotime($start));
	$endYmd=date('Y-m-d',strtotime($end));
	$os=$_REQUEST['os'];
	$country=$_REQUEST['country'];
	$channelTop=$_REQUEST['channelTop'];
	$channelSecond=$_REQUEST['channelSecond'];
	
	$link = get_ad_connection();

	$colStr='';
	$split='';
	foreach ($dbCol as $col){
		$colStr.=$split."sum($col) as $col";
		$split=',';
	}
	$addTitle=array();
	if ($channelSecond && $channelTop && $country && $os){
		$dimension=3;
		$addTitle=array('date'=>'日期','os'=>'os','country'=>'国家','channelTop'=>'第一渠道','channelSecond'=>'第二渠道');
		$sql="select date,os,country,channelTop,channelSecond,$colStr from adInstallData where date >= '$startYmd' and date<'$endYmd' and os='$os' and country='$country' and channelTop='$channelTop' and channelSecond='$channelSecond' and install>10 group by date,os,country,channelTop,channelSecond order by date desc,install desc;";
	}elseif ($channelTop && $country && $os){
		$dimension=2;
		$addTitle=array('date'=>'日期','os'=>'os','country'=>'国家','channelTop'=>'第一渠道');
		$sql="select date,os,country,channelTop,$colStr from adInstallData where date >= '$startYmd' and date<'$endYmd' and os='$os' and country='$country' and channelTop='$channelTop' group by date,os,country,channelTop order by date desc,install desc;";
	}elseif ($country && $os){
		$dimension=1;
		$addTitle=array('date'=>'日期','os'=>'os','country'=>'国家');
		$sql="select date,os,country,$colStr from adInstallData where date >= '$startYmd' and date<'$endYmd' and os='$os' and country='$country' and channelTop!='Organic' group by date,os,country order by date desc,install desc;";
	}else {
		$dimension=0;
		$addTitle=array('date'=>'日期','os'=>'os');
		$sql="select date,os,$colStr from adInstallData where date >= '$startYmd' and date<'$endYmd' and os='$os' and channelTop!='Organic' group by date,os order by date desc,install desc;";
	}
	$data=array();
	$res = mysqli_query($link,$sql);
	while ($row = mysqli_fetch_assoc($res)){
		$one=array();
		$one['date']=$row['date'];
		if ($dimension==0){
	    		$one['os']=$row['os'];
			foreach ($dbCol as $col){
				$one[$col]=$row[$col];
			}
		}else if ($dimension==1){
			$one['os']=$row['os'];
			$one['country']=$row['country'];
			foreach ($dbCol as $col){
				$one[$col]=$row[$col];
			}
		}else if ($dimension==2){
			$one['os']=$row['os'];
			$one['country']=$row['country'];
			$one['channelTop']=$row['channelTop'];
			foreach ($dbCol as $col){
				$one[$col]=$row[$col];
			}
		}else if ($dimension==3){
			$one['os']=$row['os'];
			$one['country']=$row['country'];
			$one['channelTop']=$row['channelTop'];
			$one['channelSecond']=$row['channelSecond'];
			foreach ($dbCol as $col){
				$one[$col]=$row[$col];
			}
		}
		$one['cpi']=number_format($one['cost']/$one['install'],2);
		$one['roi1']=$one['cost']>0? intval($one['pay1'] / $one['cost'] *10000)/100 :0;
		$one['roi3']=$one['cost']>0? intval($one['pay3'] / $one['cost'] *10000)/100 :0;
		$one['roi7']=$one['cost']>0? intval($one['pay7'] / $one['cost'] *10000)/100 :0;
		$one['roi14']=$one['cost']>0? intval($one['pay14'] / $one['cost'] *10000)/100 :0;
		$one['roi30']=$one['cost']>0? intval($one['pay30'] / $one['cost'] *10000)/100 :0;
		$one['roi60']=$one['cost']>0? intval($one['pay60'] / $one['cost'] *10000)/100 :0;
		$one['rate1']=$one['reg']>0? intval($one['remain1'] / $one['reg'] *10000)/100 :0;
		$one['rate3']=$one['reg']>0? intval($one['remain3'] / $one['reg'] *10000)/100 :0;
		$one['rate7']=$one['reg']>0? intval($one['remain7'] / $one['reg'] *10000)/100 :0;
		$one['rate14']=$one['reg']>0? intval($one['remain14'] / $one['reg'] *10000)/100 :0;
		$one['rate30']=$one['reg']>0? intval($one['remain30'] / $one['reg'] *10000)/100 :0;
		$one['rate60']=$one['reg']>0? intval($one['remain60'] / $one['reg'] *10000)/100 :0;
		$data[]=$one;
	}
	mysqli_close($link);
	if ($_REQUEST['event']=='output'){
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
		foreach ($data as $dbData){
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
		$file_name = '投放日数据';
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
		$disHtml = "<div><table class='listTable' style='text-align:center'><thead>";
		$disHtml .= "<tr>";
		foreach ($addTitle as $k=>$v){
			$disHtml .= "<th>$v</th>";
		}
		foreach ($titleArray as $k=>$v){
			$disHtml .= "<th>$v</th>";
		}
		$disHtml .= "</tr>";
		foreach ($data as $dbData){
			$disHtml .= "<tr>";
			foreach ($addTitle as $k=>$v){
				if ($k=='country' && $countryList[$dbData[$k]]){
					$disHtml .= "<td>".$countryList[$dbData[$k]]."</td>";
				}else {
					$disHtml .= "<td>".$dbData[$k]."</td>";
				}
			}
			foreach ($titleArray as $k=>$v){
				$disHtml .= "<td>".$dbData[$k]."</td>";
			}
			$disHtml .= "</tr>";
		}
		$disHtml .="</table><br>";
		$disHtml .='<input class="display:none;" type="hidden" value="'.$start.'" id="display_start" name="display_start"/>';
		$disHtml .='<input class="display:none;" type="hidden" value="'.$end.'" id="display_end" name="display_end"/>';
		$disHtml .='<input class="display:none;" type="hidden" value="'.$os.'" id="display_os" name="display_os"/>';
		$disHtml .='<input class="display:none;" type="hidden" value="'.$country.'" id="display_country" name="display_country"/>';
		$disHtml .='<input class="display:none;" type="hidden" value="'.$channelTop.'" id="display_channelTop" name="display_channelTop"/>';
		$disHtml .='<input class="display:none;" type="hidden" value="'.$channelSecond.'" id="display_channelSecond" name="display_channelSecond"/>';
		$disHtml.="</div>";
		echo $disHtml;
	}
	exit();
}
	
	$startYmd=date('Y-m-d',strtotime($start));
	$endYmd=date('Y-m-d',strtotime($end));
	if ($_REQUEST['event']){
		$currdimension=$_REQUEST['dimension'];
	}else {
		$currdimension=1;
	}
	$link = get_ad_connection();
	$colStr='';
	$split='';
	foreach ($dbCol as $col){
		$colStr.=$split."sum($col) as $col";
		$split=',';
	}
	$addTitle=array();
	switch ($currdimension) {
		case 0:
			$addTitle=array('os'=>'os');
			$sql="select os,$colStr from adInstallData where date >= '$startYmd' and date<'$endYmd' and channelTop!='Organic' group by os order by install desc;";
			break;
		case 1:
			$addTitle=array('os'=>'os','country'=>'国家');
			$sql="select os,country,$colStr from adInstallData where date >= '$startYmd' and date<'$endYmd' and channelTop!='Organic' group by os,country order by os,country;";
			break;
		case 2:
			$addTitle=array('os'=>'os','country'=>'国家','channelTop'=>'第一渠道');
			$sql="select os,country,channelTop,$colStr from adInstallData where date >= '$startYmd' and date< '$endYmd' group by os,country,channelTop order by os,country,channelTop;";
			break;
		case 3:
			$addTitle=array('os'=>'os','country'=>'国家','channelTop'=>'第一渠道','channelSecond'=>'第二渠道');
			$sql="select os,country,channelTop,channelSecond,$colStr from adInstallData where date >= '$startYmd' and date<'$endYmd' and install>10 group by os,country,channelTop,channelSecond order by os,country,channelTop,channelSecond;";
			break;
	}
	$instalCol=count($addTitle);
	$totalTemp=array();
// 	echo $sql;
    $res = mysqli_query($link,$sql);
    while ($row = mysqli_fetch_assoc($res)){
    		$one=array();
	    if ($currdimension==0){
	    		$one['os']=$row['os'];
			foreach ($dbCol as $col){
				$one[$col]=$row[$col];
			}
		}else if ($currdimension==1){
			$one['os']=$row['os'];
			$one['country']=$row['country'];
			foreach ($dbCol as $col){
				$one[$col]=$row[$col];
			}
		}else if ($currdimension==2){
			$one['os']=$row['os'];
			$one['country']=$row['country'];
			$one['channelTop']=$row['channelTop'];
			foreach ($dbCol as $col){
				$one[$col]=$row[$col];
			}
		}else if ($currdimension==3){
			$one['os']=$row['os'];
			$one['country']=$row['country'];
			$one['channelTop']=$row['channelTop'];
			if (strlen($row['channelSecond'])>20){
				$strArray=array();
				for ($i=0;$i<strlen($row['channelSecond']);$i++){
					$strArray[]=substr($row['channelSecond'], $i, 1);
				}
				$chunks=array_chunk($strArray, 20, true);
				$sub='';
				foreach ($chunks as $chunkData){
					$strTemp=implode("",$chunkData);
					$one['channelSecond'].=$sub.$strTemp;
					$sub="<br>";
				}
			}else {
				
				$one['channelSecond']=$row['channelSecond'];
			}
			foreach ($dbCol as $col){
				$one[$col]=$row[$col];
			}
		}
		$one['cpi']=number_format($one['cost']/$one['install'],2);
		$one['roi1']=$one['cost']>0? intval($one['pay1'] / $one['cost'] *10000)/100 :0;
		$one['roi3']=$one['cost']>0? intval($one['pay3'] / $one['cost'] *10000)/100 :0;
		$one['roi7']=$one['cost']>0? intval($one['pay7'] / $one['cost'] *10000)/100 :0;
		$one['roi14']=$one['cost']>0? intval($one['pay14'] / $one['cost'] *10000)/100 :0;
		$one['roi30']=$one['cost']>0? intval($one['pay30'] / $one['cost'] *10000)/100 :0;
		$one['roi60']=$one['cost']>0? intval($one['pay60'] / $one['cost'] *10000)/100 :0;
		$one['rate1']=$one['reg']>0? intval($one['remain1'] / $one['reg'] *10000)/100 :0;
		$one['rate3']=$one['reg']>0? intval($one['remain3'] / $one['reg'] *10000)/100 :0;
		$one['rate7']=$one['reg']>0? intval($one['remain7'] / $one['reg'] *10000)/100 :0;
		$one['rate14']=$one['reg']>0? intval($one['remain14'] / $one['reg'] *10000)/100 :0;
		$one['rate30']=$one['reg']>0? intval($one['remain30'] / $one['reg'] *10000)/100 :0;
		$one['rate60']=$one['reg']>0? intval($one['remain60'] / $one['reg'] *10000)/100 :0;
		$totalTemp[]=$one;
    }
    mysqli_close($link);
    $total=array();
    if ($currdimension!=0 && $totalTemp){
    		$total1=array();
    		$total2=array();
    		
    		foreach ($displayCountryArr as $cou){
    			foreach ($totalTemp as $dbVal){
    				if ($dbVal['country']==$cou){
	    				if ($dbVal['os']=='android'){
	    					$total1[]=$dbVal;
	    				}else {
	    					$total2[]=$dbVal;
	    				}
    				}
    			}
    		}
    		foreach ($totalTemp as $dbVal){
    			if (!in_array($dbVal['country'],$displayCountryArr)){
    				if ($dbVal['os']=='android'){
    					$total1[]=$dbVal;
    				}else {
    					$total2[]=$dbVal;
    				}
    			}
    		}
    		if (!$total1){
    			$total=$total2;
    		}elseif (!$total2){
    			$total=$total1;
    		}elseif ($total1 && $total2){
	    		$total=array_merge($total1,$total2);
    		}
    }else {
   	 	$total=$totalTemp;
    }
    if ($_REQUEST['event']!='output'){
	    if ($total){
	    		$showData=true;
	    }else {
	    		$alertHead='没有查到相关数据';
	    }
    }else {
    	
    	if ($_COOKIE['u']!='yd'){
    	$file_name = '投放数据';
    	// 输出Excel文件头，可把user.csv换成你要的文件名
    	header('Content-Type: application/vnd.ms-excel');
    	header('Content-Disposition: attachment;filename="'.$file_name.'.csv"');
    	header('Cache-Control: max-age=0');
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
    	$fp = fopen('php://output', 'a');
    	foreach ($title as $i => $v) {
    		// CSV的Excel支持GBK编码，一定要转换，否则乱码
    		$head[$i] = iconv('utf-8', 'gbk', $v);
    }
    	// 将数据通过fputcsv写到文件句柄
    	fputcsv($fp, $head);
    	foreach ($total as $dbData){
    		$outData=array();
    		foreach ($addTitle as $k=>$v){
    			if ($k=='country' && $countryList[$dbData[$k]]){
    				$outData[]=iconv('utf-8', 'gbk', $countryList[$dbData[$k]]);
    			}else {
    				$outData[]=iconv('utf-8', 'gbk', $dbData[$k]);
    			}
    		}
    		foreach ($titleArray as $tk=>$tv){
    			$outData[]=iconv('utf-8', 'gbk', $dbData[$tk]);
    		}
    		fputcsv($fp, $outData);
    	}
    	exit();
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
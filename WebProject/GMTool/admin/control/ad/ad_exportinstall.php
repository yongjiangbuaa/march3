<?php
!defined('IN_ADMIN') && exit('Access Denied');
set_time_limit(0);
ini_set('memory_limit', '768M');

if(!$_REQUEST['start_time']){
	$start = date("Y-m-d",time()-86400*5);
}else {
	$start = $_REQUEST['start_time'];
}
if(!$_REQUEST['start_time']){
	$end = date("Y-m-d",time());
}else {
	$end = $_REQUEST['end_time'];
}
$os_name = array('ALL' => '--ALL--','ios'=>'ios','android'=>'android');

$adreferrer = include 'adreferrerArray.php';

if (!$_REQUEST['selectCountry']) {
	$currCountry = 'ALL';
}else{
	$currCountry = strtolower($_REQUEST['selectCountry']);
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

$showData=false;
$alertHead='';

$titleArray=array(
	'date'=>'日期',
	'os_name'=>'操作系统',
	'country'=>'国家',
	'version'=>'version',
	'gaid'=>'gaid',
	'network_name'=>'network_name',
	'ip'=>'ip',
	'device_name'=>'device_name',
	'device_type'=>'device_type',
	'os_version'=>'os_version',
	'tracker'=>'tracker',
	'tracker_name'=>'tracker_name',
);

if($_REQUEST['event']=='view') {
	$limit = 100;
	$showData = true;
	$start = $_REQUEST['start']?substr($_REQUEST['start'],0,10):substr($start,0,10);
	$end = $_REQUEST['end']?substr($_REQUEST['end'],0,10):substr($end,0,10);

	$table_start = date('Ym',strtotime($start));
	$table_end = date('Ym',strtotime($end));
	$start = date('Ymd',strtotime($start));
	$end = date('Ymd',strtotime($end));
	$whereSql = "date>=$start and date<=$end ";
	if ($currCountry != 'all') {
		$whereSql .= " and country='" . $currCountry . "'";
	}
	if ($currPf != 'ALL') {
		$whereSql .= " and os_name='" . $currPf . "' ";
	}
	if ($currReferrer != 'ALL') {
		$whereSql .= " and network_name='" . $currReferrer . "' ";
	}
	//===========================分页（数据量太大）========================
	$i = 0;
	for ($i = $table_start; $i <= $table_end; $i++) {
		if(substr($i,4,6)>12){//跨年
			continue;
		}
		$db_start = ' installcallback.install_callback_' . $i;
		$sql_num = "select count(1) num from $db_start where  $whereSql ";
		if(isset($sql_sum)){
			$sql_sum .= " union " . $sql_num ;
		}else{
			$sql_sum = $sql_num;
		}
	}
	$result_num =query_infobright($sql_sum);
	$result_num = $result_num['ret']['data'][0]['num'];
	$pager = page($result_num, $_REQUEST['page'], $limit);
	$index = $pager['offset'];

	$i = 0;
	for ($i = $table_start; $i <= $table_end; $i++) {
		if(substr($i,4,6)>12){
			continue;
		}
		$db_start = ' installcallback.install_callback_' . $i;
		$sql = "select * from $db_start where  $whereSql ";
		if(isset($sql_all)){
			$sql_all .= " union " . $sql ;
		}else{
			$sql_all = $sql;
		}
	}

	if(in_array($_COOKIE['u'],$privilegeArr)){
		echo $sql_all;
	}

	$sql_all = $sql_all." limit {$index},{$limit}";
	$result_all =query_infobright($sql_all);
	$result_all = $result_all['ret']['data'];
}

if ($_REQUEST['event']=='output'){
	$file_name = 'install数据';
	$titleStr='';
	$prefix='';
	$start = $_REQUEST['start']?substr($_REQUEST['start'],0,10):substr($start,0,10);
	$end = $_REQUEST['end']?substr($_REQUEST['end'],0,10):substr($end,0,10);

	$table_start = date('Ym',strtotime($start));
	$table_end = date('Ym',strtotime($end));
	$start = date('Ymd',strtotime($start));
	$end = date('Ymd',strtotime($end));
	$whereSql = "date>=$start and date<=$end ";
	if ($currCountry != 'all') {
		$whereSql .= " and country='" . $currCountry . "'";
	}
	if ($currPf != 'ALL') {
		$whereSql .= " and os_name='" . $currPf . "' ";
	}
	if ($currReferrer != 'ALL') {
		$whereSql .= " and network_name='" . $currReferrer . "' ";
	}
	$i = 0;
	for ($i = $table_start; $i <= $table_end; $i++) {
		if(substr($i,4,6)>12){
			continue;
		}
		$db_start = ' installcallback.install_callback_' . $i;
		$sql = "select * from $db_start where  $whereSql ";
		if(isset($sql_all)){
			$sql_all .= " union " . $sql ;
		}else{
			$sql_all = $sql;
		}
	}
	$result_all =query_infobright($sql_all);
	$result_all = $result_all['ret']['data'];

	foreach ($titleArray as $tk=>$tv){
		$titleStr .=$prefix.$tv;
		$prefix=',';
	}
	$title=explode(',', $titleStr);

	//导入PHPExcel类
	require ADMIN_ROOT . "/include/PHPExcel.php";
	// Create new PHPExcel object
	$objPHPExcel = new PHPExcel();
	//导出数据太大 设置缓存。
	$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_in_memory;

	PHPExcel_Settings::setCacheStorageMethod($cacheMethod);
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
	foreach ($result_all as $dbData){
		$i=0;
		foreach ($titleArray as $k=>$v){
			$Excel->setCellValue(getNameFromNumber($i).''.$row, $dbData[$k]);
			$i++;
		}
		$row++;
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
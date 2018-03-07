<?php
!defined('IN_ADMIN') && exit('Access Denied');
function loadDiv_this($defValue){
	$maxServer=getMaxServerNum();
	$placeholder = '默认当前服 ,当前最大服为:'.$maxServer;
	return '<div class="span11">
	服编号<input type="text" id="selectServer" name="selectServer" value="'.$defValue.'" placeholder="'.$placeholder.'" style="width: 400px;"/>
	</div>';
}
global $servers;
$sttt = $_REQUEST['selectServer'];
$serverDiv=loadDiv_this($sttt);

if (!$_REQUEST['selectPf']) {
	$currPf = 'ALL';
}else{
	$currPf = $_REQUEST['selectPf'];
}

if($sttt==null){
	$selectServer=array($_COOKIE['Gserver2']=>"");

}else{
	$erversAndSidsArr=getSelectServersAndSids($sttt);
	$selectServer=$erversAndSidsArr['withS'];
	if(sizeof($selectServer)>10){
		$alertHeader='数据量大时,可能会超时';
	}
	$selectServerids=$erversAndSidsArr['onlyNum'];
}
$a = sizeof($selectServer);
if(!$_REQUEST['end']){
	$end = date("Y-m-d 23:59:59",time());
}else {
	$end = $_REQUEST['end'];
}

	if(!empty($_REQUEST['start_time'])){
		$start = $_REQUEST['start_time'];
	}else{
		$start = date('Y-m-d 00:00:00',strtotime("-30 day"));
	}

	$end = $_REQUEST['end_time'];
	$relogin = $_REQUEST['relogin_time'];
	$dayStart = $start?strtotime($start)*1000:0;
	$dayEnd = $end?strtotime($end)*1000:strtotime(date("Y-m-d 23:59:59",time()))*1000;
	$loginDay = $relogin?strtotime($relogin)*1000:0;
// 	$from = " (select * from stat_reg where `time` > {$dayStart} and `time`<{$dayEnd}) reg inner join userprofile u on reg.uid = u.uid "
// 	.($loginDay ? " inner join stat_login login on login.uid = u.uid where login.`time` >= {$loginDay} ":"")
// 	;

	if($currPf && $currPf != 'ALL'){
		$wheresql = " reg.pf='$currPf' and ";
	}
	$from = " where $wheresql u.regTime>=$dayStart and u.regTime<$dayEnd "
	.($loginDay ? " and u.lastOnlineTime>$loginDay":"");



	foreach($selectServer as $server=>$serverValue) {
		$sql = "select u.level,ub.level as ubLevel,u.country,count(distinct(u.uid)) as total from stat_reg reg inner join userprofile u on reg.uid = u.uid inner join user_building ub on reg.uid =ub.uid $from and ub.itemid = 400000 group by country,u.level,ub.level order by u.level asc,ub.level asc";
		$server_result = $page->executeServer($server, $sql,3);
		$all_result[] = $server_result;
	}
//echo $sql.PHP_EOL;

	$result = array();
	$levelArr = array();
	$group = 1;
	$countryArr = array();
	foreach($all_result as $ret){
		foreach ($ret['ret']['data'] as $curRow) {
			$levelArr[$curRow['level']] = $curRow['level'];
			$result['level'][0][$curRow['level']] += $curRow['total'];
			$countryArr[$curRow['country']] += $curRow['total'];
			$maiBuildingArr[$curRow['ubLevel']] = $curRow['ubLevel'];
			$result['mainBuilding'][0][$curRow['ubLevel']] += $curRow['total'];
		}
	}
	ksort($maiBuildingArr);
	ksort($countryArr);
	$result['country'] = $countryArr;


	$title = array('4%'=>'等级','人数','------','大本等级','人数','------','国家分布','人数');
	if($_REQUEST['action'] == 'output')
	{
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
		$tempRow = max(count($result['mainBuilding'][0]),count($result['level'][0]),count($result['country']));
		$levelKey = array_keys($result['level'][0]);
		$mainBuildingKey = array_keys($result['mainBuilding'][0]);
		$countryKey = array_keys($result['country']);
		for ($i=0;$i<$tempRow;$i++)
		{
			if($i<count($result['level'][0])){
				$Excel->setCellValue($titleIndex[0].''.$row, $levelKey[$row-2]);
				$Excel->setCellValue($titleIndex[1].''.$row,$result['level'][0][$levelKey[$row-2]]);
			}else{
				$Excel->setCellValue($titleIndex[0].''.$row,'');
				$Excel->setCellValue($titleIndex[1].''.$row,'');
			}
			$Excel->setCellValue($titleIndex[2].''.$row,'------');
			if($i<count($result['mainBuilding'][0])){
				$Excel->setCellValue($titleIndex[3].''.$row,$mainBuildingKey[$row-2]);
				$Excel->setCellValue($titleIndex[4].''.$row,$result['mainBuilding'][0][$mainBuildingKey[$row-2]]);
			}else{
				$Excel->setCellValue($titleIndex[3].''.$row,'');
				$Excel->setCellValue($titleIndex[4].''.$row,'');
			}
			$Excel->setCellValue($titleIndex[5].''.$row,'------');
			if($i<count($result['country'])){
				$Excel->setCellValue($titleIndex[6].''.$row,$countryKey[$row-2]);
				$Excel->setCellValue($titleIndex[7].''.$row,$result['country'][$countryKey[$row-2]]);
			}else{
				$Excel->setCellValue($titleIndex[6].''.$row,'');
				$Excel->setCellValue($titleIndex[7].''.$row,'');
			}
			$row++;
		}
		//filename
		$file_name = '等级分布统计';
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
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
//$erversAndSidsArr=getSelectServersAndSids($sttt);
//$selectServer=$erversAndSidsArr['withS'];
//$selectServerids=$erversAndSidsArr['onlyNum'];
if($sttt==null){//原先空默认全服,这里默认本服
	$selectServer=array($_COOKIE['Gserver2']=>"");

}else{
	$erversAndSidsArr=getSelectServersAndSids($sttt);
	$selectServer=$erversAndSidsArr['withS'];
	if(sizeof($selectServer)>10){
		$alertHeader='数据量大时,可能会超时';
	}
	$selectServerids=$erversAndSidsArr['onlyNum'];
}
	if(!$_REQUEST['end']){
		$end = date("Y-m-d 23:59:59",time());
	}else {
		$end = $_REQUEST['end'];
	}

	if(!empty($_REQUEST['start_time'])){
		$start = $_REQUEST['start_time'];
	}else{
		$start = date('Y-m-d 00:00:00',strtotime("-1 day"));
	}

if($_REQUEST['action']=='show' || $_REQUEST['action'] == 'output') {
	if ($_REQUEST['start_time']) {
		$start1 = strtotime($_REQUEST['start_time']) * 1000;
	}
	if ($_REQUEST['end_time']) {
		$end1 = strtotime($_REQUEST['end_time']) * 1000;
	}
	$result = array();
	$levelArr = array();
	$dateArr= array();
	$group = 1;//页面用

	foreach ($selectServer as $server => $serverValue) {
		$sid = substr($server, 1);

		$sql = "select AA.date as date ,case
		when (u.time-u.regTime)/86400000<=1 then 1
		when (u.time-u.regTime)/86400000>1 and (u.time-u.regTime)/86400000<=3 then 2
		when (u.time-u.regTime)/86400000>3 and (u.time-u.regTime)/86400000<=7 then 3
		when (u.time-u.regTime)/86400000>7 and (u.time-u.regTime)/86400000<=14 then 4
		when (u.time-u.regTime)/86400000>14 and  (u.time-u.regTime)/86400000<=30 then 5
		when (u.time-u.regTime)/86400000>30 and  (u.time-u.regTime)/86400000<=90 then 6
		when (u.time-u.regTime)/86400000>90 and  (u.time-u.regTime)/86400000<=180 then 7
		when (u.time-u.regTime)/86400000>180 and  (u.time-u.regTime)/86400000<=365 then 8
		when (u.time-u.regTime)/86400000>365 then 9 end as reglevel,count(DISTINCT u.uid) cnt
		from snapshot_s$sid.stat_login_full u
		INNER JOIN (select max(date) as date,uid from snapshot_s$sid.stat_login_full where time>= $start1 and time<$end1 group by date,uid) as AA
		on AA.uid = u.uid and AA.date=u.date
		group by date,reglevel order by date,reglevel;";

		$server_result = query_infobright($sql);
		foreach ($server_result['ret']['data'] as $curRow) {
		$levelArr[$curRow['date']][$curRow['reglevel']] = $curRow['reglevel']; //x轴用到
		$result[$curRow['date']][$curRow['reglevel']] += $curRow['cnt'];
		$dateArr[$curRow['date']] = $curRow['date'];
	}
	}
//echo $sql.PHP_EOL;

	if(in_array($_COOKIE['u'],$privilegeArr)) {
		echo   print_r($result, true);
	}

	$title= array('注册天数','数量','百分比');
	$leveltitle = array(1=>'1',2=>'2-3',3=>'4-7',4=>'8-14',5=>'15-30',6=>'31-90',7=>'91-180',8=>'181-365',9=>'大于一年');
	$html .= "<table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
	$html .= "<tr class='listTr'>";
	foreach ($dateArr as $key=>$value){ //日期 一级标题
		$html .= "<th colspan='3' style='text-align:center;'>" . $value . "</th>";
	}
	$html .= "</tr>";
	$html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
	foreach ($dateArr as $item) {//二级标题
		foreach ($title as $titlevalue) {
			$html .= "<td>$titlevalue</td>";
		}
	}
	$html .= "</tr>";

	foreach($leveltitle as $Lv=>$name) { //内容,一共 $leveltitle.size 行数据
		$html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
		foreach ($dateArr as $item) {
			$sum = array_sum($result[$item]);

			$value = $result[$item][$Lv] ? $result[$item][$Lv] :0 ;
			$per = intval($value*10000/$sum)/100;
			$html .= "<td>$name</td><td>$value</td><td>$per"."%"."</td>";
		}
		$html .= "</tr>";
	}
	$html .= "</table></div>";



	if (false && $_REQUEST['action'] == 'output') {
		$title = array('4%' => '等级', '人数', '------', '大本等级', '人数', '------', '国家分布', '人数');
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
		$titleIndex = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE');
		//set title
		$Excel = $objPHPExcel->setActiveSheetIndex(0);
		$row = 1;
		//set data
		$line = 0;
		foreach ($title as $width => $value) {
			if (strlen($value) != mb_strlen($value)) {
				$width = (strlen($value) + iconv_strlen($value)) * 1.1 * 8.26 / 22;
				$objPHPExcel->getActiveSheet()->getColumnDimension($titleIndex[$line])->setWidth($width);
			} else {
				$objPHPExcel->getActiveSheet()->getColumnDimension($titleIndex[$line])->setAutoSize(true);
			}
			$Excel->setCellValue($titleIndex[$line++] . '' . $row, $value);
		}
		$row++;
		$tempRow = max(count($result['mainBuilding'][0]), count($result['level'][0]), count($result['country']));
		$levelKey = array_keys($result['level'][0]);
		$mainBuildingKey = array_keys($result['mainBuilding'][0]);
		$countryKey = array_keys($result['country']);
		for ($i = 0; $i < $tempRow; $i++) {
			if ($i < count($result['level'][0])) {
				$Excel->setCellValue($titleIndex[0] . '' . $row, $levelKey[$row - 2]);
				$Excel->setCellValue($titleIndex[1] . '' . $row, $result['level'][0][$levelKey[$row - 2]]);
			} else {
				$Excel->setCellValue($titleIndex[0] . '' . $row, '');
				$Excel->setCellValue($titleIndex[1] . '' . $row, '');
			}
			$Excel->setCellValue($titleIndex[2] . '' . $row, '------');
			if ($i < count($result['mainBuilding'][0])) {
				$Excel->setCellValue($titleIndex[3] . '' . $row, $mainBuildingKey[$row - 2]);
				$Excel->setCellValue($titleIndex[4] . '' . $row, $result['mainBuilding'][0][$mainBuildingKey[$row - 2]]);
			} else {
				$Excel->setCellValue($titleIndex[3] . '' . $row, '');
				$Excel->setCellValue($titleIndex[4] . '' . $row, '');
			}
			$Excel->setCellValue($titleIndex[5] . '' . $row, '------');
			if ($i < count($result['country'])) {
				$Excel->setCellValue($titleIndex[6] . '' . $row, $countryKey[$row - 2]);
				$Excel->setCellValue($titleIndex[7] . '' . $row, $result['country'][$countryKey[$row - 2]]);
			} else {
				$Excel->setCellValue($titleIndex[6] . '' . $row, '');
				$Excel->setCellValue($titleIndex[7] . '' . $row, '');
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


}
	
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
<?php
!defined('IN_ADMIN') && exit('Access Denied');
global $servers;
$allServerFlag=true;
// foreach ($_REQUEST as $server=>$value)
// {
// 	if($servers[$server] && $value == 'on'){
// 		$selectServer[] = $server;
// 		$selectServerids[] = substr($server, 1);
// 	}
// }

$sttt = $_REQUEST['selectServer'];
$serverDiv=loadDiv($sttt);
$erversAndSidsArr=getSelectServersAndSids($sttt);
$selectServer=$erversAndSidsArr['withS'];
$selectServerids=$erversAndSidsArr['onlyNum'];

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
if (!$_REQUEST['selectReferrer']) {
	$currReferrer = 'ALL';
}else{
	$currReferrer = $_REQUEST['selectReferrer'];
}
if (!$_REQUEST['selectAppVersion']) {
	$currAppVersion = 'ALL';
}else{
	$currAppVersion = $_REQUEST['selectAppVersion'];
}
if($_REQUEST['allServers']){
	$allServerFlag =true;
}else{
	$allServerFlag =false;
}
if(!$_REQUEST['start'])
	$start = date("Y-m-d",time()-86400*7);
if(!$_REQUEST['end'])
	$end = date("Y-m-d",time());
if (isset($_REQUEST['end'])) {
	try {
		$start = $_REQUEST['start'];
		$end = $_REQUEST['end'];
		if (!$_REQUEST['reconnect']) {
			$dayArr = array(1, 3, 7, 15, 30);
		} else {
			$reconnect = $_REQUEST['reconnect'];
			$dayArr = explode(',', $reconnect);
			if (count($dayArr) == 1) {
				$explodeArr = explode('-', $reconnect);
				if (count($explodeArr) > 1) {
					$dayArr = array();
					$index = $explodeArr[0];
					while ($index <= $explodeArr[1])
						$dayArr[] = $index++;
				}
			}
		}
		$startYmd = date('Ymd', strtotime($start));
		$endYmd = date('Ymd', strtotime($end));
		$sids = implode(',', $selectServerids);
		foreach ($dayArr as $day) {
			$rfields[] = "sum(" . 'r' . $day . ") as " . 'r' . $day;
		}
		$fields = implode(',', $rfields);
		$dayArr2 = array(1);
		foreach ($dayArr2 as $day) {
			$rfields2[] = "sum(" . 'rr' . $day . ") as " . 'rr' . $day;
		}
		$fields2 = implode(',', $rfields2);
		$whereSql .= " and date >=$startYmd and date <= $endYmd  ";
		$whereSql2 .= " and date >=$startYmd and date <= $endYmd  ";
		if ($currCountry && $currCountry != 'ALL') {
			$whereSql .= " and country='$currCountry' ";
			$whereSql2 .= " and country='$currCountry' ";
		}
		if ($currPf && $currPf != 'ALL') {
			$whereSql .= " and pf='$currPf' ";
			$whereSql2 .= " and pf='$currPf' ";
		} else if ($currPf == 'ALL' && $_COOKIE['u'] == 'xiaomi') {
			$whereSql2 .= " and pf in('cn_360','cn_am','cn_anzhi','cn_baidu','cn_dangle','cn_ewan','cn_huawei','cn_kugou','cn_kupai','cn_lenovo','cn_mi','cn_mihy','cn_mzw','cn_nearme','cn_pps','cn_pptv','cn_sogou','cn_toutiao','cn_uc','cn_vivo','cn_wdj','cn_wyx','cn_youku','cn_oppo','cn_sy37','cn_mz','tencent') ";
		}
		if ($currReferrer && $currReferrer != 'ALL') {
			$whereSql .= " and referrer='$currReferrer' ";
		}
		$whereSql2 .= " and referrer='untrusted' ";

		if ($currAppVersion && $currAppVersion != 'ALL') {
			$whereSql .= " and appVersion='$currAppVersion' ";
			$whereSql2 .= " and appVersion='$currAppVersion' ";
		}

		//添加不信人数
		if($currReferrer == 'ALL') {
			$sql2 = "select date,sid,sum(reg_valid) reg_untrust from stat_allserver.stat_retention_daily_pf_country_referrer_appVersion where sid in($sids) $whereSql2  group by date,sid order by date,sid;";
			$ret = query_infobright($sql2);
			foreach ($ret['ret']['data'] as $row) {
				$server = 's' . $row['sid'];
				$regdate = $row['date'];
				$untrust[$regdate][$server] = $row['reg_untrust'];
			}
			//全服
			foreach ($untrust as $regdate => $svrinfo) {
				$untrust[$regdate]['allSum'] = array_sum($untrust[$regdate]);//无效注册人数
			}
		}
		//----------------------------------------------------------
			$sql = "select date,sid,sum(reg_valid) regAll,sum(replay) replayAll,sum(relocation) relocationAll,$fields,$fields2 from stat_allserver.stat_retention_daily_pf_country_referrer_appVersion where sid in($sids) $whereSql  group by date,sid order by date,sid;";
		$ret = query_infobright($sql);
//		print_r($sql);
		foreach ($ret['ret']['data'] as $row) {
			$server = 's' . $row['sid'];
			$regdate = $row['date'];
			$registerUser[$regdate][$server] = $row['regAll']; //新注册
			$replayUser[$regdate][$server] = intval($row['replayAll']);//重玩
			$relocationUser[$regdate][$server] = intval($row['relocationAll']);//迁服
			foreach ($dayArr as $day) { //净留存
				$count = $row['r' . $day] ? $row['r' . $day] : 0;
				$fact = $row['regAll'] - $untrust[$regdate][$server];
				$remainData[$server][$regdate][$day] = array('count' => $count, 'rate' => ($fact > 0 ? intval($count / $fact * 10000) / 100 : 0));
				$remainData['allSum'][$regdate][$day]['count'] += $count;
			}
			foreach ($dayArr2 as $day) {//留存
				$count = intval($row['rr' . $day]) + intval($row['r' . $day]);
				$total = intval($row['regAll'] + $row['replayAll']-$untrust[$regdate][$server]);
				$remainData[$server][$regdate]["rr"] = array('count' => $count, 'rate' => ($total > 0 ? intval($count / $total * 10000) / 100 : 0));
				$remainData['allSum'][$regdate]["rr"]['count'] += $count;
			}
		}

		//=======全服合计
		foreach ($registerUser as $regdate => $svrinfo) {
			$registerUser[$regdate]['allSum'] = array_sum($registerUser[$regdate]);
			$replayUser[$regdate]['allSum'] = array_sum($replayUser[$regdate]);
			$relocationUser[$regdate]['allSum'] = array_sum($relocationUser[$regdate]);
		}
		foreach ($remainData['allSum'] as $regdate => $daycount) {
			foreach ($daycount as $day => $value) {
				if ($day == 'rr') { //留存
					$a = $registerUser[$regdate]['allSum'] + $replayUser[$regdate]['allSum']- $untrust[$regdate]['allSum'];
					$remainData['allSum'][$regdate][$day]['rate'] = $a > 0 ? intval($value['count'] / $a * 10000) / 100 : 0;
				} else {//净留存
					$a = $registerUser[$regdate]['allSum'] - $untrust[$regdate]['allSum'];
					$remainData['allSum'][$regdate][$day]['rate'] = $a > 0 ? intval($value['count'] / $a * 10000) / 100 : 0;
				}
			}
		}


		if ($_REQUEST['datekey']) {
			$sql = "INSERT into operation_log (date,logs) VALUES (" . $_REQUEST['datekey'] . "," . "'" . $_REQUEST['num'] . "'" . ") ";
			$sql .= " ON DUPLICATE KEY UPDATE date=" . $_REQUEST['datekey'] . " ,logs=" . "'" . $_REQUEST['num'] . "'";
			$page->globalExecute($sql, 2);
		}
		$log_sql = "select * from operation_log where date >=$startYmd and date <= $endYmd;";
		$log_ret = $page->globalExecute($log_sql, 3);
		foreach ($log_ret['ret']['data'] as $row) {
			$date = $row['date'];
			$num[$date] = $row['logs'];
		}

		
	} catch ( Exception $e ) {
		$error_msg = $e->getMessage ();
	}
}
if($_REQUEST['event']=='output'){
	$start = $_REQUEST['start'];
	$end = $_REQUEST['end'];
	if (!$_REQUEST['reconnect']) {
		$dayArr = array(1, 3, 7, 15, 30);
	} else {
		$reconnect = $_REQUEST['reconnect'];
		$dayArr = explode(',', $reconnect);
		if (count($dayArr) == 1) {
			$explodeArr = explode('-', $reconnect);
			if (count($explodeArr) > 1) {
				$dayArr = array();
				$index = $explodeArr[0];
				while ($index <= $explodeArr[1])
					$dayArr[] = $index++;
			}
		}
	}
	$startYmd = date('Ymd', strtotime($start));
	$endYmd = date('Ymd', strtotime($end));
	$sids = implode(',', $selectServerids);
	foreach ($dayArr as $day) {
		$rfields[] = "sum(" . 'r' . $day . ") as " . 'r' . $day;
	}
	$fields = implode(',', $rfields);
	$dayArr2 = array(1);
	foreach ($dayArr2 as $day) {
		$rfields2[] = "sum(" . 'rr' . $day . ") as " . 'rr' . $day;
	}
	$fields2 = implode(',', $rfields2);
	$whereSql .= " and date >=$startYmd and date <= $endYmd ";
	$whereSql2 .= " and date >=$startYmd and date <= $endYmd ";
	if ($currCountry && $currCountry != 'ALL') {
		$whereSql .= " and country='$currCountry' ";
		$whereSql2 .= " and country='$currCountry' ";
	}
	if ($currPf && $currPf != 'ALL') {
		$whereSql .= " and pf='$currPf' ";
		$whereSql2 .= " and pf='$currPf' ";
	} else if ($currPf == 'ALL' && $_COOKIE['u'] == 'xiaomi') {
		$whereSql2 .= " and pf in('cn_360','cn_am','cn_anzhi','cn_baidu','cn_dangle','cn_ewan','cn_huawei','cn_kugou','cn_kupai','cn_lenovo','cn_mi','cn_mihy','cn_mzw','cn_nearme','cn_pps','cn_pptv','cn_sogou','cn_toutiao','cn_uc','cn_vivo','cn_wdj','cn_wyx','cn_youku','cn_oppo','cn_sy37','cn_mz','tencent') ";
	}
	if ($currReferrer && $currReferrer != 'ALL') {
		$whereSql .= " and referrer='$currReferrer' ";
	}
	$whereSql2 .= " and referrer='untrusted' ";

	if ($currAppVersion && $currAppVersion != 'ALL') {
		$whereSql .= " and appVersion='$currAppVersion' ";
		$whereSql2 .= " and appVersion='$currAppVersion' ";
	}

	//添加不信人数
	if($currReferrer == 'ALL') {
		$sql2 = "select date,sid,sum(reg_valid) reg_untrust from stat_allserver.stat_retention_daily_pf_country_referrer_appVersion where sid in($sids) $whereSql2  group by date,sid order by date,sid;";
		$ret = query_infobright($sql2);
		foreach ($ret['ret']['data'] as $row) {
			$server = 's' . $row['sid'];
			$regdate = $row['date'];
			$untrust[$regdate][$server] = $row['reg_untrust'];
		}
		//全服
		foreach ($untrust as $regdate => $svrinfo) {
			$untrust[$regdate]['allSum'] = array_sum($untrust[$regdate]);//无效注册人数
		}
	}
	//----------------------------------------------------------
		$sql = "select date,sid,sum(reg_valid) regAll,sum(replay) replayAll,sum(relocation) relocationAll,$fields,$fields2 from stat_allserver.stat_retention_daily_pf_country_referrer_appVersion where sid in($sids) $whereSql  group by date,sid order by date,sid;";
	$ret = query_infobright($sql);
//		print_r($sql);
	foreach ($ret['ret']['data'] as $row) {
		$server = 's' . $row['sid'];
		$regdate = $row['date'];
		$registerUser[$regdate][$server] = $row['regAll']; //新注册
		$replayUser[$regdate][$server] = intval($row['replayAll']);//重玩
		$relocationUser[$regdate][$server] = intval($row['relocationAll']);//迁服
		foreach ($dayArr as $day) { //净留存
			$count = $row['r' . $day] ? $row['r' . $day] : 0;
			$fact = $row['regAll'] - $untrust[$regdate][$server];
			$remainData[$server][$regdate][$day] = array('count' => $count, 'rate' => ($fact > 0 ? intval($count / $fact * 10000) / 100 : 0));
			$remainData['allSum'][$regdate][$day]['count'] += $count;
		}
		foreach ($dayArr2 as $day) {//留存
			$count = intval($row['rr' . $day]) + intval($row['r' . $day]);
			$total = intval($row['regAll'] + $row['replayAll']-$untrust[$regdate][$server]);
			$remainData[$server][$regdate]["rr"] = array('count' => $count, 'rate' => ($total > 0 ? intval($count / $total * 10000) / 100 : 0));
			$remainData['allSum'][$regdate]["rr"]['count'] += $count;
		}
	}

	//=======全服合计
	foreach ($registerUser as $regdate => $svrinfo) {
		$registerUser[$regdate]['allSum'] = array_sum($registerUser[$regdate]);
		$replayUser[$regdate]['allSum'] = array_sum($replayUser[$regdate]);
		$relocationUser[$regdate]['allSum'] = array_sum($relocationUser[$regdate]);
	}
	foreach ($remainData['allSum'] as $regdate => $daycount) {
		foreach ($daycount as $day => $value) {
			if ($day == 'rr') { //留存
				$a = $registerUser[$regdate]['allSum'] + $replayUser[$regdate]['allSum']- $untrust[$regdate]['allSum'];
				$remainData['allSum'][$regdate][$day]['rate'] = $a > 0 ? intval($value['count'] / $a * 10000) / 100 : 0;
			} else {//净留存
				$a = $registerUser[$regdate]['allSum'] - $untrust[$regdate]['allSum'];
				$remainData['allSum'][$regdate][$day]['rate'] = $a > 0 ? intval($value['count'] / $a * 10000) / 100 : 0;
			}
		}
	}


	if ($_REQUEST['datekey']) {
		$sql = "INSERT into operation_log (date,logs) VALUES (" . $_REQUEST['datekey'] . "," . "'" . $_REQUEST['num'] . "'" . ") ";
		$sql .= " ON DUPLICATE KEY UPDATE date=" . $_REQUEST['datekey'] . " ,logs=" . "'" . $_REQUEST['num'] . "'";
		$page->globalExecute($sql, 2);
	}
	$log_sql = "select * from operation_log where date >=$startYmd and date <= $endYmd;";
	$log_ret = $page->globalExecute($log_sql, 3);
	foreach ($log_ret['ret']['data'] as $row) {
		$date = $row['date'];
		$num[$date] = $row['logs'];
	}

	$title = array('20%'=>'日期','8%'=>'服','------','新注册','untrusted','------','重玩','------','迁服','------','第1天登录(新注册+重玩)','留存(%)','净第1天登录','净留存(%)','------','净第3天登录','净留存(%)','------','净第7天登录','净留存(%)','净第15天登录','净留存(%)','------','净第30天登录','净留存(%)','------','运营备注信息');
	
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
	foreach ($registerUser as $dateValue=>$keyvalue){
		foreach ($keyvalue as $serverValue=>$item){
			if($serverValue=='allSum') continue;
			$Excel->setCellValue($titleIndex[0].''.$row, $dateValue);
			$Excel->setCellValue($titleIndex[1].''.$row,$serverValue);
			$Excel->setCellValue($titleIndex[2].''.$row,'------');
			$Excel->setCellValue($titleIndex[3].''.$row,$registerUser[$dateValue][$serverValue]);
			$Excel->setCellValue($titleIndex[4].''.$row,$untrust[$dateValue][$serverValue]);


			$Excel->setCellValue($titleIndex[5].''.$row,'------');
			$Excel->setCellValue($titleIndex[6].''.$row,$replayUser[$dateValue][$serverValue]);
			$Excel->setCellValue($titleIndex[7].''.$row,'------');
			$Excel->setCellValue($titleIndex[8].''.$row,$relocationUser[$dateValue][$serverValue]);
			$Excel->setCellValue($titleIndex[9].''.$row,'------');
			$Excel->setCellValue($titleIndex[10].''.$row,$remainData[$serverValue][$dateValue]['rr']['count']);
			$Excel->setCellValue($titleIndex[11].''.$row,$remainData[$serverValue][$dateValue]['rr']['rate']);

			$Excel->setCellValue($titleIndex[12].''.$row,$remainData[$serverValue][$dateValue][1]['count']);
			$Excel->setCellValue($titleIndex[13].''.$row,$remainData[$serverValue][$dateValue][1]['rate']);
			$Excel->setCellValue($titleIndex[14].''.$row,'------');
			$Excel->setCellValue($titleIndex[15].''.$row,$remainData[$serverValue][$dateValue][3]['count']);
			$Excel->setCellValue($titleIndex[16].''.$row,$remainData[$serverValue][$dateValue][3]['rate']);
			$Excel->setCellValue($titleIndex[17].''.$row,'------');
			$Excel->setCellValue($titleIndex[18].''.$row,$remainData[$serverValue][$dateValue][7]['count']);
			$Excel->setCellValue($titleIndex[19].''.$row,$remainData[$serverValue][$dateValue][7]['rate']);
			$Excel->setCellValue($titleIndex[20].''.$row,'------');

			$Excel->setCellValue($titleIndex[21].''.$row,$remainData[$serverValue][$dateValue][15]['count']);
			$Excel->setCellValue($titleIndex[22].''.$row,$remainData[$serverValue][$dateValue][15]['rate']);
			$Excel->setCellValue($titleIndex[23].''.$row,'------');

			$Excel->setCellValue($titleIndex[24].''.$row,$remainData[$serverValue][$dateValue][30]['count']);
			$Excel->setCellValue($titleIndex[25].''.$row,$remainData[$serverValue][$dateValue][30]['rate']);
			$Excel->setCellValue($titleIndex[26].''.$row,'------');
			$Excel->setCellValue($titleIndex[27].''.$row,$num[$dateValue]);
			$row++;
		}
	}
	//filename
	$file_name = '每日注册留存';
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
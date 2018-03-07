<?php
!defined('IN_ADMIN') && exit('Access Denied');
global $servers;
$allServerFlag=false;
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

if($_REQUEST['allServers']){
	$allServerFlag =true;
}
if(!$_REQUEST['start'])
	$start = date("Y-m-d",time()-86400*7);
if(!$_REQUEST['end'])
	$end = date("Y-m-d",time());
if ($_REQUEST['event']=='search') {
	try {
		$start = $_REQUEST['start'];
		$end = $_REQUEST['end'];
		if(!$_REQUEST['reconnect'])
		{
			$dayArr = array(1,3,7,15,30);
		}
		else 
		{
			$reconnect = $_REQUEST['reconnect'];
			$dayArr = explode(',',$reconnect);
			if(count($dayArr) == 1)
			{
				$explodeArr = explode('-',$reconnect);
				if(count($explodeArr) > 1)
				{
					$dayArr = array();
					$index = $explodeArr[0];
					while($index <= $explodeArr[1])
						$dayArr[] = $index++;
				}
			}
		}
		$startYmd = date('Ymd',strtotime($start));
		$endYmd = date('Ymd',strtotime($end));
		$sids = implode(',', $selectServerids);
		foreach ($dayArr as $day) {
			$rfields[] = "sum(".'p'.$day.") as ".'p'.$day;
		}
		$fields = implode(',', $rfields);
		$whereSql .= " and date >=$startYmd and date <= $endYmd ";
		if($currCountry&&$currCountry!='ALL'){
			$whereSql .=" and country='$currCountry' ";
			$whereSql2 .=" and country='$currCountry' ";
		}
		if($currPf&&$currPf!='ALL'){
			$whereSql .=" and pf='$currPf' ";
			$whereSql2 .=" and pf='$currPf' ";
		}else if ($currPf=='ALL' && $_COOKIE['u']=='xiaomi'){
			$whereSql .=" and pf in('cn_360','cn_am','cn_anzhi','cn_baidu','cn_dangle','cn_ewan','cn_huawei','cn_kugou','cn_kupai','cn_lenovo','cn_mi','cn_mihy','cn_mzw','cn_nearme','cn_pps','cn_pptv','cn_sogou','cn_toutiao','cn_uc','cn_vivo','cn_wdj','cn_wyx','cn_youku','cn_oppo','cn_sy37','cn_mz','tencent') ";
			$whereSql2 .=" and pf in('cn_360','cn_am','cn_anzhi','cn_baidu','cn_dangle','cn_ewan','cn_huawei','cn_kugou','cn_kupai','cn_lenovo','cn_mi','cn_mihy','cn_mzw','cn_nearme','cn_pps','cn_pptv','cn_sogou','cn_toutiao','cn_uc','cn_vivo','cn_wdj','cn_wyx','cn_youku','cn_oppo','cn_sy37','cn_mz','tencent') ";
		}
		if($currReferrer&&$currReferrer!='ALL'){
			$whereSql .=" and referrer='$currReferrer' ";
			$whereSql2 .=" and referrer='$currReferrer' ";
		}
//date;服;新注册用户数;dau;---;付费dau;付费留存;当日付费人数;当日付费次数;当日付费金额;新增付费用户;---;新增付费用户次留;3日留;7;15;30;
		//累计付费人数   新增付费金额
		//===========================================1===============================================================
		$sql = "select date,sid,sum(reg_valid) regAll,sum(pay_all) payAll,sum(dau) dauAll,$fields from stat_allserver.stat_retention_daily_pf_country_referrer_appVersion WHERE sid in($sids) $whereSql group by date,sid order by date,sid";
		$ret = query_infobright($sql);
		checkiferror($ret,$sql);
		foreach ($ret['ret']['data'] as $row) {
			$server = 's'.$row['sid'];
			$regdate = $row['date'];
			$registerUser[$regdate][$server] = $row['regAll'];//新增注册数
			$dauAll[$regdate][$server] = intval($row['dauAll']);//dau
			$payUser[$regdate][$server] = $row['payAll']; //新增付费用户数(去重)

			//p.1 p.3 p.5 p.7....
			foreach ($dayArr as $day) {
				$count = $row['p'.$day]?$row['p'.$day]:0;
				$remainData[$server][$regdate][$day] = array('count'=>$count,'rate'=>($row['payAll']>0?intval($count/$row['payAll']*10000)/100:0));
				$remainData['allSum'][$regdate][$day]['count'] += $count;
			}
		}

		//=============================================2=============================================================
		$sql1 = "select date,sid,sum(paid_dau) paydau,sum(pdau_relocation) as pdau_relocation from stat_allserver.stat_dau_daily_pf_country_referrer WHERE sid in($sids) $whereSql group by date,sid order by date,sid ";
		$ret = query_infobright($sql1);
		checkiferror($ret,$sql1);

		foreach ($ret['ret']['data'] as $row) {
			$server = 's'.$row['sid'];
			$regdate = $row['date'];

			$paydauAll[$regdate][$server] = intval($row['paydau']);//付费dau
			$paydauAll_relocation[$regdate][$server] = intval($row['pdau_relocation']);//付费dau(迁服)
		}
		//==============================================6============================================================
		$sql6 = "select date,sid,sum(payTotle) paytotle ,sum(payUsers) payusers ,sum(payTimes) paytimes,type from stat_allserver.pay_analyze_pf_country_referrer_new WHERE sid in($sids) $whereSql  group by date,sid,type order by date,sid";
		$ret = query_infobright($sql6);
		checkiferror($ret,$sql6);

		foreach ($ret['ret']['data'] as $row) {
			$server = 's'.$row['sid'];
			$regdate = $row['date'];

			if( $row['type']==2) {
				$paytotal_re[$regdate][$server] = floatval($row['paytotle']);//当日付费金额付费dau(迁服)
			}
			$payusers[$regdate][$server] += intval($row['payusers']);//当日付费人数
			$paytimes[$regdate][$server] += intval($row['paytimes']);//当日付费次数
			$paytotal[$regdate][$server] += floatval($row['paytotle']);//当日付费金额
		}

		//==============================================5============================================================
		foreach ($selectServerids as $sid) {
			if($sid == 0) continue;
			$snapshotdb = "snapshot_s".$sid;//s1 s2 s3
			$sql5 = "select pp.date as date, sum(pp.spend) cnt from (select min(p.date) as date ,p.uid uid,p.spend spend from $snapshotdb.paylog p group by p.uid) pp where pp.date >=$startYmd and pp.date <= $endYmd group by pp.date order by pp.date";
			$ret = query_infobright($sql5);

//			checkiferror($ret,$sql5);
			foreach ($ret['ret']['data'] as $row) {
				$server= 's'.$sid;
				$newPayUsers[$row['date']][$server] = $row['cnt'] ;//新增付费用户付费,(第一笔),这人首付,付第二次不算进去
			}
		}
		//==============================================4============================================================

//		$sql3 = "select date,sid,sum(payUsers) payTotalUsers from stat_allserver.pay_analyze_pf_country_referrer WHERE sid in($sids) $whereSql group by date,sid order by date,sid";
		///--------4----
		$sql4 = "select sid,date,totalUsers from stat_allserver.pay_payAnalyze_7day WHERE sid in($sids) $whereSql  group by sid,date";
		$ret_before = query_infobright($sql4);
		checkiferror($ret_before,$sql4);
		foreach ($ret_before['ret']['data'] as $row) {
			$server= 's'.$row['sid'];
			$date = $row['date'];
			$payTotalUsers[$date][$server] = $row['totalUsers'] ;//累计付费人数 去重的
			$remainPayData[$date][$server] = $payTotalUsers[$date][$server] >0 ? intval($paydauAll[$date][$server]/$payTotalUsers[$date][$server]*10000)/100 :0;

		}
		//----------------------------全服合计-------------------------------
		foreach ($registerUser as $regdate => $svrinfo) {
			$alltotal = array_sum($svrinfo);
			$registerUser[$regdate]['allSum'] = $alltotal; //新注册合计
			$dauAll[$regdate]['allSum'] = array_sum($dauAll[$regdate]);
			$paydauAll[$regdate]['allSum'] = array_sum($paydauAll[$regdate]);
			$paydauAll_relocation[$regdate]['allSum'] = array_sum($paydauAll_relocation[$regdate]);
			$paytotal_re[$regdate]['allSum'] = array_sum($paytotal_re[$regdate]);
			$payusers[$regdate]['allSum'] = array_sum($payusers[$regdate]);
			$paytimes[$regdate]['allSum'] = array_sum($paytimes[$regdate]);
			$paytotal[$regdate]['allSum'] = array_sum($paytotal[$regdate]);
			$payUser[$regdate]['allSum'] = array_sum($payUser[$regdate]);
			$payTotalUsers[$regdate]['allSum'] = array_sum($payTotalUsers[$regdate]);
			$remainPayData[$regdate]['allSum'] = $payTotalUsers[$regdate]['allSum'] >0 ? intval($paydauAll[$regdate]['allSum']/$payTotalUsers[$regdate]['allSum']*10000)/100 :0;
			$newPayUsers[$regdate]['allSum'] = array_sum($newPayUsers[$regdate]);

		}
		//p.1 p.3 p.5 p.7....合计
		foreach ($remainData['allSum'] as $regdate => $daycount) {
			foreach ($daycount as $day => $value) {
				$remainData['allSum'][$regdate][$day]['rate'] = $registerUser[$regdate]['allSum']>0? intval($value['count'] / $payUser[$regdate]['allSum'] *10000)/100 :0;
			}
		}

		
	} catch ( Exception $e ) {
		$error_msg = $e->getMessage ();
	}
}
if($_REQUEST['event']=='output'){
	exit();




	$dayArr = array(1,3,7,15,30);
	$startYmd = date('Ymd',strtotime($start));
	$endYmd = date('Ymd',strtotime($end));
	$sids = implode(',', $selectServerids);
	foreach ($dayArr as $day) {
		$rfields[] = "sum(".'ss.p'.$day.") as ".'p'.$day;
	}
	$fields = implode(',', $rfields);
	$whereSql .= " and ss.date >=$startYmd and ss.date <= $endYmd ";
	if($currCountry&&$currCountry!='ALL'){
		$whereSql .=" and ss.country='$currCountry' ";
	}
	if($currPf&&$currPf!='ALL'){
		$whereSql .=" and ss.pf='$currPf' ";
	}else if ($currPf=='ALL' && $_COOKIE['u']=='xiaomi'){
		$whereSql .=" and ss.pf in('cn_360','cn_am','cn_anzhi','cn_baidu','cn_dangle','cn_ewan','cn_huawei','cn_kugou','cn_kupai','cn_lenovo','cn_mi','cn_mihy','cn_mzw','cn_nearme','cn_pps','cn_pptv','cn_sogou','cn_toutiao','cn_uc','cn_vivo','cn_wdj','cn_wyx','cn_youku','cn_oppo','cn_sy37','cn_mz','tencent') ";
	}
	if($currReferrer&&$currReferrer!='ALL'){
		$whereSql .=" and ss.referrer='$currReferrer' ";
	}
	if($currAppVersion&&$currAppVersion!='ALL'){
		$whereSql .=" and ss.appVersion='$currAppVersion' ";
	}

	$sql = "select ss.date,ss.sid,sum(ss.reg_valid) regAll,sum(ss.pay_all) payAll,sum(ss.dau) dauAll,sum(rr.paid_dau) paydau ,sum(sp.payTotle) paytotle ,sum(sp.payUsers) psyusers ,sum(sp.payTimes) paytimes, $fields
from stat_allserver.stat_retention_daily_pf_country_referrer_appVersion as ss
INNER join stat_allserver.stat_dau_daily_pf_country_referrer as rr on ss.date=rr.date  and ss.sid=rr.sid and ss.referrer=rr.referrer and ss.country=rr.country and ss.pf=rr.pf
INNER JOIN stat_allserver.pay_analyze_pf_country_referrer_new as sp on ss.date=sp.date  and ss.sid=sp.sid and ss.referrer=sp.referrer and ss.country=sp.country and ss.pf=sp.pf
where ss.sid in($sids) $whereSql  group by ss.date,ss.sid order by ss.date,ss.sid;";

	//date;服;新注册用户数;dau;---;付费dau;------;当日付费人数;当日付费次数;当日付费金额;新增付费用户;----;新增付费用户次留;3日留;7;15;30;
	//累计付费人数  付费留存   新增付费金额
	$ret = query_infobright($sql);
	foreach ($ret['ret']['data'] as $row) {
		$server = 's'.$row['sid'];
		$regdate = $row['date'];
		$registerUser[$regdate][$server] = $row['regAll'];//新增注册
		$dauAll[$regdate][$server] = intval($row['dauAll']);//dau

		$paydauAll[$regdate][$server] = intval($row['paydau']);//付费dau
		$payusers[$regdate][$server] = intval($row['psyusers']);//当日付费人数
		$paytimes[$regdate][$server] = intval($row['paytimes']);//当日付费次数
		$paytotal[$regdate][$server] = intval($row['paytotle']);//当日付费金额
		$payUser[$regdate][$server] = $row['payAll']; //新增付费用户数(去重)

		//p.1 p.3 p.5 p.7....
		foreach ($dayArr as $day) {
			$count = $row['p'.$day]?$row['p'.$day]:0;
			$remainData[$server][$regdate][$day] = array('count'=>$count,'rate'=>($row['payAll']>0?intval($count/$row['payAll']*10000)/100:0));
		}
	}


	$title = array('20%'=>'日期','8%'=>'服','新注册','DAU','累计付费人数','付费DAU','付费留存','当日付费人数','当日付费次数','当日付费金额','新增付费用户','第1天登录','留存(%)','------','第3天登录','留存(%)','------','第7天登录','留存(%)','------','第15天登录','留存(%)','------','第30天登录','留存(%)');
	
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
	foreach ($dateArray as $dateValue){
		foreach ($serverArray as $serverValue){
			$Excel->setCellValue($titleIndex[0].''.$row, $dateValue);
			$Excel->setCellValue($titleIndex[1].''.$row,$serverValue);
			$Excel->setCellValue($titleIndex[2].''.$row,$registerUser[$dateValue][$serverValue]);
			$Excel->setCellValue($titleIndex[3].''.$row,$dauAll[$dateValue][$serverValue]);
			$Excel->setCellValue($titleIndex[4].''.$row,'--------');//累计付费人数
			$Excel->setCellValue($titleIndex[5].''.$row,$paydauAll[$dateValue][$serverValue]);
			$Excel->setCellValue($titleIndex[6].''.$row,'--------');//付费留存
			$Excel->setCellValue($titleIndex[7].''.$row,$payusers[$dateValue][$serverValue]);
			$Excel->setCellValue($titleIndex[8].''.$row,$paytimes[$dateValue][$serverValue]);
			$Excel->setCellValue($titleIndex[9].''.$row,$paytotal[$dateValue][$serverValue]);
			$Excel->setCellValue($titleIndex[10].''.$row,$payUser[$dateValue][$serverValue]);
			$Excel->setCellValue($titleIndex[11].''.$row,'--------');//新增付费金额

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

			$row++;
		}
	}
	//filename
	$file_name = '新增支付留存';
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

function getdateArray($start,$end=null){
	if(!$end){
		$end =time();
	}
	$days = intval(($end - $start)/86400);
	$datearray=array();
	for ($i=0;$i<=$days;++$i){
		$date=date("Ymd",strtotime("+$i day",$start));
		if(strtotime($date) <= $end){
			$datearray[]=$date;
			continue;
		}else{
			break;
		}
	}
	return $datearray;
}
function checkiferror($ret,$sql){
	if(! $ret['ret']['data']){
		print_r("错误sql--$sql");
	}
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
<?php
!defined('IN_ADMIN') && exit('Access Denied');
date_default_timezone_set('GMT');
$title = "每日支付总额";
$dateMax = date("Y-m-d",time());
$days = 4;
global $servers;
// foreach ($_REQUEST as $server=>$value)
// {
// 	if($servers[$server] && $value == 'on')
// 		$selectServer[] = $server;
// }
$maxServer='';

$pf47=array('cn_360','cn_uc','cn_oppo','cn_aiqiyi','cn_meizu','cn_57k');
$pf48=array('cn_huawei','cn_leshi');

foreach ($servers as $server=>$serverInfo){
	if(substr($server, 0 ,1) != 's'){
	 continue;
	} 
	if (substr($server,1)>900000){
		continue;
	}
	$maxServer=max($maxServer,substr($server,1));
}
$sttt = $_REQUEST['selectServer'];
if (!empty($sttt)) {
	$sttt = str_replace('，', ',', $sttt);
	$sttt = str_replace(' ', '', $sttt);
	$tmp = explode(',', $sttt);
	foreach ($tmp as $tt) {
		$tt = trim($tt);
		if (!empty($tt)) {
			if(strstr($tt,'-')){
				$ttArray=explode('-', $tt);
				$min=min($ttArray[1],$maxServer);
				for ($i=$ttArray[0];$i<=$min;$i++){
					$selectServer['s'.$i] = '';
					$selectId[]=$i;
				}
			}else {
				if($tt<=$maxServer){
					$selectServer['s'.$tt] = '';
					$selectId[]=$tt;
				}
			}
		}
	}
}

if (empty($selectServer)){
	$selectServer = $servers;
	foreach ($servers as $server=>$serverInfo){
		$selectId[]=substr($server, 1);
	}
}
//$currPf为平台
if (!$_REQUEST['selectPf']) {
	$currPf = 'ALL';
}else{
	$currPf = $_REQUEST['selectPf'];
}
//$seletedpf支付渠道
if (!$_REQUEST['selectPayMethod']) {
	$seletedpf = 'all';   
}else{
	$seletedpf = $_REQUEST['selectPayMethod'];
}

foreach ($optionsArr as $pf => $pfdisp){
	$flag = ($seletedpf==$pf)?'selected="selected"':'';
	$pfOptions .= "<option value='{$pf}' $flag>{$pfdisp}</option>";
}

if ($_REQUEST['event']=='output'){ //这导出excel判断,放在开头 其它判断之前;

	$days = $_REQUEST['days'];//天数
//	$startYmd=date('Ymd',strtotime($_REQUEST['dateMax'])-($days-1)*86400);
//	$endYmd=date('Ymd',strtotime($_REQUEST['dateMax']));
	$endTime = strtotime(date('Y-m-d',strtotime($_REQUEST['dateMax'])))*1000 + 86400000;
	$startTime = strtotime(date('Y-m-d',$endTime/1000))*1000 - $days*86400000;

	$m_temp = $startTime;
	$m_dateLink =array(); //日期 正规格式  2016-04-10
	while($m_temp < $endTime){
		$tempDate = date('Y-m-d',$m_temp/1000);
		$m_dateLink[$tempDate] = $tempDate;
		$m_temp += 86400000;
	}

//	$date = $temp[1];
	$m_sids=$selectId;//所选服
	$payMethod = $seletedpf;//支付渠道
	$pf = $currPf; //平台
	$country=$_REQUEST['country']; //国家

	$wherepf = '1=1';
	if (!empty($payMethod) && $payMethod!='all') {
		$wherepf .= " and p.pf='$payMethod'";
	}
	$regPf='';
	if (!empty($pf) && $pf!='ALL') {
		$regPf .= " and r.pf='$pf' ";
	}
	$countryWhere=null;
	if ($country=="Unknown"){
		$countryWhere = " and (r.country is null or r.country='' or r.country='Unknown') ";
	}elseif($country=="GunFu"){
		$countryWhere2 = " and p.uid not in (select r.uid from stat_reg r where 1=1 $regPf) ";
	}elseif(!empty($country)&&$country!="ALL"){
		$countryWhere = " and r.country ='$country' ";
	}

//	exit(print_r($m_sids));
	$m_total = array();//二维数组,导出excel所有数据
	foreach($m_sids as $m_sid){
		if($m_sid == 0) continue;
		$m_sid = 's'.$m_sid;
		$datearr = array();
		foreach ($m_dateLink as $m_date) {
			$ts_start = strtotime($m_date);
			$ts_end = $ts_start+84600;
			$ts_start *= 1000;
			$ts_end *= 1000;

			if (!empty($countryWhere)) {
				$sql = "select p.productId ,count(1) num from paylog p left join stat_reg r on p.uid=r.uid where $wherepf $regPf $countryWhere and p.time >= $ts_start and p.time < $ts_end group by productId ASC ;";
			} elseif (!empty($countryWhere2)) {
				$sql = "select p.productId ,count(1) num from paylog p where $wherepf $countryWhere2 and p.time >= $ts_start and p.time < $ts_end group by productId ASC ;";
			} else {
				$sql = "select p.productId ,count(1) num from paylog p left join stat_reg r on p.uid=r.uid where $wherepf $regPf and p.time >= $ts_start and p.time < $ts_end group by productId ASC ;";
			}

			$displayResult = $page->executeServer($m_sid, $sql, 3);
			$cot = array();
			foreach ($displayResult['ret']['data'] as $disRow){
//				$ids[] = $disRow['productId'];//存着id
//				$cot[$disRow['productId']] = $disRow['num']; //个数
				$cot += array($disRow['productId'] => $disRow['num']);
//				$m_total[$m_sid][$m_date][$disRow['productId']] == $disRow['num'];
			}
			$datearr += array($m_date=>$cot);
		}
		$m_total += array($m_sid=>$datearr);
	}

//exit(print_r($m_total));
//	$disHtml = "<div><table class='listTable' style='text-align:center'><thead>";
//	$disHtml ="<th>sid</th>&nbsp;<th>DATE</th>&nbsp;<th>Id</th>&nbsp;<th>Name</th>&nbsp;<th>price</th><th>times</th>&nbsp;<th>totalMoney</th></thead>";
//	foreach($m_sids as $m_sid) {
//		if ($m_sid == 0) continue;
//		foreach ($m_dateLink as $m_date) {
//			foreach ($m_total[$m_sid][$m_date] as $idkeys=>$timesValue){
//
//				$tot= $exchangeName[$idkeys][1]*$timesValue;
//				$disHtml .="<tr><td>$m_sid</td>&nbsp;<td>$m_date</td>&nbsp;<td>$idkeys</td>&nbsp;<td>".$exchangeName[$idkeys][0]."</td>&nbsp;<td>".$exchangeName[$idkeys][1]."</td><td>$timesValue</td><&nbsp;td>$tot</td></tr>";
//			}
//		}
//	}
//	$disHtml .="</table></div>";
//
//	echo $disHtml;
//	exit();

	$title = array('服','日期','ID','Name','单价','次数','总额');
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
	foreach ($title as $value){
		$objPHPExcel->getActiveSheet()->getColumnDimension(getNameFromNumber($line))->setWidth(6);

//		$objPHPExcel->getActiveSheet()->getColumnDimension(getNameFromNumber($line))->setAutoSize(true);
		$Excel->setCellValue(getNameFromNumber($line++).''.$row,$value);
	}
	$row++;
	if(empty($m_total)){
		$Excel->setCellValue(getNameFromNumber(0).''.$row,'空');
	}
	foreach($m_total as $m_sid=>$item){
		foreach($item as $m_date=>$m_arr2){
			foreach($m_arr2 as $idkeys=>$timesValue){

				$tot= $exchangeName[$idkeys][1]*$timesValue; //总额= 单价*次数
//				$disHtml .="<tr><td>$m_sid</td>&nbsp;<td>$m_date</td>&nbsp;<td>$idValue</td>&nbsp;<td>".$exchangeName[$idValue][0]."</td>&nbsp;<td>".$exchangeName[$idValue][1]."</td><td>$cot[$idValue]</td><&nbsp;td>$tot</td></tr>";
				$i=0;
				$Excel->setCellValue(getNameFromNumber($i++).''.$row, $m_sid);
				$Excel->setCellValue(getNameFromNumber($i++).''.$row, $m_date);
				$Excel->setCellValue(getNameFromNumber($i++).''.$row, $idkeys);
				$Excel->setCellValue(getNameFromNumber($i++).''.$row, $exchangeName[$idkeys][0]);
				$Excel->setCellValue(getNameFromNumber($i++).''.$row, $exchangeName[$idkeys][1]);
				$Excel->setCellValue(getNameFromNumber($i++).''.$row, $timesValue);
				$Excel->setCellValue(getNameFromNumber($i++).''.$row, $tot);
				$row++;
			}
		}
	}

	//filename
	$file_name = '支付总额数据';
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
if($_REQUEST['display']){
	$serverDate=$_REQUEST['serverDate'];
	//s1_total_all_ALL_ALL_1460505600000-1461110400000  第一列
//	   s1_2016-04-18_all_ALL_ALL 普通
//	0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26_2016-04-20_all_ALL_ALL  第一行
	if ($_COOKIE['u']=='xiaomi'){
		$temp = explode('_', $serverDate,3);
	}else {
		$temp = explode('_', $serverDate);
	}
	$displayServer = $temp[0];
	$date = $temp[1];
	$payMethod = $temp[2];//支付渠道
	$pf =$temp[3];//平台
	$country = $temp[4];

	if($date != "total") {
		$ts_start = strtotime($date);
		$ts_end = $ts_start+86400;
		$ts_start *= 1000;
		$ts_end *= 1000;
	}else{
		$date2tmp = $temp[5];
		$date2 = explode('-',$date2tmp);
		$ts_start = $date2[0];
		$ts_end = $date2[1];
	}
	$wherepf = '1=1';
	if (!empty($payMethod) && $payMethod!='all') {
		$wherepf .= " and p.pf='$payMethod'";
	}
	$regPf='';
	if (!empty($pf) && $pf!='ALL') {
		$regPf .= " and r.pf='$pf' ";
	}
	$countryWhere=null;
	if ($country=="Unknown"){
		$countryWhere = " and (r.country is null or r.country='' or r.country='Unknown') ";
	}elseif($country=="GunFu"){
		$countryWhere2 = " and p.uid not in (select r.uid from stat_reg r where 1=1 $regPf) ";
	}elseif(!empty($country)&&$country!="ALL"){
		$countryWhere = " and r.country ='$country' ";
	}

	if (!empty($countryWhere)) {
		$sql = "select p.productId ,count(1) num from paylog p left join stat_reg r on p.uid=r.uid where $wherepf $regPf $countryWhere and p.time >= $ts_start and p.time < $ts_end group by productId ASC ;";
	} elseif (!empty($countryWhere2)) {
		$sql = "select p.productId ,count(1) num from paylog p where $wherepf $countryWhere2 and p.time >= $ts_start and p.time < $ts_end group by productId ASC ;";
	} else {
		$sql = "select p.productId ,count(1) num from paylog p left join stat_reg r on p.uid=r.uid where $wherepf $regPf and p.time >= $ts_start and p.time < $ts_end group by productId ASC ;";
	}
// 	print_r($sql);
	//$diaplaySql="select productId ,count(1) num from paylog where time>=$ts_start and time<$ts_end group by productId";

	$cot=array();
	$ids=array();
	$pieces = explode(",", $displayServer);
	if(is_array($pieces) && count($pieces) > 1){
//		echo "进来了<br />";
//		0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26
		foreach($pieces as $Sid){
			if($Sid == 0) continue;
			$qsid = 's'.$Sid;
			$displayResult = $page->executeServer($qsid, $sql, 3);
			foreach ($displayResult['ret']['data'] as $disRow){
				if(in_array($disRow['productId'],$ids)){ //array_key_exists不能用这个
					$cot[$disRow['productId']] += $disRow['num'];
//					echo "11111<br />";
				}
				else {
//					echo "22222<br />";
					$ids[] = $disRow['productId'];
					$cot[$disRow['productId']] = $disRow['num'];
				}
			}
		}
	}else
	{

//		echo "没进来<br />";
//		echo "$displayServer<br />";
		$displayResult = $page->executeServer($displayServer, $sql, 3);
		foreach ($displayResult['ret']['data'] as $disRow){

				$ids[] = $disRow['productId'];
				$cot[$disRow['productId']] = $disRow['num'];

		}
	}
	asort($ids);
	$disHtml = "服务器:$displayServer&nbsp;&nbsp;日期:$date<div><table class='listTable' style='text-align:center'><thead>";
	$disHtml .="<th>Id</th><th>Name</th><th>单价</th><th>次数</th><th>总额</th></thead>";
	foreach ($ids as $idValue){

		$tot= $exchangeName[$idValue][1]*$cot[$idValue];
		$disHtml .="<tr><td>$idValue</td><td>".$exchangeName[$idValue][0]."</td><td>".$exchangeName[$idValue][1]."</td><td>$cot[$idValue]</td><td>$tot</td></tr>";
	}
	$disHtml .="</table></div>";
	echo $disHtml;
	exit();
}
//导出excel


if (isset($_REQUEST['getData'])) {
	$server_open_days = get_server_open_day_list();
	$days = $_REQUEST['days'];
	$country=$_REQUEST['country'];
	$startYmd=date('Ymd',strtotime($_REQUEST['dateMax'])-($days-1)*86400);
	$endYmd=date('Ymd',strtotime($_REQUEST['dateMax']));
	$sids=implode(',', $selectId);
	$newWhereSql = " where sid in($sids) ";
	$newWhereSql .=" and date between $startYmd and $endYmd ";
	$countryWhere=null;
	$regPf='';
	if (!empty($currPf) && $currPf!='ALL') {
		$regPf .= " and r.pf='$currPf' ";
		$newWhereSql .= " and pf='$currPf' ";
	}elseif ($currPf=='ALL' && $_COOKIE['u']=='xiaomi'){
		$regPf .= " and r.pf in('cn_360','cn_am','cn_anzhi','cn_baidu','cn_dangle','cn_ewan','cn_huawei','cn_kugou','cn_kupai','cn_lenovo','cn_mi','cn_mihy','cn_mzw','cn_nearme','cn_pps','cn_pptv','cn_sogou','cn_toutiao','cn_uc','cn_vivo','cn_wdj','cn_wyx','cn_youku','cn_oppo','cn_sy37','cn_mz','tencent') ";
		$newWhereSql .= " and pf in('cn_360','cn_am','cn_anzhi','cn_baidu','cn_dangle','cn_ewan','cn_huawei','cn_kugou','cn_kupai','cn_lenovo','cn_mi','cn_mihy','cn_mzw','cn_nearme','cn_pps','cn_pptv','cn_sogou','cn_toutiao','cn_uc','cn_vivo','cn_wdj','cn_wyx','cn_youku','cn_oppo','cn_sy37','cn_mz','tencent') ";
	}
	if(!empty($country) && $country!='ALL'){
		$newWhereSql .= " and country='$country' ";
	}
	if ($country=="Unknown"){
		$countryWhere = " and (r.country is null or r.country='' or r.country='Unknown') ";
	}elseif($country=="GunFu"){
		$countryWhere2 = " and p.uid not in (select r.uid from stat_reg r where 1=1 $regPf) ";
	}elseif(!empty($country)&&$country!="ALL"){
		$countryWhere = " and r.country ='$country' ";
	}
	$endTime = strtotime(date('Y-m-d',strtotime($_REQUEST['dateMax'])))*1000 + 86400000;
	$startTime = strtotime(date('Y-m-d',$endTime/1000))*1000 - $days*86400000;
//	if($days>7){
//		$startTime1 = strtotime(date('Y-m-d',$endTime/1000))*1000 - 7*86400000;
//	}else {
//		$startTime1 = strtotime(date('Y-m-d',$endTime/1000))*1000 - $days*86400000;
//	}
	$wherepf = ' 1=1';
	if (!empty($seletedpf) && $seletedpf!='all') {
		$wherepf .= " and p.pf='$seletedpf' ";
		$newWhereSql .= " and payChanel='$seletedpf' ";
	}else{
		$wherepf .= " and p.pf!='iostest' ";
		$newWhereSql .= " and payChanel!='iostest' ";
	}
	if(false && $_COOKIE['u']!='yaoduo'){
		$sql = "select sid, sum(payCount) as payCount, date as logDate from stat_allserver.pay_payTotle_pf_country $newWhereSql group by logDate,sid order by sid;";
		$nameLink['server'] = '服务器';
		$nameLink['open'] = '开服天数';
		$nameLink['total'] = '服务器总和';
		$eventAll['sum']['server'] = '合计';
		$nameLinkSort = array_keys($nameLink);//返回所有keys
		$temp = $startTime;
		$charts_categories = array();
		$chartsPayArrTmp = array();
		$dateLink = array();
		
		while($temp < $endTime){
			$tempDate = date('Y-m-d',$temp/1000);
			$nameLink[$tempDate] = $tempDate;
			$charts_categories[] = $tempDate;
			$nameLinkSort[$endTime - $temp] = $tempDate;
			$temp += 86400000;
			$chartsPayArrTmp[$tempDate] = 0;
			$dateLink[$tempDate] = $tempDate;
		}
		
		krsort($chartsPayArrTmp);
		krsort($dateLink);
		$actAll = array();
		$dayCountAll = array();
		if($days>7){
			$countTotal=array_slice($chartsPayArrTmp,0,7);
		}else{
			$countTotal = $chartsPayArrTmp;
		}
		$charts_series = array();
		$sqlData = array();
		$chartsPayArr = $chartsPayArrTmp;
		$result = query_infobright($sql);
		$serverMap=array();
		foreach ($result['ret']['data'] as $curRow){
			$formatDate=date("Y-m-d",strtotime($curRow['logDate']));
			$server='s'.$curRow['sid'];
			if(!in_array($server, $serverMap)){
				$serverMap[]=$server;
				$eventAll[$server]['server'] = $server;
			}
			$eventAll['sum'][$formatDate] += $curRow['payCount'];
			$eventAll['sum'][$formatDate] = $eventAll['sum'][$formatDate];
			$eventAll[$server][$formatDate] = $curRow['payCount'];
			$eventAll[$server]['total'] += $curRow['payCount'];
			$chartsPayArr[$server][$formatDate] += $curRow['payCount'];
				
		}
		foreach ($selectServer as $server=>$serverInfo){
			if(in_array($server, $serverMap)){
				$eventAll['sum']['total'] += $eventAll[$server]['total'];
				
				if (isset($server_open_days[$server]) && $server_open_days[$server]) {
					$nowDate=date_create(date('Y-m-d'));
					$kaifuDate=date_create($server_open_days[$server]);
					$interval = date_diff($nowDate, $kaifuDate);
					$opendays = $interval->format('%a');
					
					$eventAll[$server]['open'] = $opendays + 1;
				}else{
					$openDaySql = "select daoliangStart from server_info where uid='server'";
					$openDayresult = $page->executeServer($server,$openDaySql,3);
					$openTime = $openDayresult['ret']['data'][0]['daoliangStart'];
					if(empty($openTime)){
						$eventAll[$server]['open'] = 0;
					}else {
						$datetime1 = date_create(date('Y-m-d'));
						$datetime2 = date_create(date('Y-m-d',$openTime/1000));
						$interval = date_diff($datetime1, $datetime2);
						$opendays = $interval->format('%a');
						$eventAll[$server]['open'] = $opendays + 1;
						write_server_open_day($server,date('Y-m-d',$openTime/1000));
					}
				}
				
// 				krsort($chartsPayArr[$server]);
// 				$chartsPayArrTemp = array_values($chartsPayArr[$server]);
// 				$charts_series[] = array('name'=>$server,'data'=>$chartsPayArrTemp);
			}
		}	
	}
	else{
		if(!empty($countryWhere)){
			$sql = "select sum(p.spend) as payCount,date_format(from_unixtime(p.time/1000),'%Y-%m-%d') as logDate from paylog p left join stat_reg r on p.uid=r.uid where $wherepf $regPf $countryWhere and p.time >= $startTime and p.time < $endTime group by logDate;";
		}elseif(!empty($countryWhere2)){
			$sql = "select sum(p.spend) as payCount,date_format(from_unixtime(p.time/1000),'%Y-%m-%d') as logDate from paylog p where $wherepf $countryWhere2 and p.time >= $startTime and p.time < $endTime group by logDate;";
		}else{
			$sql = "select sum(p.spend) as payCount,date_format(from_unixtime(p.time/1000),'%Y-%m-%d') as logDate from paylog p left join stat_reg r on p.uid=r.uid where $wherepf $regPf and p.time >= $startTime and p.time < $endTime group by logDate;";
			if(!$regPf){
				$sql = "select sum(p.spend) as payCount,date_format(from_unixtime(p.time/1000),'%Y-%m-%d') as logDate from paylog p where $wherepf $regPf and p.time >= $startTime and p.time < $endTime group by logDate;";
			}
		}
		
		$nameLink['server'] = '服务器';
		$nameLink['open'] = '开服天数';
		$nameLink['total'] = '服务器总和';
		$eventAll['sum']['server'] = '合计';
		$nameLinkSort = array_keys($nameLink);
		$temp = $startTime;
		$charts_categories = array();
		$chartsPayArrTmp = array();
		$dateLink = array();
		
		while($temp < $endTime){
			$tempDate = date('Y-m-d',$temp/1000);
			$nameLink[$tempDate] = $tempDate;
			$charts_categories[] = $tempDate;
			$nameLinkSort[$endTime - $temp] = $tempDate;
			$temp += 86400000;
			$chartsPayArrTmp[$tempDate] = 0;
			$dateLink[$tempDate] = $tempDate;
		}
		
		krsort($chartsPayArrTmp);
		krsort($dateLink);
		$actAll = array();
		$dayCountAll = array();
		if($days>7){
			$countTotal=array_slice($chartsPayArrTmp,0,7);
		}else{
			$countTotal = $chartsPayArrTmp;
		}
		
		$charts_series = array();
		foreach ($selectServer as $server=>$serverInfo){
			/* if(substr($server, 0 ,1) != 's'){
			 continue;
			} */
			$result = $page->executeServer($server,$sql,3);
			$sqlData = array();
			$chartsPayArr = $chartsPayArrTmp;
			foreach ($result['ret']['data'] as $curRow){
				$eventAll['sum'][$curRow['logDate']] += $curRow['payCount'];
				$eventAll[$server][$curRow['logDate']] = $curRow['payCount'];
				$eventAll[$server]['total'] += $curRow['payCount'];
				$chartsPayArr[$curRow['logDate']] += $curRow['payCount'];
			}
			$eventAll[$server]['server'] = $server;
			$eventAll[$server]['total']=$eventAll[$server]['total'];
			$eventAll['sum']['total'] += $eventAll[$server]['total'];
			$eventAll['sum']['total']=$eventAll['sum']['total'];
// 			$openDaySql = "SELECT time from stat_reg limit 1";
// 			$openDayresult = $page->executeServer($server,$openDaySql,3);
// 			$openTime = $openDayresult['ret']['data'][0]['time'] ? $openDayresult['ret']['data'][0]['time'] : (time()-10) * 1000;
// 			$eventAll[$server]['open'] = ceil(( time()*1000 - $openTime) /86400000 );
			$openDaySql = "select daoliangStart from server_info where uid='server'";
			$openDayresult = $page->executeServer($server,$openDaySql,3);
			$openTime = $openDayresult['ret']['data'][0]['daoliangStart'];
			if(empty($openTime)){
					$eventAll[$server]['open'] = 0;
				}else {
					$datetime1 = date_create(date('Y-m-d'));
					$datetime2 = date_create(date('Y-m-d',$openTime/1000));
					$interval = date_diff($datetime1, $datetime2);
					$opendays = $interval->format('%a');
					$eventAll[$server]['open'] = $opendays + 1;
				}
			krsort($chartsPayArr);
			$chartsPayArr = array_values($chartsPayArr);
			$charts_series[] = array('name'=>$server,'data'=>$chartsPayArr);
		}
	} 	
	

	
	$eventAll['sum']['open'] = '-';
// 	printStat($eventAll,$nameLink,$nameLinkSort,$hightLight);
	//表头  数据
	if (in_array($_COOKIE['u'],$privilegeArr)){
		$html = $sql."<table class='listTable' style='text-align:center'><thead>";
	}else {
		$html = "<table class='listTable' style='text-align:center'><thead>";
	}
	if(!$nameLinkSort){
	    $nameLinkSort = array_keys($nameLink);
	}
	ksort($nameLinkSort);
	foreach ($nameLinkSort as $xRow){
	    $html .= "<th>$nameLink[$xRow]</th>";
	}
	$html .= "</thead>";
	$i=1;
	foreach ($eventAll as $date=>$eventData)
	{
//		if($i==1){
//			$html .= "<tbody><tr class='listTr'>";
//			foreach ($nameLinkSort as $indexKey=>$xRow){
//				$temp = $eventData[$xRow];
//				if(!$temp){
//					$temp = '-';
//				}
//				if ($indexKey=='server'||$indexKey='open'){
////					$html .= "<td style='text-align: right;'><font size='3' color='red'>$temp</font></td>";
//					$html .= "<td style='text-align: right;'>$temp</td>";
//				}else {
//					$temp=number_format($temp,2);
//					$html .= "<td style='text-align: right;'><font size='3' color='red'>$temp</font></td>";
//				}
//			}
//			$html .= "</tr></tbody>";
//			$i++;//限制第一行用
//		}
//		else{
			$html .= "<tbody><tr class='listTr'>";
			foreach ($nameLinkSort as $indexKey=>$xRow){
				$temp = $eventData[$xRow];
				if(!$temp){
					$temp = '-';
				}
				if($xRow!='server'&&$xRow!='open'&&$temp!='-' ){
					$tp='';
					if ($_COOKIE['u']!='xiaomi'){
						if(empty($seletedpf)){
							$tp ='_all';
						}else {
							$tp ='_'.$seletedpf;
						}
					}
					if(empty($currPf)){
						$tp .='_ALL';
					}else {
						$tp .='_'.$currPf;
					}
					if ($_COOKIE['u']!='xiaomi'){
						$tp .='_'.$country;
					}
//					id="s1_2016-04-19_all_ALL_ALL"
//					id="s1_total_all_ALL_ALL_starttime-endtime"
					if($xRow == 'total'){
						$dateset = $xRow.$tp.'_'.$startTime.'-'.$endTime;
					}else{
						$dateset = $xRow.$tp;
					}
					if($eventData['server'] == '合计'){
						$serverset = implode(',', $selectId);
					}else{
						$serverset = $eventData['server'];
					}
//				旧的	$html .='<td style="text-align: right;" id="'.$eventData['server'].'_'.$xRow.$tp.'"><a href="'.'javascript:void(edit('."'".$eventData['server'].'_'.$xRow.$tp."'))".'">'.$temp.'</a></td>';
					$html .='<td style="text-align: right;" id="'.$serverset.'_'.$dateset.'"><a href="'.'javascript:void(edit('."'".$serverset.'_'.$dateset."'))".'">'.$temp.'</a></td>';
				}
				else {
					if ($indexKey=='server'||$indexKey='open'){
						$html .= "<td style='text-align: right;'>$temp</td>";
					}else {
						$temp=number_format($temp,2);
						$html .= "<td style='text-align: right;'>".$temp."</td>";
					}
				}
			}
			$html .= "</tr></tbody>";
			$i++;
//		}
	   
	}
	$html .= "</table><br><br>";
	$ret = array();
	$ret['html'] = $html;
// 	rsort($charts_categories);
// 	$ret['charts']['categories'] = $charts_categories;
// 	$ret['charts']['series'] = $charts_series;



	exit(json_encode($ret));
}

include( renderTemplate("{$module}/{$module}_{$action}") );

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
function get_server_open_day_list(){
	if (!file_exists('/tmp/server_open_days.txt')) {
		return array();
	}
	$opendays = parse_ini_file('/tmp/server_open_days.txt');
	return $opendays;
}
function write_server_open_day($server,$day){
	file_put_contents('/tmp/server_open_days.txt', "$server=$day\n", FILE_APPEND);
}

?>
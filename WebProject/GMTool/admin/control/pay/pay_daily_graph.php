<?php
!defined('IN_ADMIN') && exit('Access Denied');
global $servers;

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

if ($_REQUEST['to150']) {
	$tosid = 150;
}
if ($_REQUEST['from151']) {
	$fromsid = 151;
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


if(!$_REQUEST['date'])
	$rDate = date("Y-m-d",time()-2*86400);
if(!$_REQUEST['day'])
	$rDay = 3;
$timeFix = strtotime(date('Y-m-d H:i:s')) - strtotime(gmdate('Y-m-d H:i:s'));
if (isset($_REQUEST['date'])) {
	try {
		$currPf = $_REQUEST['selectPf'];
		if ($currPf && $currPf!='ALL'){
			$miSql=" and pf='$currPf' ";
		}else {
			$miSql=" and pf in('cn_360','cn_am','cn_anzhi','cn_baidu','cn_dangle','cn_ewan','cn_huawei','cn_kugou','cn_kupai','cn_lenovo','cn_mi','cn_mihy','cn_mzw','cn_nearme','cn_pps','cn_pptv','cn_sogou','cn_toutiao','cn_uc','cn_vivo','cn_wdj','cn_wyx','cn_youku','cn_oppo','cn_sy37','cn_mz','tencent') ";
		}
		$rDate = $_REQUEST['date'];
		$rDay = $_REQUEST['day'];
		$dayStart = strtotime($rDate,time());
		$dayEnd = $dayStart + 86400 * $rDay;
		$dayStart *= 1000;
		$dayEnd *= 1000;
		
		$whereSql = "";
		if($currCountry&&$currCountry!='ALL'){
			$whereSql .=" inner join stat_reg r on a.uid=r.uid where r.country='$currCountry' ";
		}else{
			$whereSql .=" where 1=1 ";
		}
		if($seletedpf&&$seletedpf!='all'){
			$whereSql .=" and a.pf='$seletedpf' ";
		}
		
//		if($seletedpf&&$seletedpf!='all'){
	
		if ($_COOKIE['u']=='xiaomi'){
			$sql = "select DATE_FORMAT(FROM_UNIXTIME(a.`time`/1000),'%Y-%m-%d') as date,DATE_FORMAT(FROM_UNIXTIME(a.`time`/1000),'%H') as hour,floor(DATE_FORMAT(FROM_UNIXTIME(a.`time`/1000),'%i')/5)*5 as five,sum(a.spend) as paysum from (select * from paylog where time > $dayStart and time <= $dayEnd) a inner join (select uid from stat_reg where 1=1 $miSql) r on a.uid=r.uid group by date,hour,five order by a.time asc";
			$title = "'收入总数(RMB/100USD)'";
			if($_REQUEST['action'] == 'payuser'){
				$sql = "select DATE_FORMAT(FROM_UNIXTIME(a.`time`/1000),'%Y-%m-%d') as date,DATE_FORMAT(FROM_UNIXTIME(a.`time`/1000),'%H') as hour,floor(DATE_FORMAT(FROM_UNIXTIME(a.`time`/1000),'%i')/5)*5 as five,count(distinct(a.uid)) as paysum from (select * from paylog where time > $dayStart and time <= $dayEnd) a inner join (select uid from stat_reg where 1=1 $miSql) r on a.uid=r.uid group by date,hour,five order by a.time asc";
				$title = "'付费人数(未去重)'";
			}
			if($_REQUEST['action'] == 'paytimes'){
				$sql = "select DATE_FORMAT(FROM_UNIXTIME(a.`time`/1000),'%Y-%m-%d') as date,DATE_FORMAT(FROM_UNIXTIME(a.`time`/1000),'%H') as hour,floor(DATE_FORMAT(FROM_UNIXTIME(a.`time`/1000),'%i')/5)*5 as five,count(1) as paysum from (select * from paylog where time > $dayStart and time <= $dayEnd) a inner join (select uid from stat_reg where 1=1 $miSql) r on a.uid=r.uid group by date,hour,five order by a.time asc";
				$title = "'付费次数'";
			}
		}else {
			//默认收入曲线
			$sql = "select DATE_FORMAT(FROM_UNIXTIME(a.`time`/1000),'%Y-%m-%d') as date,DATE_FORMAT(FROM_UNIXTIME(a.`time`/1000),'%H') as hour,floor(DATE_FORMAT(FROM_UNIXTIME(a.`time`/1000),'%i')/5)*5 as five,sum(a.spend) as paysum from (select * from paylog where time > $dayStart and time <= $dayEnd) a $whereSql group by date,hour,five order by a.time asc";
			$title = "'收入总数(RMB/100USD)'";
			if($_REQUEST['action'] == 'payuser'){
				$sql = "select DATE_FORMAT(FROM_UNIXTIME(a.`time`/1000),'%Y-%m-%d') as date,DATE_FORMAT(FROM_UNIXTIME(a.`time`/1000),'%H') as hour,floor(DATE_FORMAT(FROM_UNIXTIME(a.`time`/1000),'%i')/5)*5 as five,count(distinct(a.uid)) as paysum from (select * from paylog where time > $dayStart and time <= $dayEnd) a $whereSql group by date,hour,five order by a.time asc";
				$title = "'付费人数(未去重)'";
			}
			if($_REQUEST['action'] == 'paytimes'){
				$sql = "select DATE_FORMAT(FROM_UNIXTIME(a.`time`/1000),'%Y-%m-%d') as date,DATE_FORMAT(FROM_UNIXTIME(a.`time`/1000),'%H') as hour,floor(DATE_FORMAT(FROM_UNIXTIME(a.`time`/1000),'%i')/5)*5 as five,count(1) as paysum from (select * from paylog where time > $dayStart and time <= $dayEnd) a $whereSql group by date,hour,five order by a.time asc";
				$title = "'付费次数'";
			}
		}
	//	file_put_contents('/tmp/ydpaySql.log', $sql."\n",FILE_APPEND);
		
//		}else {
	// 		$sql = "select DATE_FORMAT(FROM_UNIXTIME(`time`/1000),'%Y-%m-%d %H:%i') as min,floor(DATE_FORMAT(FROM_UNIXTIME(`time`/1000),'%i')/5)*5 as five,DATE_FORMAT(FROM_UNIXTIME(`time`/1000),'%Y-%m-%d') as date,(@paysum:=sum(amt+pubacct_payamt_coins*10)/100) as paysum from (select * from paylog where time > $dayStart and time <= $dayEnd) a group by min order by time asc";
// 			$sql = "select DATE_FORMAT(FROM_UNIXTIME(`time`/1000),'%Y-%m-%d') as date,DATE_FORMAT(FROM_UNIXTIME(`time`/1000),'%H') as hour,floor(DATE_FORMAT(FROM_UNIXTIME(`time`/1000),'%i')/5)*5 as five,sum(spend) as paysum from (select * from paylog where time > $dayStart and time <= $dayEnd) a group by date,hour,five order by time asc";
// 			$title = "'收入总数(RMB/100USD)'";
// 			if($_REQUEST['action'] == 'payuser'){
// 				$sql = "select DATE_FORMAT(FROM_UNIXTIME(`time`/1000),'%Y-%m-%d') as date,DATE_FORMAT(FROM_UNIXTIME(`time`/1000),'%H') as hour,floor(DATE_FORMAT(FROM_UNIXTIME(`time`/1000),'%i')/5)*5 as five,count(distinct(uid)) as paysum from (select * from paylog where time > $dayStart and time <= $dayEnd and currency = 0) a group by date,hour,five order by time asc";
// 				$title = "'付费人数(未去重)'";
// 			}
// 			if($_REQUEST['action'] == 'paytimes'){
// 				$sql = "select DATE_FORMAT(FROM_UNIXTIME(`time`/1000),'%Y-%m-%d') as date,DATE_FORMAT(FROM_UNIXTIME(`time`/1000),'%H') as hour,floor(DATE_FORMAT(FROM_UNIXTIME(`time`/1000),'%i')/5)*5 as five,count(1) as paysum from (select * from paylog where time > $dayStart and time <= $dayEnd and currency = 0) a group by date,hour,five order by time asc";
// 				$title = "'付费次数'";
// 			}
//		}
		$sqlData = array();
// 		foreach ($selectServer as $server=>$serInfo)
// 		{
// 			$sid = substr($server, 1);
// 			if ($sid == 252 || $sid == 267) {
// 				continue;
// 			}
// 				if ($tosid && $sid > $tosid) {
// 					continue;
// 				}
// 				if ($fromsid && $sid < $fromsid) {
// 					continue;
// 				}
				
// 			$result = $page->executeServer($server,$sql,3,false);
// 			$recall = 3;
// 			//如果没有查询到结果循环调用3次
// 			while(!$result['ret']['data'] && $recall++<3){
// 				$result = $page->executeServer($server,$sql,3,false);
// 			}
// 			if($result['ret']['data']){
// 				$dayTemp = array();
// 				$everyMinData = array();
// 			//旧代码，统计每分钟的数据，最后将每5分钟的数据提出
// // 				foreach ($result['ret']['data'] as $everyMin)
// // 				{
// // // 					$tempTime = strtotime($everyMin['min']);
// // // 					$date = date('Y-m-d',$tempTime);
// // 					$tempTime = $everyMin['min'];
// // 					$date = $everyMin['date'];
// // 					$dayTemp[$date] += $everyMin['paysum'];
// // 					$everyMinData[$date][$tempTime] += $dayTemp[$date];
// // 				}
// // 				foreach ($everyMinData as $date=>$minData){
// // 					$temp2 = $temp = strtotime($date);
// // 					$y = 0;
// // 					do {
// // 						$tempTime = date('Y-m-d H:i',$temp);
// // 						if($minData[$tempTime]){
// // 							$y = $minData[$tempTime];
// // 						}
// // 						//最终用于显示的结果中0点的数据归到前一天里面
// // 						$sqlData[$date][$tempTime] += $y;
// // 						$temp += 60;
// // 					}while ($temp <= $temp2 + 86400);
// // 				}
// // 			}else{
// // // 				echo $server.' error <br />';
// // 			}
// // 		}
// // 		foreach ($sqlData as $date=>$dateData){
// // 			$temp2 = $temp = strtotime($date);
// // 			$y = 0;
// // 			do {
// // 				for ($i = 0;$i < 5;$i++){
// // 					$tempTime = date('Y-m-d H:i',$temp);
// // 					if($dateData[$tempTime]){
// // 						$y = $dateData[$tempTime];
// // 					}
// // 					$temp += 60;
// // 				}
// // 				$data[$date][] = array('x'=>date('H:i',$temp)
// // 						,'y'=>$y);
// // 			}while ($temp <= $temp2 + 86400);
// // 			$total[$date] = $y;
// // 		}
// 				//新代码，查询的时候将插入哪一分钟的数据查询出来，解析的时候将每5分钟的数据加入最终显示数据里面
// 				foreach ($result['ret']['data'] as $curRow)
// 				{
// 					$date = $curRow['date'];
// 					$hour = (int)$curRow['hour'];
// 					$min = (int)$curRow['five'];
// 					$everyMinData[$date][$hour][$min] += $curRow['paysum'];
// 					$html .= "$server	$date	$hour	$min	{$curRow['paysum']}\n";
// 				}
// 				foreach ($everyMinData as $date=>$dateData){
// 					$temp = 0;
// 					$y = 0;
// 					do {
// 						$tmpHour = floor($temp/3600);
// 						$tmpMin = $temp%3600/300*5;
// 						if($dateData[$tmpHour][$tmpMin]){
// 							$y += $dateData[$tmpHour][$tmpMin];
// 						}
// 						//最终用于显示的结果中0点的数据归到前一天里面
// 						$sqlData[$date][$tmpHour][$tmpMin] += $y;
// 						$temp += 300;
// 					}while ($temp <= 86400);
// 				}
// 			}else{
// 				//$op_msg .= $server.' error<br />';
// 			}
// 		}
		
		//v2版本，从paydb读取数据
		include ADMIN_ROOT.'/include/pay/payment.php';
		try {
			$dateStart = date('Y-m-d G i',$dayStart/1000);
			$dateEnd = date('Y-m-d G i',$dayEnd/1000);
			$newWhereSql = " date >= '$dateStart' and date < '$dateEnd'";
			if($currCountry&&$currCountry!='ALL'){
				$newWhereSql .=" and country='$currCountry'";
			}
			if($seletedpf&&$seletedpf!='all'){
				$newWhereSql .=" and pf='$seletedpf'";
			}
			if ($_COOKIE['u']=='xiaomi'){
				$newSql = "select *,sum(paysum) total from payfive where $newWhereSql $miSql group by date order by date asc";
				$title = "'收入总数(RMB/100USD)'";
				if($_REQUEST['action'] == 'paytimes'){
					$newSql = "select *,sum(paytimes) total from payfive where $newWhereSql $miSql group by date order by date asc";
					$title = "'付费次数'";
				}
			}else {
				$newSql = "select *,sum(paysum) total from payfive p where $newWhereSql and pf !='iostest' group by date order by date asc";
				$title = "'收入总数(RMB/100USD)'";
				if($_REQUEST['action'] == 'paytimes'){
					$newSql = "select *,sum(paytimes) total from payfive p where $newWhereSql and pf !='iostest' group by date order by date asc";
					$title = "'付费次数'";
				}
			}
			$payment = payment::singleton();
			$newFiveDatas = $payment->getFiveData($newSql);
			foreach ($newFiveDatas as $newFiveData){
				$dateInfo = explode(" ", $newFiveData['date']);
				$date = $dateInfo[0];
				$hour = (int)$dateInfo[1];
				$min = (int)$dateInfo[2];
				$everyMinData[$date][$hour][$min] += $newFiveData['total'];
				$testData[$date] += $newFiveData['total'];
			}
// 			foreach ($everyMinData as $date=>$dateData){
// 				foreach ($dateData as $hour=>$hourData){
// 					foreach ($hourData as $five=>$pay){
// 						$testData[$date] += $pay;
// 					}
// 				}
// 			}
			foreach ($everyMinData as $date=>$dateData){
				$temp = 0;
				$y = 0;
				do {
					$tmpHour = floor($temp/3600);
					$tmpMin = $temp%3600/300*5;
					if($dateData[$tmpHour][$tmpMin]){
						$y += $dateData[$tmpHour][$tmpMin];
					}
					$sqlData[$date][$tmpHour][$tmpMin] += $y;
					$temp += 300;
				}while ($temp < 86400);
			}
			if (in_array($_COOKIE['u'],$privilegeArr)){
				$op_msg = search($testData);
				$op_msg .= $newSql;
			}
				
		} catch (Exception $e) {
			$op_msg = $e->getMessage();
		}
// 		$file = 'test_'.date('Y-m-d-H-i-s').'.log';
// 		file_put_contents( ADMIN_ROOT .'/'.$file,$html . "\n",FILE_APPEND);
		foreach ($sqlData as $date=>$dateData){
			$tmpDayStart = strtotime($date);
			$temp = 0;
			$y = 0;
			do {
				$tmpHour = floor($temp/3600);
				$tmpMin = $temp%3600/300*5;
				if($dateData[$tmpHour][$tmpMin]){
					$y = $dateData[$tmpHour][$tmpMin];
				}
				$temp += 300;
				$data[$date][] = array('x'=>date('H:i',$tmpDayStart+$temp)
						,'y'=>$y);
			}while ($temp <= 86400);
			$total[$date] = $y;
		}
		//if($predict){
		if(1){
			//预测今日数据
			$today = end($data);
			//当天已有数据
			$changing['y'] = -1;
			$predictData = array();
			$tTime=0;
			$nowTime=strtotime("now");
			$startTime=strtotime($date);
			foreach ($today as $key=>$fiveData){
//				if($changing['y'] == $fiveData['y']){
				if($startTime+$tTime>$nowTime){
					unset($predictData[end(array_keys($predictData))]);
					break;
				}
				$tTime+=300;
				if($fiveData['y']==0)
				{
					$changing['y'] = -1;
				}else{
					$changing = $fiveData;
				}
				$predictData[$key] = $fiveData;
			}
			
			//根据当天最后一段时间的数据拟合
			$accordingData = array_merge($predictData);
			$accordingIndex = 0;
			$accordingCount = 0;
			$according = array();
			for ($i = 0;$i<40&&$accordingData;$i++){
				$lastData = array_pop($accordingData);
				$according[] = $lastData['y'];
				$accordingCount++;
			}
			$according = array_reverse($according);
			//线性拟合
			$sumx = array_sum(array_keys($according));
			$sumy = array_sum($according);
			$sumx2 = 0;
			$sumxy = 0;
			foreach ($according as $x=>$y){
				$sumx2 += $x*$x;
				$sumxy += $x*$y;
			}
			//b=(n∑xiyi-∑xi∑yi)/[n∑xi^2-(∑xi)^2]=(4*89-12*24)/(4*46-12^2)=1.7
			$b = ($accordingCount*$sumxy - $sumx*$sumy)/($accordingCount*$sumx2 - $sumx*$sumx);
			//a=y'-bx'
			$a = $sumy/$accordingCount - $b*$sumx/$accordingCount ;
				
			//生成预测线
			$predictDate = end(array_keys($data)) . '_predict';
			$predictCount = count($according);
			foreach ($today as $key=>$fiveData){
				if(!$predictData[$key]){
					$predictY = sprintf("%.2f", $b * $predictCount++ + $a);
					$predictData[$key] = array('x'=>$fiveData['x']
						,'y'=>$predictY);
				}
				$data[$predictDate][] = array('x'=>$predictData[$key]['x']
						,'y'=>$predictData[$key]['y']);
			}
			$total[$predictDate] = $predictData[$key]['y'];
		}
	} catch ( Exception $e ) {
		$error_msg = $e->getMessage ();
	}
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
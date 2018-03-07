<?php
!defined('IN_ADMIN') && exit('Access Denied');
date_default_timezone_set('GMT');
$title = "按平台各个国家的ROI统计";
$dateMax = date("Y-m-d",time()-86400);
$dateMin = date("Y-m-d",time()-86400*30);
global $servers;
$sttt = $_REQUEST['selectServer'];
$serverDiv=loadDiv($sttt);
$erversAndSidsArr=getSelectServersAndSids($sttt);
$selectServer=$erversAndSidsArr['withS'];
$selectServerids=$erversAndSidsArr['onlyNum'];

$indexArr=array(
	'7Average'=>'7日均值',
	'30Average'=>'30日均值',
	'daily'=>'每日曲线'
);

$pfCountry=array(
		'market_global' => 'GooglePlay',
		'AppStore' => 'AppStore',
		'facebook' => 'facebook',
);

$displayCountryArr=array(
		'US' => '美国',
		'JP' => '日本',
		'CN' => '中国',
		'KR' => '韩国',
		'TW' => '台湾省',
		'RU' => '俄罗斯',
		'HK' => '香港特别行政区',
		'MO' => '澳门地区',
		'GB' => '英国',
		'DE' => '德国',
		'FR' => '法国',
		'TR' => '土耳其',
		'AE' => '阿联酋',
		'AU' => '澳大利亚',
		'NZ' => '新西兰',
		'IT'=>'意大利',
		'ES' => '西班牙',
		'NO' => '挪威',
		'IR' => '伊朗',
		'ID' => '印度尼西亚',
		'SG' => '新加坡',
		'MY' => '马来西亚',
		'TH' => '泰国',
		'VN' => '越南',
		'BR' => '巴西',
		'SA' => '沙特阿拉伯',
		'OTHER' => '其它国家',
);
$displayPf=array(
		'market_global' => 'GooglePlay',
		'AppStore' => 'AppStore',
		'amazon' => 'amazon',
		'nstore' => 'nstore',
		'tstore' => 'tstore',
		'facebook' => 'facebook',
		'cafebazaar' => 'cafebazaar',
		'mycard' => 'mycard',
		'gash' => 'gash',
		'cn1' => 'cn1',
		'xiaomi' => 'xiaomi互娱',
		'xiaomiOther' => 'xiaomi其它',
		'other' => 'other',
);
$mipf=array(
		'cn_mi'=>'cn_mi',
		'cn_mihy'=>'cn_mihy',
		'mi_web'=>'mi_web',
);

$mipfOther=array(
		'cn_360'=>'cn_360',
		'cn_am'=>'cn_am',
		'cn_anzhi'=>'cn_anzhi',
		'cn_baidu'=>'cn_baidu',
		'cn_dangle'=>'cn_dangle',
		'cn_ewan'=>'cn_ewan',
		'cn_huawei'=>'cn_huawei',
		'cn_kugou'=>'cn_kugou',
		'cn_kupai'=>'cn_kupai',
		'cn_lenovo'=>'cn_lenovo',
		'cn_mzw'=>'cn_mzw',
		'cn_nearme'=>'cn_nearme',
		'cn_pps'=>'cn_pps',
		'cn_pptv'=>'cn_pptv',
		'cn_sogou'=>'cn_sogou',
		'cn_toutiao'=>'cn_toutiao',
		'cn_uc'=>'cn_uc',
		'cn_vivo'=>'cn_vivo',
		'cn_wdj'=>'cn_wdj',
		'cn_wyx'=>'cn_wyx',
		'cn_youku'=>'cn_youku',
		'cn_sy37'=>'cn_sy37',
		'cn_mz'=>'cn_mz',
		'tencent'=>'tencent',
);

$tableIndex=array(
	"today"=>"注册当日付费",
	"3day"=>"注册3日后付费",
	"7day"=>"注册7日后付费",
	"15day"=>"注册15日后付费",
	"30day"=>"注册30日后付费",
	"allday"=>"注册30日以上付费",
);
if ($_REQUEST['getData']) {
	$totalReg =	$totalIncome = $today = $day3 = $day7 =$day15=$day30 =0;
	$country=$_REQUEST['country'];
	$startYmd=date('Ymd',strtotime($_REQUEST['dateMin']));
	$endYmd=date('Ymd',strtotime($_REQUEST['dateMax']));
	$curCountry=$_REQUEST['event'];
	$countryParams='';
	if ($curCountry=='ALL' || $curCountry=='OTHER'){
		$newWhereSql='';
		$countryParams='All';
	}else {
		$newWhereSql=" and country='$curCountry' ";
		$countryParams=$curCountry;
	}
	
	$ct_field_list = array(
			'googleplay' => 'Googleplay',
			'facebook' => 'Facebook',
			'cn'=>'中国',
			'kr' => '韩国',
			'jp' => '日本',
			'th' => '泰国',
			'tw' => '繁体',
	);
	$fcnt = count($ct_field_list);
	$fields = implode(',', array_keys($ct_field_list));
	
		$startDateYMD = date("Y-m-d",strtotime($_REQUEST['dateMin']));
		$endDateYMD = date("Y-m-d",strtotime($_REQUEST['dateMax']));
		foreach ($ct_field_list as $k=>$v) {
			$sumfields[] = "ifnull($k,0)";
		}
		$sum = implode('+', $sumfields);
		$costsql = "select date, $fields, $sum as allcost from adcost where date>='$startDateYMD' and date<='$endDateYMD' order by date";
		$result = $page->globalExecute($costsql, 3, true);
		$adCostDB = $result['ret']['data'];
		foreach ($adCostDB as $record){
			$date=date('Ymd',strtotime($record['date']));
			$adCostTime[$date] = $record['allcost'];
			foreach (array_keys($ct_field_list) as $c) {
				$adCostTimeCountry[$c][$date] = $record[$c];
			}
		}
		
		//国家和平台相互独立
		if(isset($ct_field_list[strtolower($curCountry)])){
			$adCostTime = $adCostTimeCountry[strtolower($curCountry)];
		}
	
	$nameLink = array('todayRoi'=>'当日ROI','3roi'=>'3日ROI','7roi'=>'7日ROI','15roi'=>'15日ROI','30roi'=>'30日ROI','allroi'=>'总转化率');
	$sql = "select regDate,payDate,pf,country,sum(spendSum) spendSum from stat_allserver.stat_roi_pf_country_v2 where regDate >= $startYmd and regDate <= $endYmd $newWhereSql group by regDate,payDate,pf,country;";
	
	$ret = query_infobright($sql);
	$eventAll = array();
	$total=array();
	$pfTotal = array();
	$dateList=array();
	foreach ($ret['ret']['data'] as $curRow){
		$regDate=$curRow['regDate'];
		$payDate=$curRow['payDate'];
		$newdate = $curRow['regDate'];
		if (!in_array($newdate, $dateList)){
			$dateList[]=$newdate;
		}
			if (array_key_exists($curRow['country'], $displayCountryArr)){
				$cou=$curRow['country'];
			}else {
				$cou='OTHER';
			}
			
			if (array_key_exists($curRow['pf'], $displayPf)){
				$findPf=$curRow['pf'];
			}elseif (array_key_exists($curRow['pf'], $mipf)){
				$findPf='xiaomi';
			}elseif (array_key_exists($curRow['pf'], $mipfOther)){
				$findPf='xiaomiOther';
			}else {
				$findPf='other';
			}
			if($regDate==$payDate){
				$total_num[$newdate]['today']+=$curRow['spendSum'];
				$pfTotal_num[$findPf][$newdate]['today']+=$curRow['spendSum'];
				$eventAll_num[$findPf][$newdate][$cou]['today']+=$curRow['spendSum'];
			}
			if ((strtotime($payDate)-strtotime($regDate))/86400<=3){
				$total_num[$newdate]['3day']+=$curRow['spendSum'];
				$pfTotal_num[$findPf][$newdate]['3day']+=$curRow['spendSum'];
				$eventAll_num[$findPf][$newdate][$cou]['3day']+=$curRow['spendSum'];
			}
			if ((strtotime($payDate)-strtotime($regDate))/86400<=7){
				$total_num[$newdate]['7day']+=$curRow['spendSum'];
				$pfTotal_num[$findPf][$newdate]['7day']+=$curRow['spendSum'];
				$eventAll_num[$findPf][$newdate][$cou]['7day']+=$curRow['spendSum'];
			}
			if ((strtotime($payDate)-strtotime($regDate))/86400<=15){
				$total_num[$newdate]['15day']+=$curRow['spendSum'];
				$pfTotal_num[$findPf][$newdate]['15day']+=$curRow['spendSum'];
				$eventAll_num[$findPf][$newdate][$cou]['15day']+=$curRow['spendSum'];
			}
			if ((strtotime($payDate)-strtotime($regDate))/86400<=30){
				$total_num[$newdate]['30day']+=$curRow['spendSum'];
				$pfTotal_num[$findPf][$newdate]['30day']+=$curRow['spendSum'];
				$eventAll_num[$findPf][$newdate][$cou]['30day']+=$curRow['spendSum'];
			}
			$total_num[$newdate]['allday']+=$curRow['spendSum'];
			$pfTotal_num[$findPf][$newdate]['allday']+=$curRow['spendSum'];
			$eventAll_num[$findPf][$newdate][$cou]['allday']+=$curRow['spendSum'];
		}
		
	$sql= "select regDate,pf,country,sum(reg) reg from stat_allserver.stat_roi_pf_country_reg where regDate >= $startYmd and regDate <= $endYmd $newWhereSql group by regDate,pf,country;";
	$ret = query_infobright($sql);
	foreach ($ret['ret']['data'] as $curRow){
		$newdate = $curRow['regDate'];
		if (array_key_exists($curRow['country'], $displayCountryArr)){
			$cou=$curRow['country'];
		}else {
			$cou='OTHER';
		}
			
		if (array_key_exists($curRow['pf'], $displayPf)){
			$findPf=$curRow['pf'];
		}elseif (array_key_exists($curRow['pf'], $mipf)){
			$findPf='xiaomi';
		}elseif (array_key_exists($curRow['pf'], $mipfOther)){
			$findPf='xiaomiOther';
		}else {
			$findPf='other';
		}
		if (!in_array($newdate, $dateList)){
			$dateList[]=$newdate;
		}
		$eventAll[$findPf][$newdate][$cou]['reg'] += $curRow['reg'];
		$totalReg[$newdate] += $curRow['reg'];
		$totalPfReg[$findPf][$newdate] += $curRow['reg'];
	}
	
	$totalTdBackgroundCol=array();
	foreach ($dateList as $k=>$d){
		if ($k==0){
			foreach ($tableIndex as $tk=>$tv){
				$total[$dateList[$k]][$tk]=number_format($total_num[$dateList[$k]][$tk],2)."<br><br>转化率: ".($adCostTime[$dateList[$k]] ? round($total_num[$dateList[$k]][$tk]/$adCostTime[$dateList[$k]],2) : '无');
			}
			continue;
		}
		foreach ($tableIndex as $tk=>$tv){
			$totalTdBackgroundCol[$dateList[$k]][$tk]='';
			if ($total_num[$dateList[$k]][$tk]>$total_num[$dateList[$k-1]][$tk]){
				if (!$adCostTime[$dateList[$k]]){
					$total[$dateList[$k]][$tk]=number_format($total_num[$dateList[$k]][$tk],2)."<strong><font color='red'>&#8593;</font></strong>"."<br><br>转化率: 无";
				}elseif ((($total_num[$dateList[$k]][$tk]/$adCostTime[$dateList[$k]])>($total_num[$dateList[$k-1]][$tk]/$adCostTime[$dateList[$k-1]]))){
					$total[$dateList[$k]][$tk]=number_format($total_num[$dateList[$k]][$tk],2)."<strong><font color='red'>&#8593;</font></strong>"."<br><br>转化率: ".round($total_num[$dateList[$k]][$tk]/$adCostTime[$dateList[$k]],2)."<strong><font color='red'>&#8593;</font></strong>";
				}else {
					$total[$dateList[$k]][$tk]=number_format($total_num[$dateList[$k]][$tk],2)."<strong><font color='red'>&#8593;</font></strong>"."<br><br>转化率: ".round($total_num[$dateList[$k]][$tk]/$adCostTime[$dateList[$k]],2)."<strong><font color='green'>&#8595;</font></strong>";
				}
			}elseif ($total_num[$dateList[$k]][$tk]<$total_num[$dateList[$k-1]][$tk]){
				if (!$adCostTime[$dateList[$k]]){
					$total[$dateList[$k]][$tk]=number_format($total_num[$dateList[$k]][$tk],2)."<strong><font color='green'>&#8595;</font></strong>"."<br><br>转化率: 无";
				}elseif ((($total_num[$dateList[$k]][$tk]/$adCostTime[$dateList[$k]])>($total_num[$dateList[$k-1]][$tk]/$adCostTime[$dateList[$k-1]]))){
					$total[$dateList[$k]][$tk]=number_format($total_num[$dateList[$k]][$tk],2)."<strong><font color='green'>&#8595;</font></strong>"."<br><br>转化率: ".round($total_num[$dateList[$k]][$tk]/$adCostTime[$dateList[$k]],2)."<strong><font color='red'>&#8593;</font></strong>";
				}else {
					$total[$dateList[$k]][$tk]=number_format($total_num[$dateList[$k]][$tk],2)."<strong><font color='green'>&#8595;</font></strong>"."<br><br>转化率: ".round($total_num[$dateList[$k]][$tk]/$adCostTime[$dateList[$k]],2)."<strong><font color='green'>&#8595;</font></strong>";
				}
			}
		}
	}
	
	sort($dateList);
	$tdBackgroundCol=array();
	$tdBackgroundCol2=array();
	foreach ($displayPf as $pk=>$pv){
		$i=0;
		foreach ($dateList as $k=>$d){
			if ($k==0){
				foreach ($tableIndex as $tk=>$tv){
					if (isset($ct_field_list[strtolower($pv)])){
						$pfTotal[$pk][$dateList[$k]][$tk]=number_format($pfTotal_num[$pk][$dateList[$k]][$tk],2)."<br><br>转化率:".($adCostTimeCountry[strtolower($pv)][$dateList[$k]] ? round($pfTotal_num[$pk][$dateList[$k]][$tk]/$adCostTimeCountry[strtolower($pv)][$dateList[$k]],2) : '无');
					}else {
						$pfTotal[$pk][$dateList[$k]][$tk]=number_format($pfTotal_num[$pk][$dateList[$k]][$tk],2);
					}
					foreach ($displayCountryArr as $countryKey=>$countryVal){
						$eventAll[$pk][$dateList[$k]][$countryKey][$tk]=number_format($eventAll_num[$pk][$dateList[$k]][$countryKey][$tk],2);
					}
				}
				continue;
			}
			foreach ($tableIndex as $tk=>$tv){
				if (isset($ct_field_list[strtolower($pv)])){
					if ($pfTotal_num[$pk][$dateList[$k]][$tk]>$pfTotal_num[$pk][$dateList[$k-1]][$tk]){
						if (!$adCostTimeCountry[strtolower($pv)][$dateList[$k]]){
							$pfTotal[$pk][$dateList[$k]][$tk]=number_format($pfTotal_num[$pk][$dateList[$k]][$tk],2)."<strong><font color='red'>&#8593;</font></strong>"."<br><br>转化率: 无";
						}elseif ((($total_num[$dateList[$k]][$tk]/$adCostTime[$dateList[$k]])>($total_num[$dateList[$k-1]][$tk]/$adCostTimeCountry[strtolower($pv)][$dateList[$k-1]]))){
							$pfTotal[$pk][$dateList[$k]][$tk]=number_format($pfTotal_num[$pk][$dateList[$k]][$tk],2)."<strong><font color='red'>&#8593;</font></strong>"."<br><br>转化率: ".round($total_num[$dateList[$k]][$tk]/$adCostTimeCountry[strtolower($pv)][$dateList[$k]],2)."<strong><font color='red'>&#8593;</font></strong>";
						}else {
							$pfTotal[$pk][$dateList[$k]][$tk]=number_format($pfTotal_num[$pk][$dateList[$k]][$tk],2)."<strong><font color='red'>&#8593;</font></strong>"."<br><br>转化率: ".round($total_num[$dateList[$k]][$tk]/$adCostTimeCountry[strtolower($pv)][$dateList[$k]],2)."<strong><font color='green'>&#8595;</font></strong>";
						}
					}elseif ($pfTotal_num[$pk][$dateList[$k]][$tk]<$pfTotal_num[$pk][$dateList[$k-1]][$tk]){
						if (!$adCostTimeCountry[strtolower($pv)][$dateList[$k]]){
							$pfTotal[$pk][$dateList[$k]][$tk]=number_format($pfTotal_num[$pk][$dateList[$k]][$tk],2)."<strong><font color='green'>&#8595;</font></strong>"."<br><br>转化率: 无";
						}elseif ((($total_num[$dateList[$k]][$tk]/$adCostTime[$dateList[$k]])>($total_num[$dateList[$k-1]][$tk]/$adCostTimeCountry[strtolower($pv)][$dateList[$k-1]]))){
							$pfTotal[$pk][$dateList[$k]][$tk]=number_format($pfTotal_num[$pk][$dateList[$k]][$tk],2)."<strong><font color='green'>&#8595;</font></strong>"."<br><br>转化率: ".round($total_num[$dateList[$k]][$tk]/$adCostTimeCountry[strtolower($pv)][$dateList[$k]],2)."<strong><font color='red'>&#8593;</font></strong>";
						}else {
							$pfTotal[$pk][$dateList[$k]][$tk]=number_format($pfTotal_num[$pk][$dateList[$k]][$tk],2)."<strong><font color='green'>&#8595;</font></strong>"."<br><br>转化率: ".round($total_num[$dateList[$k]][$tk]/$adCostTimeCountry[strtolower($pv)][$dateList[$k]],2)."<strong><font color='green'>&#8595;</font></strong>";
						}
					}
				}else {
					if ($pfTotal_num[$pk][$dateList[$k]][$tk]>$pfTotal_num[$pk][$dateList[$k-1]][$tk]){
						$pfTotal[$pk][$dateList[$k]][$tk]=number_format($pfTotal_num[$pk][$dateList[$k]][$tk],2)."<strong><font color='red'>&#8593;</font></strong>";
					}elseif ($pfTotal_num[$pk][$dateList[$k]][$tk]<$pfTotal_num[$pk][$dateList[$k-1]][$tk]){
						$pfTotal[$pk][$dateList[$k]][$tk]=number_format($pfTotal_num[$pk][$dateList[$k]][$tk],2)."<strong><font color='green'>&#8595;</font></strong>";
					}
				}
				foreach ($displayCountryArr as $countryKey=>$countryVal){
					if (isset($eventAll_num[$pk][$dateList[$k]][$countryKey][$tk]) && !empty($eventAll_num[$pk][$dateList[$k]][$countryKey][$tk])){
						if ($eventAll_num[$pk][$dateList[$k]][$countryKey][$tk]>$eventAll_num[$pk][$dateList[$k-1]][$countryKey][$tk]){
							$eventAll[$pk][$dateList[$k]][$countryKey][$tk]=number_format($eventAll_num[$pk][$dateList[$k]][$countryKey][$tk],2)."<strong><font color='red'>&#8593;</font></strong>";
						}elseif ($eventAll_num[$pk][$dateList[$k]][$countryKey][$tk]<$eventAll_num[$pk][$dateList[$k-1]][$countryKey][$tk]){
							$eventAll[$pk][$dateList[$k]][$countryKey][$tk]=number_format($eventAll_num[$pk][$dateList[$k]][$countryKey][$tk],2)."<strong><font color='green'>&#8595;</font></strong>";
						}
					}
				}
				
			}
		}
	
	}
	
	
	rsort($dateList);
}
	

include( renderTemplate("{$module}/{$module}_{$action}") );
?>
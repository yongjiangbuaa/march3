<?php
!defined('IN_ADMIN') && exit('Access Denied');
date_default_timezone_set('GMT');
$title = "按平台各个国家的支付总额统计";
$dateMax = date("Y-m-d",time()-86400);
$dateMin = date("Y-m-d",time()-86400*14);
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
		'xiaomi' => 'xiaomi',
		'other' => 'other',
);
$mipf=array(
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
		'cn_mi'=>'cn_mi',
		'cn_mihy'=>'cn_mihy',
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

$displayCountryFlag=false;

foreach ($_REQUEST as $cKey=>$value)
{
	if($displayCountryArr[$cKey] && $value == 'on'){
		$selectCountry[] = $cKey;
	}
}

if ($_REQUEST['getData']) {
	$country=$_REQUEST['country'];
	$startYmd=date('Ymd',strtotime($_REQUEST['dateMin']));
	$endYmd=date('Ymd',strtotime($_REQUEST['dateMax']));
	$sids=implode(',', $selectServerids);
	
	$curCountry=$_REQUEST['event'];
	if ($curCountry=='ALL' || $curCountry=='OTHER'){
		$newWhereSql='';
	}else {
		$newWhereSql=" and country='$curCountry' ";
	}
	
	$total=array();
	$total_num=array();
	$pfData=array();
	$pfData_num=array();
	$dateList=array();
	
	$pfPieData=array();
	$countryPieData=array();
	
	$showChart=array();
	//均线图
	$day7Ave=7;
	$day30Ave=30;
	$before7Day=date('Ymd',strtotime($startYmd)-86400*$day30Ave);
	$sql="select date as logDate,payChanel,pf,country,sum(payCount) as payCount from stat_allserver.pay_payTotle_pf_country where sid in($sids) and date between $before7Day and $endYmd $newWhereSql group by logDate,payChanel,pf,country order by payCount desc;";
	if ($_COOKIE['u']=='yd'){
		echo $sql;
	}
	
	$result = query_infobright($sql);
	$dayData=array();
	$chartCountryData=array();
	foreach ($result['ret']['data'] as $curRow){
		if ($curCountry=='OTHER'){
			if (!array_key_exists($curRow['country'], $displayCountryArr)){
				$dayData[$curRow['logDate']]+=$curRow['payCount'];
			}
		}else {
			$dayData[$curRow['logDate']]+=$curRow['payCount'];
		}
		if ($curRow['logDate']>=$startYmd && $curRow['logDate']<=$endYmd){
			if (array_key_exists($curRow['country'], $displayCountryArr)){
				$cou=$curRow['country'];
			}else {
				$cou='OTHER';
			}
			
			if (array_key_exists($curRow['pf'], $displayPf)){
				$findPf=$curRow['pf'];
			}elseif (array_key_exists($curRow['pf'], $mipf)){
				$findPf='xiaomi';
			}else {
				$findPf='other';
			}
			
			if ($curRow['logDate']==$endYmd){
				if ($curCountry=='OTHER'){
					if (!array_key_exists($curRow['country'], $displayCountryArr)){
						$pfPieData[$findPf]+=$curRow['payCount'];
						$countryPieData[$cou]+=$curRow['payCount'];
					}
				}else {
					$pfPieData[$findPf]+=$curRow['payCount'];
					$countryPieData[$cou]+=$curRow['payCount'];
				}
				
			}
			
			$chartCountryData[$cou][$curRow['logDate']]+=$curRow['payCount'];
			
			if ($curCountry=='OTHER'){
				if (!array_key_exists($curRow['country'], $displayCountryArr)){
					$total_num[$curRow['logDate']]+=$curRow['payCount'];
				}
			}else {
				$total_num[$curRow['logDate']]+=$curRow['payCount'];
			}
			
			$pfData_num[$findPf][$curRow['logDate']][$cou]+=$curRow['payCount'];
			
			if (!in_array($curRow['logDate'], $dateList)){
				$dateList[]=$curRow['logDate'];
			}
		}
	}
	
	//跨服的支付数据
	$sql="select date as logDate,payChanel,pf,country,sum(payCount) as payCount from stat_allserver.pay_payTotle_pf_country_cross where sid in($sids) and date between $before7Day and $endYmd $newWhereSql group by logDate,payChanel,pf,country order by payCount desc;";
	if ($_COOKIE['u']=='yd'){
		echo $sql;
	}
	$result = query_infobright($sql);
	foreach ($result['ret']['data'] as $curRow){
		if ($curCountry=='OTHER'){
			if (!array_key_exists($curRow['country'], $displayCountryArr)){
				$dayData[$curRow['logDate']]+=$curRow['payCount'];
			}
		}else {
			$dayData[$curRow['logDate']]+=$curRow['payCount'];
		}
		if ($curRow['logDate']>=$startYmd && $curRow['logDate']<=$endYmd){
			if (array_key_exists($curRow['country'], $displayCountryArr)){
				$cou=$curRow['country'];
			}else {
				$cou='OTHER';
			}
				
			if (array_key_exists($curRow['pf'], $displayPf)){
				$findPf=$curRow['pf'];
			}elseif (array_key_exists($curRow['pf'], $mipf)){
				$findPf='xiaomi';
			}else {
				$findPf='other';
			}
				
			if ($curRow['logDate']==$endYmd){
				if ($curCountry=='OTHER'){
					if (!array_key_exists($curRow['country'], $displayCountryArr)){
						$pfPieData[$findPf]+=$curRow['payCount'];
						$countryPieData[$cou]+=$curRow['payCount'];
					}
				}else {
					$pfPieData[$findPf]+=$curRow['payCount'];
					$countryPieData[$cou]+=$curRow['payCount'];
				}
	
			}
				
			$chartCountryData[$cou][$curRow['logDate']]+=$curRow['payCount'];
				
			if ($curCountry=='OTHER'){
				if (!array_key_exists($curRow['country'], $displayCountryArr)){
					$total_num[$curRow['logDate']]+=$curRow['payCount'];
				}
			}else {
				$total_num[$curRow['logDate']]+=$curRow['payCount'];
			}
				
			$pfData_num[$findPf][$curRow['logDate']][$cou]+=$curRow['payCount'];
				
			if (!in_array($curRow['logDate'], $dateList)){
				$dateList[]=$curRow['logDate'];
			}
		}
	}
	
	foreach ($displayPf as $k=>$v){
		foreach ($dateList as $date){
			$pfData_num[$k][$date]['total']=array_sum($pfData_num[$k][$date]);
		}
	}
	
	sort($dateList);
	$length=count($dateList);
	$dateStr='['.implode(',', $dateList).']';
	ksort($dayData);
	$showChart['7Average']=dayAverage($dayData, $dateList, $day7Ave);
	$showChart['30Average']=dayAverage($dayData, $dateList, $day30Ave);
	$showChart['daily']=dayAverage($dayData, $dateList, 1);
	
	/*
	$showCountryChart=array();
	if ($_REQUEST['specifiedCountry']){
		foreach ($displayCountryArr as $country=>$countryname){
			$showFlag=0;
			$temp=array();
			foreach ($dateList as $date){
				$temp[]=number_format(floatval($chartCountryData[$country][$date]),2,".","");
				$showFlag+=floatval($chartCountryData[$country][$date]);
			}
			if ($showFlag>0){
				$str='[';
				$str.=implode(',', $temp);
				$str.=']';
				$showCountryChart[$country]['data']=$str;
				if (in_array($country, $selectCountry)){
					$showCountryChart[$country]['visible']=true;
				}else {
					$showCountryChart[$country]['visible']=false;
				}
			}
		}
		$displayCountryFlag=true;
	}
	*/
	$totalTdBackgroundCol=array();
	foreach ($dateList as $k=>$d){
		if ($k==0){
			$total[$dateList[$k]]=number_format($total_num[$dateList[$k]],2);
			continue;
		}
		$totalTdBackgroundCol[$dateList[$k]]='';
		if ($total_num[$dateList[$k]]>$total_num[$dateList[$k-1]]){
			$total[$dateList[$k]]=number_format($total_num[$dateList[$k]],2)."<strong><font color='red'>&#8593;</font></strong>";
			$totalTdBackgroundCol[$dateList[$k]]="up";   //style='background-color: darkred;'
		}elseif ($total_num[$dateList[$k]]<$total_num[$dateList[$k-1]]){
			$total[$dateList[$k]]=number_format($total_num[$dateList[$k]],2)."<strong><font color='green'>&#8595;</font></strong>";
			$totalTdBackgroundCol[$dateList[$k]]="down";  //style='background-color: cadetblue;'
		}
	}
	
	$tdBackgroundCol=array();
	foreach ($displayPf as $pk=>$pv){
		$i=0;
		foreach ($dateList as $k=>$d){
			if ($k==0){
				$pfData[$pk][$dateList[$k]]['total']=number_format($pfData_num[$pk][$dateList[$k]]['total'],2);
				foreach ($displayCountryArr as $countryKey=>$countryVal){
					$pfData[$pk][$dateList[$k]][$countryKey]=number_format($pfData_num[$pk][$dateList[$k]][$countryKey],2);
				}
				continue;
			}
			$tdBackgroundCol[$pk][$dateList[$k]]['total']='';
			if ($pfData_num[$pk][$dateList[$k]]['total']>$pfData_num[$pk][$dateList[$k-1]]['total']){
				$pfData[$pk][$dateList[$k]]['total']=number_format($pfData_num[$pk][$dateList[$k]]['total'],2)."<strong><font color='red'>&#8593;</font></strong>";
				$tdBackgroundCol[$pk][$dateList[$k]]['total']='up';
			}elseif ($pfData_num[$pk][$dateList[$k]]['total']<$pfData_num[$pk][$dateList[$k-1]]['total']){
				$pfData[$pk][$dateList[$k]]['total']=number_format($pfData_num[$pk][$dateList[$k]]['total'],2)."<strong><font color='green'>&#8595;</font></strong>";
				$tdBackgroundCol[$pk][$dateList[$k]]['total']='down';
			}
			
			foreach ($displayCountryArr as $countryKey=>$countryVal){
				$tdBackgroundCol[$pk][$dateList[$k]][$countryKey]='';
				if (isset($pfData_num[$pk][$dateList[$k]][$countryKey]) && !empty($pfData_num[$pk][$dateList[$k]][$countryKey])){
					if ($pfData_num[$pk][$dateList[$k]][$countryKey]>$pfData_num[$pk][$dateList[$k-1]][$countryKey]){
						$pfData[$pk][$dateList[$k]][$countryKey]=number_format($pfData_num[$pk][$dateList[$k]][$countryKey],2)."<strong><font color='red'>&#8593;</font></strong>";
						$tdBackgroundCol[$pk][$dateList[$k]][$countryKey]='up';
					}elseif ($pfData_num[$pk][$dateList[$k]][$countryKey]<$pfData_num[$pk][$dateList[$k-1]][$countryKey]){
						$pfData[$pk][$dateList[$k]][$countryKey]=number_format($pfData_num[$pk][$dateList[$k]][$countryKey],2)."<strong><font color='green'>&#8595;</font></strong>";
						$tdBackgroundCol[$pk][$dateList[$k]][$countryKey]='down';
					}
				}
			}
		}
	
	}
	rsort($dateList);
	
	//计算饼状数据

	$pfPie=array();
	$countryPie=array();
	$pfPieData['total']=array_sum($pfPieData);
	$countryPieData['total']=array_sum($countryPieData);
	foreach ($displayPf as $pk=>$pv){
		$pfPie[]="['$pv',".(intval($pfPieData[$pk] / $pfPieData['total'] *10000)/100)."]";
	}
	foreach ($displayCountryArr as $ck=>$cv){
		$countryPie[]="['$cv',".(intval($countryPieData[$ck] / $countryPieData['total'] *10000)/100)."]";
	}
	
	$titleDate=date('Y-m-d',strtotime($endYmd));
	
}

function dayAverage($arrayList,$dateArr,$day){
	$dateTemp=array_keys($arrayList);
	$aveDay=array();
	foreach ($dateArr as $date){
		$key=array_search($date,$dateTemp);
		$sum=0;
		for ($i=0;$i<=($day-1);$i++){
			$sum+=$arrayList[$dateTemp[$key-$i]];
		}
		$aveDay[$date]=number_format($sum/$day,2,".","");
	}
	$aveStr='['.implode(',', $aveDay).']';
	return $aveStr;
}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>
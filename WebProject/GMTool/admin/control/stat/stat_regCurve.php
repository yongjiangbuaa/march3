<?php
!defined('IN_ADMIN') && exit('Access Denied');
date_default_timezone_set('GMT');
$title = "按平台各个国家的注册人数统计";
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

if ($_REQUEST['getData']) {
	$country=$_REQUEST['country'];
	$startYmd=date('Ymd',strtotime($_REQUEST['dateMin']));
	$endYmd=date('Ymd',strtotime($_REQUEST['dateMax']));
	$sids=implode(',', $selectServerids);
	
	$curCountry=$_REQUEST['event'];
	$countryParams='';
	if ($curCountry=='ALL' || $curCountry=='OTHER'){
		$newWhereSql='';
		$countryParams='All';
	}else {
		$newWhereSql=" and country='$curCountry' ";
		$countryParams=$curCountry;
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
	$resultVal = array();
	if (empty($sttt)){
		$client = new Redis ();
		$client->connect ( 'localhost' );
		$temp=$client->hGetAll("regDaily");
		$client->close();
		for ($d=$before7Day;$d<=$endYmd;){
			$temp2=json_decode($temp[$d]);
			foreach ($temp2 as $pcKey=>$rVal){
				$one=array();
				$one['regDate']=$d;
				$t=explode(",", $pcKey);
				$one['pf']=$t[0];
				$one['country']=$t[1];
				$one['reg']=$rVal;
				$resultVal[]=$one;
			}
			$d=date("Ymd",strtotime($d)+86400);
		}
	}else {
		$sql="select date as regDate,pf,country,sum(reg) as reg from stat_allserver.stat_dau_daily_pf_country_new where sid in($sids) and date between $before7Day and $endYmd $newWhereSql group by regDate,pf,country;";
		if ($_COOKIE['u']=='yd'){
			echo $sql;
		}
		$result = query_infobright($sql);
		$resultVal=$result['ret']['data'];
	}
	
	/*
	$url = 'http://10.60.99.54:8080/phpInterface';
	$data = array('start'=>$before7Day, 'end'=>$endYmd, 'country'=>"$countryParams", 'sesNum'=>5, 'serverNum'=>280);
	$jsonRet = get($url,$data);
	$resultVal = array();
	foreach (json_decode($jsonRet) as $indexKey=>$regValue){
		if ($indexKey=="costTime"){
			if ($_COOKIE['u']=='yd'){
				echo "$indexKey:$regValue\n";
			}
			continue;
		}
		$temp=explode(',', $indexKey);
		$one=array();
		$one['regDate']=$temp[0];
		$one['pf']=$temp[1];
		$one['country']=$temp[2];
		$one['reg']=$regValue;
		$resultVal[]=$one;
	}
	*/
	
	
	
	$dayData=array();
	$iosData=array();
	$chartCountryData=array();
	foreach ($resultVal as $curRow){
		if ($curCountry!='ALL' && $curCountry!="OTHER"){
			if ($curRow['country']==$curCountry){
				if ($curRow['pf']=="AppStore"){
					$iosData[$curRow['regDate']]+=$curRow['reg'];
				}else {
						
					$dayData[$curRow['regDate']]+=$curRow['reg'];
				}
			}
		}elseif ($curCountry=='OTHER'){
			if (!array_key_exists($curRow['country'], $displayCountryArr)){
				if ($curRow['pf']=="AppStore"){
					$iosData[$curRow['regDate']]+=$curRow['reg'];
				}else {
					$dayData[$curRow['regDate']]+=$curRow['reg'];
				}
			}
		}else {
			if ($curRow['pf']=="AppStore"){
				$iosData[$curRow['regDate']]+=$curRow['reg'];
			}else {
				$dayData[$curRow['regDate']]+=$curRow['reg'];
			}
		}
		
		if ($curRow['regDate']>=$startYmd && $curRow['regDate']<=$endYmd){
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
			
			if ($curRow['regDate']==$endYmd){
				if ($curCountry=='OTHER'){
					if (!array_key_exists($curRow['country'], $displayCountryArr)){
						$pfPieData[$findPf]+=$curRow['reg'];
						$countryPieData[$cou]+=$curRow['reg'];
					}
				}else {
					$pfPieData[$findPf]+=$curRow['reg'];
					$countryPieData[$cou]+=$curRow['reg'];
				}
				
			}
			
			$chartCountryData[$cou][$curRow['regDate']]+=$curRow['reg'];
			
			if ($curCountry!='ALL' && $curCountry!="OTHER"){
				if ($curRow['country']==$curCountry){
					$total_num[$curRow['regDate']]+=$curRow['reg'];
				}
			}elseif ($curCountry=='OTHER'){
				if (!array_key_exists($curRow['country'], $displayCountryArr)){
					$total_num[$curRow['regDate']]+=$curRow['reg'];
				}
			}else {
				$total_num[$curRow['regDate']]+=$curRow['reg'];
			}
			
			$pfData_num[$findPf][$curRow['regDate']][$cou]+=$curRow['reg'];
			
			if (!in_array($curRow['regDate'], $dateList)){
				$dateList[]=$curRow['regDate'];
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
//	$dateStr=json_encode($dateList);//可以用这个替代
	ksort($dayData);
	ksort($iosData);
	$showChart['7Average']=dayAverage($dayData, $dateList, $day7Ave);
	$showChart['30Average']=dayAverage($dayData, $dateList, $day30Ave);
	$showChart['daily']=dayAverage($dayData, $dateList, 1);
	$iosShowChart['7Average']=dayAverage($iosData, $dateList, $day7Ave);
	$iosShowChart['30Average']=dayAverage($iosData, $dateList, $day30Ave);
	$iosShowChart['daily']=dayAverage($iosData, $dateList, 1);
	
	$totalTdBackgroundCol=array();
	foreach ($dateList as $k=>$d){
		if ($k==0){
			$total[$dateList[$k]]=number_format($total_num[$dateList[$k]],0);
			continue;
		}
		$totalTdBackgroundCol[$dateList[$k]]='';
		if ($total_num[$dateList[$k]]>$total_num[$dateList[$k-1]]){
			$total[$dateList[$k]]=number_format($total_num[$dateList[$k]],0)."<strong><font color='red'>&#8593;</font></strong>";
			$totalTdBackgroundCol[$dateList[$k]]="up";   //style='background-color: darkred;'
		}elseif ($total_num[$dateList[$k]]<$total_num[$dateList[$k-1]]){
			$total[$dateList[$k]]=number_format($total_num[$dateList[$k]],0)."<strong><font color='green'>&#8595;</font></strong>";
			$totalTdBackgroundCol[$dateList[$k]]="down";  //style='background-color: cadetblue;'
		}
	}
	
	$tdBackgroundCol=array();
	foreach ($displayPf as $pk=>$pv){
		$i=0;
		foreach ($dateList as $k=>$d){
			if ($k==0){
				$pfData[$pk][$dateList[$k]]['total']=number_format($pfData_num[$pk][$dateList[$k]]['total'],0);
				foreach ($displayCountryArr as $countryKey=>$countryVal){
					$pfData[$pk][$dateList[$k]][$countryKey]=number_format($pfData_num[$pk][$dateList[$k]][$countryKey],0);
				}
				continue;
			}
			$tdBackgroundCol[$pk][$dateList[$k]]['total']='';
			if ($pfData_num[$pk][$dateList[$k]]['total']>$pfData_num[$pk][$dateList[$k-1]]['total']){
				$pfData[$pk][$dateList[$k]]['total']=number_format($pfData_num[$pk][$dateList[$k]]['total'],0)."<strong><font color='red'>&#8593;</font></strong>";
				$tdBackgroundCol[$pk][$dateList[$k]]['total']='up';
			}elseif ($pfData_num[$pk][$dateList[$k]]['total']<$pfData_num[$pk][$dateList[$k-1]]['total']){
				$pfData[$pk][$dateList[$k]]['total']=number_format($pfData_num[$pk][$dateList[$k]]['total'],0)."<strong><font color='green'>&#8595;</font></strong>";
				$tdBackgroundCol[$pk][$dateList[$k]]['total']='down';
			}
			
			foreach ($displayCountryArr as $countryKey=>$countryVal){
				$tdBackgroundCol[$pk][$dateList[$k]][$countryKey]='';
				if (isset($pfData_num[$pk][$dateList[$k]][$countryKey]) && !empty($pfData_num[$pk][$dateList[$k]][$countryKey])){
					if ($pfData_num[$pk][$dateList[$k]][$countryKey]>$pfData_num[$pk][$dateList[$k-1]][$countryKey]){
						$pfData[$pk][$dateList[$k]][$countryKey]=number_format($pfData_num[$pk][$dateList[$k]][$countryKey],0)."<strong><font color='red'>&#8593;</font></strong>";
						$tdBackgroundCol[$pk][$dateList[$k]][$countryKey]='up';
					}elseif ($pfData_num[$pk][$dateList[$k]][$countryKey]<$pfData_num[$pk][$dateList[$k-1]][$countryKey]){
						$pfData[$pk][$dateList[$k]][$countryKey]=number_format($pfData_num[$pk][$dateList[$k]][$countryKey],0)."<strong><font color='green'>&#8595;</font></strong>";
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

function get($url, $data = array()){
	$ch = curl_init();   // 初始化一个curl资源类型变量

	/*设置访问的选项*/
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);  // 启用时会将服务器返回的Location: 放在header中递归的返回给服务器，即允许跳转
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );  // 将获得的数据返回而不是直接在页面上输出
	curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTP );  // 设置访问地址用的协议类型为HTTP
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 600);  // 访问的超时时间限制为15s
	$url = $url.'?'.http_build_query($data);
	curl_setopt($ch, CURLOPT_URL, $url);  // 设置即将访问的URL

	$result = curl_exec($ch);  // 执行本次访问，返回一个结果
	curl_close($ch);  // 关闭		
	return $result;
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
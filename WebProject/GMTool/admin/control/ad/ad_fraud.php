<?php
!defined('IN_ADMIN') && exit('Access Denied');
ini_set('memory_limit', '4096M');

if(!$_REQUEST['start_time']){
	$start = date("Y-m-d",time()-86400*40);
}else {
	$start = $_REQUEST['start_time'];
}
if(!$_REQUEST['end_time']){
	$end = date("Y-m-d",time()-86400);
}else {
	$end = $_REQUEST['end_time'];
}

$countrysAdd=array(
	'AD'=>'AD-安道尔',
	'AE'=>'AE-阿联酋',
	'AF'=>'AF-阿富汗',
	'AG'=>'AG-安提瓜和巴布达',
	'AI'=>'AI-安圭拉',
	'AL'=>'AL-阿尔巴尼亚',
	'AM'=>'AM-亚美尼亚',
	'AO'=>'AO-安哥拉',
	'AQ'=>'AQ-南极洲',
	'AR'=>'AR-阿根廷',
	'AS'=>'AS-美属萨摩亚',
	'AT'=>'AT-奥地利',
	'AU'=>'AU-澳大利亚',
	'AW'=>'AW-阿鲁巴',
	'AX'=>'AX-奥兰',
	'AZ'=>'AZ-阿塞拜疆',
	'BA'=>'BA-波斯尼亚和黑塞哥维那',
	'BB'=>'BB-巴巴多斯',
	'BD'=>'BD-孟加拉国',
	'BE'=>'BE-比利时',
	'BF'=>'BF-布基纳法索',
	'BG'=>'BG-保加利亚',
	'BH'=>'BH-巴林',
	'BI'=>'BI-布隆迪',
	'BJ'=>'BJ-贝宁',
	'BL'=>'BL-圣巴泰勒米',
	'BM'=>'BM-百慕大',
	'BN'=>'BN-文莱',
	'BO'=>'BO-玻利维亚',
	'BQ'=>'BQ-加勒比荷兰',
	'BR'=>'BR-巴西',
	'BS'=>'BS-巴哈马',
	'BT'=>'BT-不丹',
	'BV'=>'BV-布韦岛',
	'BW'=>'BW-博茨瓦纳',
	'BY'=>'BY-白俄罗斯',
	'BZ'=>'BZ-伯利兹',
	'CA'=>'CA-加拿大',
	'CC'=>'CC-科科斯（基林）群岛',
	'CD'=>'CD-刚果（金）',
	'CF'=>'CF-中非',
	'CG'=>'CG-刚果（布）',
	'CH'=>'CH-瑞士',
	'CI'=>'CI-科特迪瓦',
	'CK'=>'CK-库克群岛',
	'CL'=>'CL-智利',
	'CM'=>'CM-喀麦隆',
	'CN'=>'CN-中国',
	'CO'=>'CO-哥伦比亚',
	'CR'=>'CR-哥斯达黎加',
	'CU'=>'CU-古巴',
	'CV'=>'CV-佛得角',
	'CW'=>'CW-库拉索',
	'CX'=>'CX-圣诞岛',
	'CY'=>'CY-塞浦路斯',
	'CZ'=>'CZ-捷克',
	'DE'=>'DE-德国',
	'DJ'=>'DJ-吉布提',
	'DK'=>'DK-丹麦',
	'DM'=>'DM-多米尼克',
	'DO'=>'DO-多米尼加',
	'DZ'=>'DZ-阿尔及利亚',
	'EC'=>'EC-厄瓜多尔',
	'EE'=>'EE-爱沙尼亚',
	'EG'=>'EG-埃及',
	'EH'=>'EH-阿拉伯撒哈拉民主共和国',
	'ER'=>'ER-厄立特里亚',
	'ES'=>'ES-西班牙',
	'ET'=>'ET-埃塞俄比亚',
	'FI'=>'FI-芬兰',
	'FJ'=>'FJ-斐济',
	'FK'=>'FK-福克兰群岛',
	'FM'=>'FM-密克罗尼西亚联邦',
	'FO'=>'FO-法罗群岛',
	'FR'=>'FR-法国',
	'GA'=>'GA-加蓬',
	'GB'=>'GB-英国',
	'GD'=>'GD-格林纳达',
	'GE'=>'GE-格鲁吉亚',
	'GF'=>'GF-法属圭亚那',
	'GG'=>'GG-根西',
	'GH'=>'GH-加纳',
	'GI'=>'GI-直布罗陀',
	'GL'=>'GL-格陵兰',
	'GM'=>'GM-冈比亚',
	'GN'=>'GN-几内亚',
	'GP'=>'GP-瓜德罗普',
	'GQ'=>'GQ-赤道几内亚',
	'GR'=>'GR-希腊',
	'GS'=>'GS-南乔治亚和南桑威奇群岛',
	'GT'=>'GT-危地马拉',
	'GU'=>'GU-关岛',
	'GW'=>'GW-几内亚比绍',
	'GY'=>'GY-圭亚那',
	'HK'=>'HK-香港',
	'HM'=>'HM-赫德岛和麦克唐纳群岛',
	'HN'=>'HN-洪都拉斯',
	'HR'=>'HR-克罗地亚',
	'HT'=>'HT-海地',
	'HU'=>'HU-匈牙利',
	'ID'=>'ID-印尼',
	'IE'=>'IE-爱尔兰',
	'IL'=>'IL-以色列',
	'IM'=>'IM-马恩岛',
	'IN'=>'IN-印度',
	'IO'=>'IO-英属印度洋领地',
	'IQ'=>'IQ-伊拉克',
	'IR'=>'IR-伊朗',
	'IS'=>'IS-冰岛',
	'IT'=>'IT-意大利',
	'JE'=>'JE-泽西',
	'JM'=>'JM-牙买加',
	'JO'=>'JO-约旦',
	'JP'=>'JP-日本',
	'KE'=>'KE-肯尼亚',
	'KG'=>'KG-吉尔吉斯斯坦',
	'KH'=>'KH-柬埔寨',
	'KI'=>'KI-基里巴斯',
	'KM'=>'KM-科摩罗',
	'KN'=>'KN-圣基茨和尼维斯',
	'KP'=>'KP-朝鲜',
	'KR'=>'KR-韩国',
	'KW'=>'KW-科威特',
	'KY'=>'KY-开曼群岛',
	'KZ'=>'KZ-哈萨克斯坦',
	'LA'=>'LA-老挝',
	'LB'=>'LB-黎巴嫩',
	'LC'=>'LC-圣卢西亚',
	'LI'=>'LI-列支敦士登',
	'LK'=>'LK-斯里兰卡',
	'LR'=>'LR-利比里亚',
	'LS'=>'LS-莱索托',
	'LT'=>'LT-立陶宛',
	'LU'=>'LU-卢森堡',
	'LV'=>'LV-拉脱维亚',
	'LY'=>'LY-利比亚',
	'MA'=>'MA-摩洛哥',
	'MC'=>'MC-摩纳哥',
	'MD'=>'MD-摩尔多瓦',
	'ME'=>'ME-黑山',
	'MF'=>'MF-法属圣马丁',
	'MG'=>'MG-马达加斯加',
	'MH'=>'MH-马绍尔群岛',
	'MK'=>'MK-马其顿',
	'ML'=>'ML-马里',
	'MM'=>'MM-缅甸',
	'MN'=>'MN-蒙古',
	'MO'=>'MO-澳门',
	'MP'=>'MP-北马里亚纳群岛',
	'MQ'=>'MQ-马提尼克',
	'MR'=>'MR-毛里塔尼亚',
	'MS'=>'MS-蒙特塞拉特',
	'MT'=>'MT-马耳他',
	'MU'=>'MU-毛里求斯',
	'MV'=>'MV-马尔代夫',
	'MW'=>'MW-马拉维',
	'MX'=>'MX-墨西哥',
	'MY'=>'MY-马来西亚',
	'MZ'=>'MZ-莫桑比克',
	'NA'=>'NA-纳米比亚',
	'NC'=>'NC-新喀里多尼亚',
	'NE'=>'NE-尼日尔',
	'NF'=>'NF-诺福克岛',
	'NG'=>'NG-尼日利亚',
	'NI'=>'NI-尼加拉瓜',
	'NL'=>'NL-荷兰',
	'NO'=>'NO-挪威',
	'NP'=>'NP-尼泊尔',
	'NR'=>'NR-瑙鲁',
	'NU'=>'NU-纽埃',
	'NZ'=>'NZ-新西兰',
	'OM'=>'OM-阿曼',
	'PA'=>'PA-巴拿马',
	'PE'=>'PE-秘鲁',
	'PF'=>'PF-法属波利尼西亚',
	'PG'=>'PG-巴布亚新几内亚',
	'PH'=>'PH-菲律宾',
	'PK'=>'PK-巴基斯坦',
	'PL'=>'PL-波兰',
	'PM'=>'PM-圣皮埃尔和密克隆',
	'PN'=>'PN-皮特凯恩群岛',
	'PR'=>'PR-波多黎各',
	'PS'=>'PS-巴勒斯坦',
	'PT'=>'PT-葡萄牙',
	'PW'=>'PW-帕劳',
	'PY'=>'PY-巴拉圭',
	'QA'=>'QA-卡塔尔',
	'RE'=>'RE-留尼汪',
	'RO'=>'RO-罗马尼亚',
	'RS'=>'RS-塞尔维亚',
	'RU'=>'RU-俄罗斯',
	'RW'=>'RW-卢旺达',
	'SA'=>'SA-沙特阿拉伯',
	'SB'=>'SB-所罗门群岛',
	'SC'=>'SC-塞舌尔',
	'SD'=>'SD-苏丹',
	'SE'=>'SE-瑞典',
	'SG'=>'SG-新加坡',
	'SH'=>'SH-圣赫勒拿',
	'SI'=>'SI-斯洛文尼亚',
	'SJ'=>'SJ-斯瓦尔巴群岛和扬马延岛',
	'SK'=>'SK-斯洛伐克',
	'SL'=>'SL-塞拉利昂',
	'SM'=>'SM-圣马力诺',
	'SN'=>'SN-塞内加尔',
	'SO'=>'SO-索马里',
	'SR'=>'SR-苏里南',
	'SS'=>'SS-南苏丹',
	'ST'=>'ST-圣多美和普林西比',
	'SV'=>'SV-萨尔瓦多',
	'SX'=>'SX-荷属圣马丁',
	'SY'=>'SY-叙利亚',
	'SZ'=>'SZ-斯威士兰',
	'TC'=>'TC-特克斯和凯科斯群岛',
	'TD'=>'TD-乍得',
	'TF'=>'TF-法属南部领地',
	'TG'=>'TG-多哥',
	'TH'=>'TH-泰国',
	'TJ'=>'TJ-塔吉克斯坦',
	'TK'=>'TK-托克劳',
	'TL'=>'TL-东帝汶',
	'TM'=>'TM-土库曼斯坦',
	'TN'=>'TN-突尼斯',
	'TO'=>'TO-汤加',
	'TR'=>'TR-土耳其',
	'TT'=>'TT-特立尼达和多巴哥',
	'TV'=>'TV-图瓦卢',
	'TW'=>'TW-台湾',
	'TZ'=>'TZ-坦桑尼亚',
	'UA'=>'UA-乌克兰',
	'UG'=>'UG-乌干达',
	'UM'=>'UM-美国本土外小岛屿',
	'US'=>'US-美国',
	'UY'=>'UY-乌拉圭',
	'UZ'=>'UZ-乌兹别克斯坦',
	'VA'=>'VA-梵蒂冈',
	'VC'=>'VC-圣文森特和格林纳丁斯',
	'VE'=>'VE-委内瑞拉',
	'VG'=>'VG-英属维尔京群岛',
	'VI'=>'VI-美属维尔京群岛',
	'VN'=>'VN-越南',
	'VU'=>'VU-瓦努阿图',
	'WF'=>'WF-瓦利斯和富图纳',
	'WS'=>'WS-萨摩亚',
	'YE'=>'YE-也门',
	'YT'=>'YT-马约特',
	'ZA'=>'ZA-南非',
	'ZM'=>'ZM-赞比亚',
	'ZW'=>'ZW-津巴布韦'
);
$osList=array(
		'ALL'=>'OS: ALL',
		'android'=>'Android',
		'ios'=>'IOS'
);
$ifPayList=array(
	3=>'全部用户',
	0=>'非付费用户',
	1=>'付费用户',
);
$displayCountryArr=array(
		'US',
		'JP',
		'CN',
		'KR',
		'TW',
		'RU',
		'HK',
		'MO',
		'GB',
		'DE',
		'FR',
		'TR',
		'AE',
		'AU',
		'NZ',
		'IT',
		'ES',
		'NO',
		'IR',
		'ID',
		'SG',
		'MY',
		'TH',
		'VN',
		'BR',
		'SA',
);

$param=array(
	'installdate'=>'日期',
	'os'=>'系统',
	'country'=>'国家',
	'firstchannel'=>'一级渠道',
	'countrycode'=>'作弊国家',
);
$sql="select os,firstchannel from ad_fraud_lifetime group by os,firstchannel;";
$res = query_bqresult($sql);
$osChannel=array();
foreach ($res as $row){
	$osChannel[$row['os']][$row['firstchannel']]=$row['firstchannel'];
	if (!isset($osChannel['ALL'][$row['firstchannel']])){
		$osChannel['ALL'][$row['firstchannel']]=$row['firstchannel'];
	}
};
$titleArray=array(
		'installdate'=>'日期',
		'cnt'=>'作弊量',
);
$titleIndex=array(
	'installdate'=>0,
	'cnt'=>1,
);
$numArray=array();
for ($n=1;$n<=2;$n++){
	$numArray[]=$n;
}
$columnIndex=array();
for ($ci=0;$ci<=1;$ci++){
	$columnIndex[$ci]=$ci+1;
}
$dbCol=array(
		'cnt',
);
$colStr='';
$split='';
foreach ($dbCol as $col){
	$colStr.=$split."sum($col) as $col";
	$split=',';
}


//数据展示
$startYmd=date('Y-m-d',strtotime($start));
$endYmd=date('Y-m-d',strtotime($end));
if (!$_REQUEST['os']){
	$curOs='ALL';
}else {
	$curOs=$_REQUEST['os'];
}
if (!$_REQUEST['ifPay']&&$_REQUEST['ifPay']!='0'){
	$curIfPay=3;
}else {
	$curIfPay=$_REQUEST['ifPay'];
}
if (!$_REQUEST['selectCountry']){
	$currCountry='ALL';
}else {
	$currCountry=$_REQUEST['selectCountry'];
}
if (!$_REQUEST['topChannel_'.$curOs]||$_REQUEST['topChannel_'.$curOs]=='ALL'){
	$curTopChannel='ALL';
	$flag='firstchannel';
}else {
	$curTopChannel=$_REQUEST['topChannel_'.$curOs];
	$flag='countrycode';
}
$whereSql=" where installdate between '$startYmd' and '$endYmd' ";
$oswhere='';
if($curOs && $curOs!='ALL'){
	$oswhere =" and os='$curOs' ";
}
$ifpaywhere='';
if($curIfPay!=3){
	$ifpaywhere =" and payor='$curIfPay' ";
}
$countrywhere='';
if ($currCountry && $currCountry!="ALL"){
	$countrywhere = " and country ='$currCountry' ";
}

$channeltopwhere='';
if ($curTopChannel && $curTopChannel!='ALL'){
	$channeltopwhere =" and firstchannel='$curTopChannel' ";
}

//$totaldata=array();
//$datearray=array();
//
//foreach ($param as $K=>$V){
//	if ($K=='installdate'){
//		$sql="select installdate,$colStr from ad_fraudip_mid $whereSql $oswhere $countrywhere $channeltopwhere group by installdate order by installdate;";
//	}elseif ($K=='os'){
//		$sql="select os,$colStr from ad_fraudip_mid $whereSql $oswhere $countrywhere $channeltopwhere group by os order by cnt desc;";
//	}elseif ($K=='country'){
//		$sql="select country,$colStr from ad_fraudip_mid $whereSql $oswhere $countrywhere $channeltopwhere group by country  order by cnt desc limit 20;";
//	}elseif ($K=='firstchannel'){
//		$sql="select  firstchannel,$colStr from ad_fraudip_mid $whereSql $oswhere $countrywhere $channeltopwhere group by firstchannel order by cnt desc limit 20;";
//	}else{
//		$sql="select  countrycode,$colStr from ad_fraudip_mid $whereSql $oswhere $countrywhere $channeltopwhere group by countrycode  order by cnt desc limit 20;";
//	}
//
//	$res = query_bqresult($sql);
//	$data=array();
//	$total=array();
//	foreach ($res as $row){
//		$one=array();
//		$one[$K]=$row[$K];
//		if ($K=='installdate'){
//			$temp=date('Ymd',strtotime($row['installdate']));
//			$datearray[]=date('Ymd',strtotime($row['installdate']));
//		}else {
//			$temp=$row[$K];
//		}
//		foreach ($dbCol as $col){
//			$one[$col]=$row[$col];
//			$total[$col]+=$row[$col];
//		}
//		$data[$temp]=$one;
//		$xarray[$K][]=$row[$K];
//	}
//
//	$dataTemp=array();
//	if ($K=='country'){
//		foreach ($displayCountryArr as $cou){
//			foreach ($data as $cK=>$dbVal){
//				if ($cK==$cou){
//					$dataTemp[]=$dbVal;
//				}
//			}
//		}
//		foreach ($data as $dbVal){
//			if (!in_array($dbVal['country'],$displayCountryArr)){
//				$dataTemp[]=$dbVal;
//			}
//		}
//		unset($data);
//		$data=$dataTemp;
//	}elseif ($K=='installdate'){
//		krsort($data);
//	}elseif ($K=='countrycode'){
//		foreach ($displayCountryArr as $cou){
//			foreach ($data as $cK=>$dbVal){
//				if ($cK==$cou){
//					$dataTemp[]=$dbVal;
//				}
//			}
//		}
//		foreach ($data as $dbVal){
//			if (!in_array($dbVal['countrycode'],$displayCountryArr)){
//				$dataTemp[]=$dbVal;
//			}
//		}
//		unset($data);
//		$data=$dataTemp;
//	}
//
//	$totaldata[$K]['data']=$data;
//	$totaldata[$K]['total']=$total;
//	krsort($datearray);
//
//}


if($flag=='firstchannel'){
	$sql="select  firstchannel,$colStr from ad_fraudip_mid $whereSql $oswhere $countrywhere $channeltopwhere group by firstchannel order by cnt desc limit 20;";
	$clicksql="select  firstchannel,sum(total) totalcnt,sum(less30) cnt from ad_fraudclick $whereSql $oswhere $countrywhere $channeltopwhere group by firstchannel order by cnt desc limit 20;";
}else{
	$sql="select  countrycode,$colStr from ad_fraudip_mid $whereSql $oswhere $countrywhere $channeltopwhere group by countrycode  order by cnt desc limit 20;";
	$clicksql="select  country as countrycode,sum(total) totalcnt,sum(less30) cnt from ad_fraudclick $whereSql $oswhere $countrywhere $channeltopwhere group by countrycode order by cnt desc limit 20;";
}

$lifetimesql="select  peroid,$colStr from ad_fraud_lifetime $whereSql $oswhere $countrywhere $channeltopwhere $ifpaywhere group by peroid  order by peroid";
$lvdistsql="select  level,$colStr from ad_fraud_lvdistribute $whereSql $oswhere $countrywhere $channeltopwhere $ifpaywhere group by level  order by level";

$ltres=query_bqresult($lifetimesql);
$ltdata=array();
foreach ($ltres as $row){
	$one=array();
	$one['peroid']=$row['peroid'];
	$one['cnt']=$row['cnt'];
	$ltdata[$row['peroid']]=$one;
}

$ltxarray=array_keys($ltdata);
$ltxStr='[\''.implode('\',\'', $ltxarray).'\']';
$ltchartscnt=array();
foreach ($ltdata as $cdataK=>$cdataV){
	$ltchartscnt[$cdataK]= $cdataV['cnt'];
}
if ($_COOKIE['u']=='wanghaobi'){
// 	print_r($ltdata);
	echo $lifetimesql;
	echo $curIfPay;
	echo $_REQUEST['ifPay'];
}
$ltshowChart='['.implode(',',$ltchartscnt).']';

$lvres=query_bqresult($lvdistsql);
$lvdata=array();
foreach ($lvres as $row){
	$one=array();
	$one['level']=$row['level'];
	$one['cnt']=$row['cnt'];
	$lvdata[$row['level']]=$one;
}

$lvxarray=array_keys($lvdata);
$lvxStr='[\''.implode('\',\'', $lvxarray).'\']';
$lvchartscnt=array();
foreach ($lvdata as $cdataK=>$cdataV){
	$lvchartscnt[$cdataK]= $cdataV['cnt'];
}
if ($_COOKIE['u']=='wanghaobi'){
// 	print_r($lvdata);
	echo $lvdistsql;
}
$lvshowChart='['.implode(',',$lvchartscnt).']';


$clires=query_bqresult($clicksql);
$clidata=array();
foreach ($clires as $clirow){
	$one=array();
	$one[$flag]=$clirow[$flag];
	$one['totalcnt']=$clirow['totalcnt'];
	$one['cnt']=$clirow['cnt'];
	$one['percent']=$clirow['totalcnt'] ? number_format(($clirow['cnt']/$clirow['totalcnt'])*100,2,'.','') : 0;
	$clidata[$clirow[$flag]]=$one;
}

$clixarray=array_keys($clidata);
$clickxStr='[\''.implode('\',\'', $clixarray).'\']';
$chartscnt=array();
$chartspercent=array();
foreach ($clidata as $cdataK=>$cdataV){
	$chartscnt[$cdataK]= $cdataV['cnt'];
	$chartspercent[$cdataK]= $cdataV['percent'];
}
if ($_COOKIE['u']=='yd'){
// 	print_r($clidata);
	echo $clicksql;
}
$clishowChart='['.implode(',',$chartscnt).']';

$res = query_bqresult($sql);
$data=array();
$total=array();
foreach ($res as $row){
	$one=array();
	$one[$flag]=$row[$flag];
	if ($flag=='installdate'){
		$temp=date('Ymd',strtotime($row['installdate']));
	}else {
		$temp=$row[$flag];
	}
	foreach ($dbCol as $col){
		$one[$col]=$row[$col];
		$total[$col]+=$row[$col];
	}
	$data[$temp]=$one;
	$xarray[]=$row[$flag];
}

$dataTemp=array();
if ($flag=='countrycode'){
	foreach ($displayCountryArr as $cou){
		foreach ($data as $cK=>$dbVal){
			if ($cK==$cou){
				$dataTemp[]=$dbVal;
			}
		}
	}
	foreach ($data as $dbVal){
		if (!in_array($dbVal['countrycode'],$displayCountryArr)){
			$dataTemp[]=$dbVal;
		}
	}
	unset($data);
	$data=$dataTemp;
}

krsort($data);
$firstchannelStr='[\''.implode('\',\'', $xarray).'\']';
$cnt2=array();
foreach ($data as $dataK=>$dataV){
	$cnt2[$dataK]= $dataV['cnt'];
}
rsort($cnt2);
$showChart2='['.implode(',',$cnt2).']';


if ($_REQUEST['event']=='output'){
	$titlearr=array(
		'installdate'=>'安装日期',
		'uid'=>'gaid',
		'os'=>'操作系统',
		'firstchannel'=>'一级渠道',
		'secondchannel'=>'二级渠道',
		'country'=>'国家',
		'countrycode'=>'作弊国家',
	);

	$sql="select installdate,uid,os,firstchannel,secondchannel,country,countrycode from ad_fraudip $whereSql $oswhere $countrywhere $channeltopwhere order by installdate";
//	echo $sql;
	$res = query_bqresult($sql);

	$data=array();
	foreach ($res as $row){
		$one=array();
		foreach ($titlearr as $K=>$V){
			$one[$K]=$row[$K];
		}
		$data[]=$one;
	}

//	print_r($data);

	$titleStr='';
	$prefix='';
	foreach ($titlearr as $tk=>$tv){
		$titleStr .=$prefix.$tv;
		$prefix=',';
	}
	$title=explode(',', $titleStr);

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
	foreach ($data as $dbData){
		$i=0;
		foreach ($titlearr as $tk=>$tv){
			if ($tk=='country' && $countrysAdd[$dbData['country']]){
				$Excel->setCellValue(getNameFromNumber($i).''.$row, $countrysAdd[$dbData['country']]);
			}else {
				$Excel->setCellValue(getNameFromNumber($i).''.$row, $dbData[$tk]);
			}
			$i++;
		}
		$row++;
	}
	//filename
	$file_name = '广告作弊IP明细';
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

function getNameFromNumber($num)
{
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
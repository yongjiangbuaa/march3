<?php
!defined('IN_ADMIN') && exit('Access Denied');
ini_set('memory_limit', '4096M');
if(!$_REQUEST['start_time']){
	$start = date("Y-m-d",time()-86400*30);
}else {
	$start = $_REQUEST['start_time'];
}
if(!$_REQUEST['end_time']){
	$end = date("Y-m-d",time()-86400);
}else {
	$end = $_REQUEST['end_time'];
}

$osList=array(
		'ALL'=>'OS: ALL',
		'android'=>'Android',
		'ios'=>'IOS'
);
$dimensionArray=array(
	'Day',
	'国家',
	'OS',
	'一级渠道',
);
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
$sql="select os,firstchannel from user_history_info group by os,firstchannel order by firstchannel;";
$res = query_bqresult($sql);
$osChannel=array();
foreach ($res as $row){
	$osChannel[$row['os']][$row['firstchannel']]=$row['firstchannel'];
	if (!isset($osChannel['ALL'][$row['firstchannel']])){
		$osChannel['ALL'][$row['firstchannel']]=$row['firstchannel'];
	}
};
$titleArray=array(
		'dt'=>'日期',
		'usernum'=>'总人数',
		'paysum'=>'金额',
		'payuser'=>'付费人数',
		'penetrance'=>'渗透率(%)',
		'arpu'=>'ARPU',
		'arppu'=>'ARPPU',

);
$titleIndex=array(
	'dt'=>0,
	'usernum'=>1,
	'paysum'=>2,
	'payuser'=>3,
	'penetrance'=>4,
	'arpu'=>5,
	'arppu'=>6,
);
$numArray=array();
for ($n=1;$n<=3;$n++){
	$numArray[]=$n;
}
$columnIndex=array();
for ($ci=0;$ci<=2;$ci++){
	$columnIndex[$ci]=$ci+1;
}
$dbCol=array(
		'usernum',
		'paysum',
		'payuser',
);
$colStr='';
$split='';
foreach ($dbCol as $col){
	$colStr.=$split."sum($col) as $col";
	$split=',';
}
$organicFlag=false;
if($_REQUEST['includeOrganic']){
	$organicFlag =true;
}

//数据展示
$startYmd=date('Y-m-d',strtotime($start));
$endYmd=date('Y-m-d',strtotime($end));
if (!$_REQUEST['os']){
	$curOs='ALL';
}else {
	$curOs=$_REQUEST['os'];
}
if (!$_REQUEST['selectCountry']){
	$currCountry='ALL';
}else {
	$currCountry=$_REQUEST['selectCountry'];
}
if (!$_REQUEST['topChannel_'.$curOs]){
	$curTopChannel='ALL';
}else {
	$curTopChannel=$_REQUEST['topChannel_'.$curOs];
}
$currdimension=$_REQUEST['dimension'];
$dtwhereSql=" where dt between '$startYmd' and  '$endYmd' ";
$whereSql=" where dt = '$endYmd' ";
$oswhere='';
if($curOs && $curOs!='ALL'){
	$oswhere =" and os='$curOs' ";
}
$countrywhere='';
if ($currCountry && $currCountry!="ALL"){
	$countrywhere = " and country ='$currCountry' ";
}

$channeltopwhere='';
if ($curTopChannel && $curTopChannel!='ALL'){
	$channeltopwhere =" and firstchannel='$curTopChannel' ";
}


if (!$organicFlag){
	$whereSql .= "and firstchannel !='Organic' ";
	$dtwhereSql .= "and firstchannel !='Organic' ";
}


$sql="select dt,$colStr from user_history_info $dtwhereSql $oswhere $countrywhere $channeltopwhere group by dt order by dt;";
$curvesql="select dt,sum(value) s,valuetype from user_info_gaid $dtwhereSql $oswhere $countrywhere $channeltopwhere group by dt,valuetype order by dt";
$res = query_bqresult($curvesql);
$curvedata=array();
$xarr=array();
$yarr=array();
foreach ($res as $row){
	$curvedata[$row['dt']][$row['valuetype']]=$row['s'];
}
ksort($curvedata);
foreach($curvedata as $k=>$v){
	$xarr[]=$k;
	$yarr['penetrance'][]=number_format($v['payuser']/$v['dau']*100,2);
	$yarr['arpu'][]=number_format($v['pay']/$v['dau'],2);
	$yarr['arppu'][]=number_format($v['pay']/$v['payuser'],2);
}

$param='dt';
$res = query_bqresult($sql);
$data=array();
$total=array();

$show=array();
foreach ($res as $row){
	$one=array();
	$one[$param]=$row[$param];
//	$xarr[]=$row[$param];
	$temp=$row[$param];
	foreach ($dbCol as $col){
		$one[$col]=$row[$col];
		$total[$col]+=$row[$col];
//		$yarr[$col][]=$row[$col];
	}
	$one['penetrance']=$curvedata[$row[$param]]['dau']>0?number_format($curvedata[$row[$param]]['payuser']/$curvedata[$row[$param]]['dau']*100,2):0;
	$one['arpu']=$curvedata[$row[$param]]['dau']>0?number_format($curvedata[$row[$param]]['pay']/$curvedata[$row[$param]]['dau'],2):0;
	$one['arppu']=$curvedata[$row[$param]]['payuser']>0?number_format($curvedata[$row[$param]]['pay']/$curvedata[$row[$param]]['payuser'],2):0;
	$total['gaiddau']+=$curvedata[$row[$param]]['dau'];
	$total['gaidpay']+=$curvedata[$row[$param]]['pay'];
	$total['gaidpayuser']+=$curvedata[$row[$param]]['payuser'];
	$data[$temp]=$one;
}
ksort($data);
foreach($data as $k => $v){
	foreach($dbCol as $col){
		$yarr[$col][]=$v[$col];
	}
//	$yarr['penetrance'][]=number_format($v['payuser']/$v['usernum']*100,2);
//	$yarr['arpu'][]=number_format($v['paysum']/$v['usernum'],2);
//	$yarr['arppu'][]=number_format($v['paysum']/$v['payuser'],2);
	$xarr[]=$v[$param];
}

$total['penetrance']=number_format($total['gaidpayuser']/$total['gaiddau']*100,2);
$total['arpu']=number_format($total['gaidpay']/$total['gaiddau'],2);
$total['arppu']=number_format($total['gaidpay']/$total['gaidpayuser'],2);


$dataTemp=array();
if ($param=='country'){
	foreach ($displayCountryArr as $cou){
		foreach ($data as $cK=>$dbVal){
			if ($cK==$cou){
				$dataTemp[]=$dbVal;
			}
		}
	}
	foreach ($data as $dbVal){
		if (!in_array($dbVal['country'],$displayCountryArr)){
			$dataTemp[]=$dbVal;
		}
	}
	unset($data);
	$data=$dataTemp;
}elseif ($param=='date'){
	krsort($data);
}


$indexArr=array(
	'penetrance'=>'渗透率',
	'arpu'=>'arpu',
	'arppu'=>'arppu',
);
$coordinates=array(
	'penetrance'=>0,
	'arpu'=>0,
	'arppu'=>1,
);
$showchart=array();

//if($param=='country'||$param=='firstchannel'){
//	foreach($indexArr as $k=>$v){
//		$showchart[$k]='['.implode(',', array_slice($yarr[$k],0,10)).']';
//	}
//	$xstr='[\''.implode('\',\'', array_slice($xarr,0,10)).'\']';
//}else{
	foreach($indexArr as $k=>$v){
		$showchart[$k]='['.implode(',', $yarr[$k]).']';
	}
	$xstr='[\''.implode('\',\'', $xarr).'\']';
//}



if($currdimension!=0){
	if ($currdimension==1){
		$sql="select country,$colStr from user_history_info $whereSql $oswhere $countrywhere $channeltopwhere group by country order by paysum;";
		$curvesql="select country,sum(value) s,valuetype from user_info_gaid $dtwhereSql $oswhere $countrywhere $channeltopwhere group by country,valuetype order by s";
		$param='country';
		$titleArray['dt']='国家';
	}elseif ($currdimension==2){
		$sql="select os,$colStr from user_history_info $whereSql $oswhere $countrywhere $channeltopwhere group by os order by paysum;";
		$curvesql="select os,sum(value) s,valuetype from user_info_gaid $dtwhereSql $oswhere $countrywhere $channeltopwhere group by os,valuetype order by s";
		$param='os';
		$titleArray['dt']='系统';
	}elseif ($currdimension==3){
		$sql="select firstchannel,$colStr from user_history_info $whereSql $oswhere $countrywhere $channeltopwhere group by firstchannel order by paysum;";
		$curvesql="select firstchannel,sum(value) s,valuetype from user_info_gaid $dtwhereSql $oswhere $countrywhere $channeltopwhere group by firstchannel,valuetype order by s";
		$param='firstchannel';
		$titleArray['dt']='一级渠道';
	}


	$res = query_bqresult($curvesql);
	$curvedata=array();
	foreach ($res as $row){
		$curvedata[$row[$param]][$row['valuetype']]=$row['s'];
	}

	$res = query_bqresult($sql);
	$data=array();
	$total=array();
	$show=array();
	foreach ($res as $row){
		$one=array();
		$one[$param]=$row[$param];
//	$xarr[]=$row[$param];
		$temp=$row[$param];
		foreach ($dbCol as $col){
			$one[$col]=$row[$col];
			$total[$col]+=$row[$col];
//		$yarr[$col][]=$row[$col];
		}
		$one['penetrance']=$curvedata[$row[$param]]['dau']>0?number_format($curvedata[$row[$param]]['payuser']/$curvedata[$row[$param]]['dau']*100,2):0;
		$one['arpu']=$curvedata[$row[$param]]['dau']>0?number_format($curvedata[$row[$param]]['pay']/$curvedata[$row[$param]]['dau'],2):0;
		$one['arppu']=$curvedata[$row[$param]]['payuser']>0?number_format($curvedata[$row[$param]]['pay']/$curvedata[$row[$param]]['payuser'],2):0;
		$total['gaiddau']+=$curvedata[$row[$param]]['dau'];
		$total['gaidpay']+=$curvedata[$row[$param]]['pay'];
		$total['gaidpayuser']+=$curvedata[$row[$param]]['payuser'];
		$data[$temp]=$one;
		$show[$row['paysum']]=$one;
	}
	krsort($show);

	$total['penetrance']=number_format($total['gaidpayuser']/$total['gaiddau']*100,2);
	$total['arpu']=number_format($total['gaidpay']/$total['gaiddau'],2);
	$total['arppu']=number_format($total['gaidpay']/$total['gaidpayuser'],2);


	$dataTemp=array();
	if ($param=='country'){
		foreach ($displayCountryArr as $cou){
			foreach ($data as $cK=>$dbVal){
				if ($cK==$cou){
					$dataTemp[]=$dbVal;
				}
			}
		}
		foreach ($data as $dbVal){
			if (!in_array($dbVal['country'],$displayCountryArr)){
				$dataTemp[]=$dbVal;
			}
		}
		unset($data);
		$data=$dataTemp;
	}elseif ($param=='date'){
		krsort($data);
	}

//echo $sql;
//print_r($data);

}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>
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
if (!$_REQUEST['selectCountry']){
	$currCountry='ALL';
}else {
	$currCountry=$_REQUEST['selectCountry'];
}
if ($_REQUEST['selectPf']){
	$currPf=$_REQUEST['selectPf'];
}else {
	$currPf='ALL';
}
$allServerFlag=false;
if($_REQUEST['allServers']){
	$allServerFlag =true;
}
$titleArray=array(
	'date'=>'日期',
	'pf'=>'平台',
	'country'=>'国家',
	'dau'=>'日活跃',
	'devicedau'=>'机器码DAU	',
	'paydau'=>'付费DAU',
	'old'=>'老玩家',
	'reg'=>'新注册',
// 		'reg'=>'新注册(全部)',
// 		'adreg'=>'新注册（广告）',
// 		'orreg'=>'新注册（自然）',
	'replay'=>'重玩',
	'move'=>'迁服',
	'paysum'=>'付费总值',
	'getgold_paygoldsum'=>'充值金币',
	'costgold_paygoldsum'=>'消费金币',
	'payuser'=>'付费用户',
	'paycount'=>'付费次数',
	'firstpay'=>'首充人数',
	'payrate'=>'付费率(%)',
	'regpayrate'=>'新增付费率(%)',
	'ARPU'=>'ARPU',
	'COSTGOLDARPU'=>'消费金币ARPU',
	'ARPPU'=>'ARPPU',
// 		'retention1'=>'次日登陆',
	'rate1'=>'次日留存(%)',
// 		'retention3'=>'3日登陆',
	'rate3'=>'3日留存(%)',
// 		'retention7'=>'7日登陆',
	'rate7'=>'7日留存(%)',
// 		'retention14'=>'14日登陆',
	'rate14'=>'14日留存(%)',
// 		'retention30'=>'30日登陆',
	'rate30'=>'30日留存(%)',
);
$columns=array(
	'reg',
	'replay',
	'move',
	'dau',
	'paydau',
	'devicedau',
	'firstpay',
	'newregpay',
	'paysum',
	'payuser',
	'paycount',
);
$rateArray=array(
	'rate1'=>'retention1',
	'rate3'=>'retention3',
	'rate7'=>'retention7',
	'rate14'=>'retention14',
	'rate30'=>'retention30',
);
$dimensionArray=array(
	'日期',
	'国家',
	'平台',
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

if ($_REQUEST['dimension']){
	$currdimension=$_REQUEST['dimension'];
}else {
	$currdimension=0;
}

$startYmd=date('Y-m-d',strtotime($start));
$endYmd=date('Y-m-d',strtotime($end));
$sidcolumn='';
$countrywhere='';
if ($allServerFlag){
	$user_basic_table="user_basic_server";
	$user_dau_table="user_dau_server";
	$user_pay_table="user_pay_server";
	$user_retention_table="user_retention_server";
	$user_paygold_table="user_paygold_server";
	if ((strtotime($endYmd)-strtotime($startYmd)>(86400*2))){
		$startYmd=date('Y-m-d',strtotime($endYmd)-86400*2);
	}
	$sidcolumn="server,";
}elseif ($currCountry && $currCountry!="ALL" && $currPf=='ALL'){
	$countrywhere = " and country ='$currCountry' ";
	$user_basic_table="user_basic_country";
	$user_dau_table="user_dau_country";
	$user_pay_table="user_pay_country";
	$user_retention_table="user_retention_country";
	$user_paygold_table="user_paygold_country";
}elseif ($currPf && $currPf!='ALL' && $currCountry=='ALL'){
	$countrywhere = " and pf ='$currPf' ";
	$user_basic_table="user_basic_pf";
	$user_dau_table="user_dau_pf";
	$user_pay_table="user_pay_pf";
	$user_retention_table="user_retention_pf";
	$user_paygold_table="user_paygold_pf";
}elseif (($currCountry && $currCountry!="ALL" && $currPf && $currPf!='ALL')){
	$countrywhere = " and country ='$currCountry' and pf ='$currPf' ";
	$user_basic_table="user_basic_pf_country";
	$user_dau_table="user_dau_pf_country";
	$user_pay_table="user_pay_pf_country";
	$user_retention_table="user_retention_pf_country";
	$user_paygold_table="user_paygold_pf_country";
}else {
	$user_basic_table="user_basic_total";
	$user_dau_table="user_dau_total";
	$user_pay_table="user_pay_total";
	$user_retention_table="user_retention_total";
	$user_paygold_table="user_paygold_total";
}
$param='date';
if ($currdimension==1){
	$param='country';
	$user_basic_table="user_basic_pf_country";
	$user_dau_table="user_dau_pf_country";
	$user_pay_table="user_pay_pf_country";
	$user_retention_table="user_retention_pf_country";
	$user_paygold_table="user_paygold_pf_country";
}elseif ($currdimension==2) {
	$param='pf';
	$user_basic_table="user_basic_pf_country";
	$user_dau_table="user_dau_pf_country";
	$user_pay_table="user_pay_pf_country";
	$user_retention_table="user_retention_pf_country";
	$user_paygold_table="user_paygold_pf_country";
}

$sql="select $param,$sidcolumn valueType,value from $user_basic_table where date between '$startYmd' and '$endYmd' $countrywhere;";
$total=array();
$res = query_bqresult($sql);
if ($allServerFlag){
	foreach ($res as $row){
		if ($row['server']>8000){
			continue;
		}
		$total[$row['server']][$row[$param]][$row['valueType']]+=$row['value'];
	}
}else {
	foreach ($res as $row){
		$total[$row[$param]][$row['valueType']]+=$row['value'];
	}
}
$sql="select $param,$sidcolumn valueType,value from $user_dau_table where date between '$startYmd' and '$endYmd' $countrywhere;";
$res = query_bqresult($sql);
if ($allServerFlag){
	foreach ($res as $row){
		if ($row['server']>8000){
			continue;
		}
		$total[$row['server']][$row[$param]][$row['valueType']]+=$row['value'];
	}
}else {
	foreach ($res as $row){
		$total[$row[$param]][$row['valueType']]+=$row['value'];
	}
}
$sql="select $param,$sidcolumn valueType,value from $user_pay_table where date between '$startYmd' and '$endYmd' $countrywhere;";
$res = query_bqresult($sql);
if ($allServerFlag){
	foreach ($res as $row){
		if ($row['server']>8000){
			continue;
		}
		$total[$row['server']][$row[$param]][$row['valueType']]+=$row['value'];
	}
}else {
	foreach ($res as $row){
		$total[$row[$param]][$row['valueType']]+=$row['value'];
	}
}
$sql="select $param,$sidcolumn valueType,value from $user_retention_table where date between '$startYmd' and '$endYmd' $countrywhere;";
$res = query_bqresult($sql);
if ($allServerFlag){
	foreach ($res as $row){
		if ($row['server']>8000){
			continue;
		}
		$total[$row['server']][$row[$param]][$row['valueType']]+=$row['value'];
	}
}else {
	foreach ($res as $row){
		$total[$row[$param]][$row['valueType']]+=$row['value'];
	}
}
$sql="select $param,$sidcolumn eventtype, valueType,value from $user_paygold_table where date between '$startYmd' and '$endYmd' $countrywhere;";
$res = query_bqresult($sql);
if ($allServerFlag){
	foreach ($res as $row){
		if ($row['server']>8000){
			continue;
		}
		$total[$row['server']][$row[$param]][$row['eventtype'].'_'.$row['valueType']]+=$row['value'];
	}
}else {
	foreach ($res as $row){
		$total[$row[$param]][$row['eventtype'].'_'.$row['valueType']]+=$row['value'];
	}
}

if($param=='date'){
	unset($titleArray['country']);
	unset($titleArray['pf']);
}elseif ($param=='country'){
	unset($titleArray['date']);
	unset($titleArray['pf']);
}else {
	unset($titleArray['date']);
	unset($titleArray['country']);
}

$serverrows=array();
foreach ($total as $key=>&$dbVal){
	if ($allServerFlag){
		krsort($dbVal);
		$i=0;
		foreach ($dbVal as $serverKey=>$val){
			$i++;
			if ($i==1){
				$serverrows[$key][$serverKey]=2;
			}
			if ($param=='date'){
				$dbVal[$serverKey][$param]=date('Y-m-d',strtotime($serverKey));
			}elseif ($param=='country' && $countrysAdd[$serverKey]){
				$dbVal[$serverKey][$param]=$countrysAdd[$serverKey];
			}elseif ($param=='pf' && $pfList[$serverKey]){
				$dbVal[$serverKey][$param]=$pfList[$serverKey];
			}else {
				$dbVal[$serverKey][$param]=$serverKey;
			}
			$dbVal[$serverKey]['old']=$dbVal[$serverKey]['dau']-$dbVal[$serverKey]['reg']-$dbVal[$serverKey]['replay']-$dbVal[$serverKey]['move'];
			$dbVal[$serverKey]['orreg']=$dbVal[$serverKey]['reg']-$dbVal[$serverKey]['adreg'];
			$dbVal[$serverKey]['payrate']=$dbVal[$serverKey]['dau'] ? intval($dbVal[$serverKey]['payuser']*10000/$dbVal[$serverKey]['dau'] )/100 : 0;
			$dbVal[$serverKey]['regpayrate']=$dbVal[$serverKey]['reg'] ? intval($dbVal[$serverKey]['newregpay']*10000/$dbVal[$serverKey]['reg'] )/100 : 0;
			$dbVal[$serverKey]['ARPU']=$dbVal[$serverKey]['dau'] ? intval($dbVal[$serverKey]['paysum']*100/$dbVal[$serverKey]['dau'] )/100 : 0;
			$dbVal[$serverKey]['COSTGOLDARPU']=$dbVal[$serverKey]['costgold_paygoldcnt'] ? intval($dbVal[$serverKey]['costgold_paygoldsum']*100/$dbVal[$serverKey]['costgold_paygoldcnt'] )/100 : 0;
			$dbVal[$serverKey]['ARPPU']=$dbVal[$serverKey]['payuser'] ? intval($dbVal[$serverKey]['paysum']*100/$dbVal[$serverKey]['payuser'] )/100 : 0;
			foreach ($rateArray as $k=>$v){
				$dbVal[$serverKey][$k]=$dbVal[$serverKey]['reg']>0 ? intval($dbVal[$serverKey][$v] / $dbVal[$serverKey]['reg'] *10000)/100 :0;
			}
			foreach ($titleArray as $tk=>$tv){
				if ($tk==$param){
					continue;
				}
				if (isset($rateArray[$tk]) || $tk=='paysum' || $tk=='payrate' || $tk=='regpayrate' || $tk=='ARPU' || $tk=='COSTGOLDARPU' || $tk=='ARPPU'){
					$dbVal[$serverKey][$tk]=number_format($dbVal[$serverKey][$tk],2);
				}else {
					$dbVal[$serverKey][$tk]=number_format($dbVal[$serverKey][$tk],0);
				}
			}
		}
	}else {
		if ($param=='date'){
			$dbVal[$param]=date('Y-m-d',strtotime($key));
		}elseif ($param=='country' && $countrysAdd[$key]){
			$dbVal[$param]=$countrysAdd[$key];
		}elseif ($param=='pf' && $pfList[$key]){
			$dbVal[$param]=$pfList[$key];
		}else {
			$dbVal[$param]=$key;
		}
		$dbVal['old']=$dbVal['dau']-$dbVal['reg']-$dbVal['replay']-$dbVal['move'];
		$dbVal['orreg']=$dbVal['reg']-$dbVal['adreg'];
		$dbVal['payrate']=intval($dbVal['payuser']*10000/$dbVal['dau'] )/100;
		$dbVal['regpayrate']=intval($dbVal['newregpay']*10000/$dbVal['reg'] )/100;
		$dbVal['ARPU']=intval($dbVal['paysum']*100/$dbVal['dau'] )/100;
		$dbVal['COSTGOLDARPU']=$dbVal['costgold_paygoldcnt'] ? intval($dbVal['costgold_paygoldsum']*100/$dbVal['costgold_paygoldcnt'] )/100 : 0.00;
		$dbVal['ARPPU']=intval($dbVal['paysum']*100/$dbVal['payuser'] )/100;
		foreach ($rateArray as $k=>$v){
			$dbVal[$k]=$dbVal['reg']>0? intval($dbVal[$v] / $dbVal['reg'] *10000)/100 :0;;
		}
		foreach ($titleArray as $tk=>$tv){
			if ($tk==$param){
				continue;
			}
			if (isset($rateArray[$tk]) || $tk=='paysum' || $tk=='payrate' || $tk=='regpayrate' || $tk=='ARPU' || $tk=='COSTGOLDARPU' || $tk=='ARPPU'){
				$dbVal[$tk]=number_format($dbVal[$tk],2);
			}else {
				$dbVal[$tk]=number_format($dbVal[$tk],0);
			}
		}
	}
}
if ($param=='date'){
	krsort($total);
}elseif ($param=='pf'){
	$dataTemp=array();
	foreach ($pfList as $cou=>$cv){
		foreach ($total as $cK=>$dbVal){
			if ($cK==$cou){
				$dataTemp[]=$dbVal;
			}
		}
	}
	foreach ($total as $dbK=>$dbVal){
		if (!isset($pfList[$dbK])){
			$dataTemp[]=$dbVal;
		}
	}
	unset($total);
	$total=$dataTemp;
}else {
	$dataTemp=array();
	foreach ($displayCountryArr as $cou){
		foreach ($total as $cK=>$dbVal){
			if ($cK==$cou){
				$dataTemp[]=$dbVal;
			}
		}
	}
	foreach ($total as $dbVal){
		if (!in_array($dbVal[$param],$displayCountryArr)){
			$dataTemp[]=$dbVal;
		}
	}
	unset($total);
	$total=$dataTemp;
}

if ($_REQUEST['event']=='output'){
	$file_name = '日报数据';
	$titleStr='';
	$prefix='';
	foreach ($titleArray as $tk=>$tv){
		$titleStr .=$prefix.$tv;
		$prefix=',';
	}
	if ($allServerFlag){
		$titleStr="服,".$titleStr;
	}
	$title=explode(',', $titleStr);

	// 输出Excel文件头，可把user.csv换成你要的文件名
	header('Content-Type: application/vnd.ms-excel');
	header('Content-Disposition: attachment;filename="'.$file_name.'.csv"');
	header('Cache-Control: max-age=0');
	$fp = fopen('php://output', 'a');
	foreach ($title as $i => $v) {
		// CSV的Excel支持GBK编码，一定要转换，否则乱码
		$head[$i] = iconv('utf-8', 'gbk', $v);
	}
	// 将数据通过fputcsv写到文件句柄
	fputcsv($fp, $head);
	if ($allServerFlag){
		foreach ($total as $serkey=>$dbData){
			foreach ($dbData as $dk=>$dval){
				$outData=array();
				$outData[]=iconv('utf-8', 'gbk', $serkey);
				foreach ($titleArray as $k=>$v){
					$outData[]=iconv('utf-8', 'gbk', $dval[$k]);
				}
				fputcsv($fp, $outData);
			}
		}
	}else {
		foreach ($total as $serkey=>$dbData){
			$outData=array();
			foreach ($titleArray as $k=>$v){
				$outData[]=iconv('utf-8', 'gbk', $dbData[$k]);
			}
			fputcsv($fp, $outData);
		}
	}

	exit();
}

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

include( renderTemplate("{$module}/{$module}_{$action}") );
?>
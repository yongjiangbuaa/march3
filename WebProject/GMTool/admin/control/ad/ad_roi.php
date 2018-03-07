<?php
!defined('IN_ADMIN') && exit('Access Denied');
ini_set('memory_limit', '4096M');

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

$osList=array(
	'ALL'=>'OS: ALL',
	'android'=>'Android',
	'ios'=>'IOS'
);
$dimensionArray=array(
	'日期',
	'OS',
	'国家',
	'渠道',
);
$typeArray=array(
	'ad'=>'广告数据',
	'organic'=>'自然量',
	'all'=>'全部'
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
$sql="select os,case when channelTop in ('Off-Facebook Installs','Facebook Installs') then 'Facebook Installs' when channelTop in ('Google (unknown)','Google admob text&banner','Google admob video','Google admob-vedio','google adwords','Google adwords Search','Google adwords-Display','Google adwords-video','google search','Google Universal App Campaigns','Youtube Installs','Google admob text&banner','Google admob video','Google admob-vedio','admob-vedio','Google adwords-video','Google adwords-vedio','Admob','Admob-Wezonet','ga admob kr','Google-DBM','Google-Search-AD') then 'Google Installs' when channelTop in ('ApplovinAndroid','ApploviniOS') then 'Applovin' when channelTop in ('Septeni_JP_And','Septeni_JP_iOS') then 'Septeni_JP' else channelTop end as channelTop1 from ad_install_data_without_channelsecond group by os,channelTop1 order by channelTop1;";
$res = query_bqresult($sql);
$osChannel=array();
foreach ($res as $row){
	$osChannel[$row['os']][$row['channelTop1']]=$row['channelTop1'];
	if (!isset($osChannel['ALL'][$row['channelTop1']])){
		$osChannel['ALL'][$row['channelTop1']]=$row['channelTop1'];
	}
};
$titleArray=array(
	'date'=>'日期',
	'install'=>'安装量',
	'cost'=>'花费',
	'cpi'=>'cpi',
	'pay1'=>'1日充值',
	'roi1'=>'1日roi(%)',
	'pay3'=>'3日充值',
	'roi3'=>'3日roi(%)',
	'pay7'=>'7日充值',
	'roi7'=>'7日roi(%)',
	'pay14'=>'14日充值',
	'roi14'=>'14日roi(%)',
	'pay30'=>'30日充值',
	'roi30'=>'30日roi(%)',
	'remain1'=>'次日登陆',
	'rate1'=>'留存(%)',
	'remain3'=>'3日登陆',
	'rate3'=>'留存(%)',
	'remain7'=>'7日登陆',
	'rate7'=>'留存(%)',
);
$titleIndex=array(
	'date'=>0,
	'install'=>1,
	'cost'=>2,
	'cpi'=>3,
	'pay1'=>4,
	'roi1'=>5,
	'pay3'=>6,
	'roi3'=>7,
	'pay7'=>8,
	'roi7'=>9,
	'pay14'=>10,
	'roi14'=>11,
	'pay30'=>12,
	'roi30'=>13,
);
$columncolors=array(
	'roi1'=>array(1,3),
	'roi3'=>array(3,5),
	'roi7'=>array(5,10),
	'roi14'=>array(10,20),
	'roi30'=>array(20,40),
);
$numArray=array();
for ($n=1;$n<=14;$n++){
	$numArray[]=$n;
}
$columnIndex=array();
for ($ci=0;$ci<=13;$ci++){
	$columnIndex[$ci]=$ci+1;
}
$dbCol=array(
	'install',
	'cost',
	'pay1',
	'pay3',
	'pay7',
	'pay14',
	'pay30',
	'remain1',
	'remain3',
	'remain7',
);
$colStr='';
$split='';
foreach ($dbCol as $col){
	$colStr.=$split."sum($col) as $col";
	$split=',';
}
// $organicFlag=false;
// if($_REQUEST['includeOrganic']){
// 	$organicFlag =true;
// }

if ($_REQUEST['type']=='modify'){
	$channel=$_REQUEST['channelKey'];
	$responsibleOriginal=$_REQUEST['responsibleKey'];
	$responsibleNew=trim($_REQUEST['paramValue']);
	$time=date('Y-m-d H:i:s');
	if ($responsibleOriginal=='none'){
		$sql="insert into ad_responsible(channel,responsible,lastmodifytime,lastmodifypeople) values('$channel','$responsibleNew','$time','".$_COOKIE['u']."');";
	}else {
		$sql="update ad_responsible set responsible='$responsibleNew',lastmodifytime='$time',lastmodifypeople='".$_COOKIE['u']."' where channel='$channel' and responsible='$responsibleOriginal';";
	}
	$res = query_bqresult($sql);
	exit($sql);
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
if (!$_REQUEST['eventtype']){
	$currtype='all';
}else {
	$currtype=$_REQUEST['eventtype'];
}

$currdimension=$_REQUEST['dimension'];
$whereSql=" where date between '$startYmd' and '$endYmd' ";
$oswhere='';
if($curOs && $curOs!='ALL'){
	$oswhere =" and os='$curOs' ";
}
$countrywhere='';
$ggcountrywhere='';
if ($currCountry && $currCountry!="ALL"){
	$countrywhere = " and country ='$currCountry' ";
	$ggcountrywhere =" and abbr ='$currCountry' ";
}

$channeltopwhere='';
$adjustggwhere='';
$ggwhere='';
if ($curTopChannel && $curTopChannel!='ALL' && $curTopChannel!='Facebook Installs' && $curTopChannel!='Google Installs' && $curTopChannel!="Applovin" && $curTopChannel!='Septeni_JP'){
	$channeltopwhere =" and channelTop='$curTopChannel' ";
}else if($curTopChannel=='Facebook Installs'){
	$channeltopwhere =" and channelTop in('Facebook Installs','Off-Facebook Installs') ";
}else if ($curTopChannel=='Google Installs'){
	$channeltopwhere = " and channelTop in('Google (unknown)','Google admob text&banner','Google admob video','Google admob-vedio','google adwords','Google adwords Search','Google adwords-Display','Google adwords-video','google search','Google Universal App Campaigns','Youtube Installs','Google admob text&banner','Google admob video','Google admob-vedio','admob-vedio','Google adwords-video','Google adwords-vedio','Admob','Admob-Wezonet','ga admob kr','Google-DBM','Google-Search-AD') ";
	$adjustggwhere = " and channelTop in('Google (unknown)','Google admob text&banner','Google admob video','Google admob-vedio','google adwords','Google adwords Search','Google adwords-Display','Google adwords-video','google search','Google Universal App Campaigns','Youtube Installs','Google admob text&banner','Google admob video','Google admob-vedio','admob-vedio','Google adwords-video','Google adwords-vedio','Admob','Admob-Wezonet','ga admob kr','Google-DBM','Google-Search-AD') ";
	$ggwhere = " and FirstLevelType!='Septeni_JP' ";
}elseif ($curTopChannel=='Septeni_JP'){
	if ($curOs=='android'){
		$adjustggwhere = " and channelTop = 'Septeni_JP_And' ";
		$channeltopwhere = " and channelTop = 'Septeni_JP_And' ";
	}elseif ($curOs=='ios'){
		$adjustggwhere = " and channelTop = 'Septeni_JP_iOS' ";
		$channeltopwhere = " and channelTop = 'Septeni_JP_iOS' ";
	}else {
		$adjustggwhere = " and channelTop in('Septeni_JP_And','Septeni_JP_iOS') ";
		$channeltopwhere = " and channelTop in('Septeni_JP_And','Septeni_JP_iOS') ";
	}
	$ggwhere = " and FirstLevelType='Septeni_JP' ";
}elseif ($curTopChannel=="Applovin"){
	$channeltopwhere = " and channelTop in ('ApplovinAndroid','ApploviniOS') ";
}elseif ($curTopChannel=='ALL'){
	if ($curOs=='android'){
		$adjustggwhere = " and channelTop in('Septeni_JP_And','Google (unknown)','Google admob text&banner','Google admob video','Google admob-vedio','google adwords','Google adwords Search','Google adwords-Display','Google adwords-video','google search','Google Universal App Campaigns','Youtube Installs','Google admob text&banner','Google admob video','Google admob-vedio','admob-vedio','Google adwords-video','Google adwords-vedio','Admob','Admob-Wezonet','ga admob kr','Google-DBM','Google-Search-AD') ";
	}elseif ($curOs=='ios'){
		$adjustggwhere = " and channelTop in('Septeni_JP_iOS','Google (unknown)','Google admob text&banner','Google admob video','Google admob-vedio','google adwords','Google adwords Search','Google adwords-Display','Google adwords-video','google search','Google Universal App Campaigns','Youtube Installs','Google admob text&banner','Google admob video','Google admob-vedio','admob-vedio','Google adwords-video','Google adwords-vedio','Admob','Admob-Wezonet','ga admob kr','Google-DBM','Google-Search-AD') ";
	}else {
		$adjustggwhere = " and channelTop in('Septeni_JP_And','Septeni_JP_iOS','Google (unknown)','Google admob text&banner','Google admob video','Google admob-vedio','google adwords','Google adwords Search','Google adwords-Display','Google adwords-video','google search','Google Universal App Campaigns','Youtube Installs','Google admob text&banner','Google admob video','Google admob-vedio','admob-vedio','Google adwords-video','Google adwords-vedio','Admob','Admob-Wezonet','ga admob kr','Google-DBM','Google-Search-AD') ";
	}
}
$applovinwhere='';
$adjustapplwhere="";
if ($curOs=='android'){
	$applovinwhere = " and os='android' ";
	$adjustapplwhere=" and channelTop='ApplovinAndroid' ";
}elseif ($curOs=='ios'){
	$applovinwhere = " and os='ios'";
	$adjustapplwhere=" and channelTop='ApploviniOS' ";
}else {
	$applovinwhere = " and os in('android','ios')";
	$adjustapplwhere=" and channelTop in('ApplovinAndroid','ApploviniOS') ";
}

$fbFlag=false;
if($curTopChannel=='ALL' || $curTopChannel=='Facebook Installs'){
	$fbFlag=true;
}

$ggFlag=false;
if($curTopChannel=='ALL' || $curTopChannel=='Google Installs' || $curTopChannel=='Septeni_JP'){
	$ggFlag=true;
}

$applovinFlag=false;
if ($curTopChannel=='ALL' || $curTopChannel=='Applovin'){
	$applovinFlag=true;
}

// if (!$organicFlag){
// 	$whereSql .= "and channelTop!='Organic' ";
// }
if ($currtype=='ad'){
	$whereSql .= "and channelTop!='Organic' ";
}elseif ($currtype=='organic'){
	$whereSql .= "and channelTop='Organic' ";
}

// $whereSql .= " and channelTop not in ('Facebook Installs','Off-Facebook Installs','Google Universal App Campaigns','Google-Search-AD','Google (unknown)','Admob','Admob-Wezonet','ga admob kr','Google-DBM','Youtube Installs') ";
if ($currdimension==0){
	$sql="select date,$colStr from ad_install_data_without_channelsecond $whereSql $oswhere $countrywhere $channeltopwhere group by date;";
	$adjustfb="select date,sum(cost) cost from ad_install_data_without_channelsecond where date between '$startYmd' and '$endYmd' $oswhere $countrywhere and channelTop in('Facebook Installs','Off-Facebook Installs') group by date;";

	$fbsql="select date,sum(spend) as cost from fb_ad_result_country where date between '$startYmd' and '$endYmd' $oswhere $countrywhere group by date;";

	$adjustgg="select date,sum(cost) cost from ad_install_data_without_channelsecond where date between '$startYmd' and '$endYmd' $oswhere $countrywhere $adjustggwhere  group by date;";
	$ggsql="select reportDate as date,sum(Cost) as cost from gg_ad_data_new where reportDate between '$startYmd' and '$endYmd' $oswhere $ggcountrywhere $ggwhere group by date;";

	$adjustappsql="select date,sum(cost) cost from ad_install_data_without_channelsecond where date between '$startYmd' and '$endYmd' $oswhere $countrywhere $adjustapplwhere group by date;";
	$appsql="select dt as date,sum(Cost) cost from ad_applovin where dt between '$startYmd' and '$endYmd' $oswhere $countrywhere $applovinwhere group by dt;";
	$param='date';
}elseif ($currdimension==1){
	$sql="select os,$colStr from ad_install_data_without_channelsecond $whereSql $oswhere $countrywhere $channeltopwhere group by os;";
	$adjustfb="select os,sum(cost) cost from ad_install_data_without_channelsecond where date between '$startYmd' and '$endYmd' $oswhere $countrywhere and channelTop in('Facebook Installs','Off-Facebook Installs') group by os;";

	$fbsql="select os,sum(spend) as cost from fb_ad_result_country where date between '$startYmd' and '$endYmd' $oswhere $countrywhere group by os;";

	$adjustgg="select os,sum(cost) cost from ad_install_data_without_channelsecond where date between '$startYmd' and '$endYmd' $oswhere $countrywhere $adjustggwhere group by os;";
	$ggsql="select os,sum(Cost) as cost from gg_ad_data_new where reportDate between '$startYmd' and '$endYmd' $oswhere $ggcountrywhere $ggwhere group by os;";

	$adjustappsql="select os,sum(cost) cost from ad_install_data_without_channelsecond where date between '$startYmd' and '$endYmd' $oswhere $countrywhere $adjustapplwhere group by os;";
	$appsql="select os,sum(Cost) cost from ad_applovin where dt between '$startYmd' and '$endYmd' $oswhere $countrywhere $applovinwhere group by os;";
	$param='os';
	$titleArray['date']='系统';
}elseif ($currdimension==2){
	$sql="select country,$colStr from ad_install_data_without_channelsecond $whereSql $oswhere $countrywhere $channeltopwhere group by country;";
	$adjustfb="select country,sum(cost) cost from ad_install_data_without_channelsecond where date between '$startYmd' and '$endYmd' $oswhere $countrywhere and channelTop in('Facebook Installs','Off-Facebook Installs') group by country;";

	$fbsql="select country,sum(spend) as cost from fb_ad_result_country where date between '$startYmd' and '$endYmd' $oswhere $countrywhere group by country;";

	$adjustgg="select country,sum(cost) cost from ad_install_data_without_channelsecond where date between '$startYmd' and '$endYmd' $oswhere $countrywhere $adjustggwhere group by country;";
	$ggsql="select abbr as country,sum(Cost) as cost from gg_ad_data_new where reportDate between '$startYmd' and '$endYmd' $oswhere $ggcountrywhere $ggwhere group by country;";

	$adjustappsql="select country,sum(cost) cost from ad_install_data_without_channelsecond where date between '$startYmd' and '$endYmd' $oswhere $countrywhere $adjustapplwhere group by country;";
	$appsql="select country,sum(Cost) cost from ad_applovin where dt between '$startYmd' and '$endYmd' $oswhere $countrywhere $applovinwhere group by country;";
	$param='country';
	$titleArray['date']='国家';
}else{
	$sql="select case when channelTop in ('Off-Facebook Installs','Facebook Installs') then 'Facebook Installs' when channelTop in ('Google (unknown)','Google admob text&banner','Google admob video','Google admob-vedio','google adwords','Google adwords Search','Google adwords-Display','Google adwords-video','google search','Google Universal App Campaigns','Youtube Installs','Google admob text&banner','Google admob video','Google admob-vedio','admob-vedio','Google adwords-video','Google adwords-vedio','Admob','Admob-Wezonet','ga admob kr','Google-DBM','Google-Search-AD') then 'Google Installs' when channelTop in('ApplovinAndroid','ApploviniOS') then 'Applovin' when channelTop in ('Septeni_JP_And','Septeni_JP_iOS') then 'Septeni_JP' else channelTop end as channelTop1,$colStr from ad_install_data_without_channelsecond $whereSql $oswhere $countrywhere $channeltopwhere group by channelTop1;";
	$adjustfb="select case when channelTop='Off-Facebook Installs' then 'Facebook Installs' when channelTop!='Off-Facebook Installs' then channelTop end as channelTop1,sum(cost) cost from ad_install_data_without_channelsecond where date between '$startYmd' and '$endYmd' $oswhere $countrywhere and channelTop in('Facebook Installs','Off-Facebook Installs') group by channelTop1;";

	$fbsql="select 'Facebook Installs' as channelTop1,sum(spend) as cost from fb_ad_result_country where date between '$startYmd' and '$endYmd' $oswhere $countrywhere group by channelTop1;";

	$adjustgg="select case when channelTop in ('Google (unknown)','Google admob text&banner','Google admob video','Google admob-vedio','google adwords','Google adwords Search','Google adwords-Display','Google adwords-video','google search','Google Universal App Campaigns','Youtube Installs','Google admob text&banner','Google admob video','Google admob-vedio','admob-vedio','Google adwords-video','Google adwords-vedio','Admob','Admob-Wezonet','ga admob kr','Google-DBM','Google-Search-AD') then 'Google Installs' when channelTop in ('Septeni_JP_And','Septeni_JP_iOS') then 'Septeni_JP' else channelTop end as channelTop1,sum(cost) cost from ad_install_data_without_channelsecond where date between '$startYmd' and '$endYmd' $oswhere $countrywhere $adjustggwhere group by channelTop1;";
// 	$ggsql="select case when FirstLevelType in('Search Network','Display Network','YouTube Videos') then 'Google Installs' end as channelTop1,sum(Cost) as cost from gg_ad_data_new where reportDate between '$startYmd' and '$endYmd' $oswhere $ggcountrywhere group by channelTop1;";
	$ggsql="select case when FirstLevelType='Septeni_JP' then FirstLevelType else 'Google Installs' end as channelTop1,sum(Cost) as cost from gg_ad_data_new where reportDate between '$startYmd' and '$endYmd' $oswhere $ggcountrywhere $ggwhere group by channelTop1;";

	$adjustappsql="select case when channelTop in ('ApplovinAndroid','ApploviniOS') then 'Applovin' else channelTop end as channelTop1,sum(cost) cost from ad_install_data_without_channelsecond where date between '$startYmd' and '$endYmd' $oswhere $countrywhere $adjustapplwhere group by channelTop1;";
	$appsql="select 'Applovin' as channelTop1,sum(Cost) cost from ad_applovin where dt between '$startYmd' and '$endYmd' $oswhere $countrywhere $applovinwhere group by channelTop1;";
	$param='channelTop1';
	$titleArray['date']='渠道';
}

if ($param=='channelTop1'){
	$responsibles=array();
	foreach ($osChannel['ALL'] as $channelVal){
		$responsibles[$channelVal]='none';
	}
	$respsql="select channel,responsible from ad_responsible;";
	$respres=query_bqresult($respsql);
	foreach ($respres as $resprow){
		$responsibles[$resprow['channel']]=$resprow['responsible'];
	}
	$titleArray=array_merge(array('responsible'=>'负责人'),$titleArray);
}

if ($currtype!='organic'){
	// 	获取facebook数据
	$fbCost=array();
	$adjustfbCost=array();
	$fbc=0;
	$adfbc=0;
	if ($fbFlag){
		$fbres = query_bqresult($fbsql);
		foreach ($fbres as $fbrow){
			$fbCost[strtolower($fbrow[$param])]=$fbrow['cost'];
			$fbc+=$fbrow['cost'];
		}
		$adjustres=query_bqresult($adjustfb);
		foreach ($adjustres as $adjustrow){
			$adjustfbCost[strtolower($adjustrow[$param])]=$adjustrow['cost'];
			$adfbc+=$adjustrow['cost'];
		}
	}
	// 获取Google数据
	$ggCost=array();
	$adjustggCost=array();
	$ggc=0;
	$adggc=0;
	if ($ggFlag){
		$ggres = query_bqresult($ggsql);
		foreach ($ggres as $ggrow){
			$ggCost[strtolower($ggrow[$param])]=$ggrow['cost'];
			$ggc+=$ggrow['cost'];
		}
		$adjustggres = query_bqresult($adjustgg);
		foreach ($adjustggres as $adjustggrow){
			$adjustggCost[strtolower($adjustggrow[$param])]=$adjustggrow['cost'];
			$adggc+=$adjustggrow['cost'];
		}
	}
	//获取Applovin数据
	$applCost=array();
	$adjustapplCost=array();
	$appc=0;
	$adappc=0;
	if ($applovinFlag){
		$appres = query_bqresult($appsql);
		foreach ($appres as $approw){
			$applCost[strtolower($approw[$param])]=$approw['cost'];
			$appc+=$approw['cost'];
		}
		$adjustappres = query_bqresult($adjustappsql);
		foreach ($adjustappres as $adjustapprow){
			$adjustapplCost[strtolower($adjustapprow[$param])]=$adjustapprow['cost'];
			$adappc+=$adjustapprow['cost'];
		}
	}
}
if ($_COOKIE['u']=='yd'){
	// 	echo $sql."\n";
	// 	echo $ggsql;
	// 	echo $adjustappsql;
	echo $adjustgg."\n";
	echo $ggsql."\n";
// 	print_r($applCost);
}

$res = query_bqresult($sql);
$data=array();
$total=array();
$sumcost=0;
$oncost=0;
foreach ($res as $row){
	$one=array();
	$one[$param]=$row[$param];
	if ($param=='date'){
		$temp=date('Ymd',strtotime($row['date']));
	}else {
		$temp=$row[$param];
	}
	$sumcost+=$row['cost'];
	foreach ($dbCol as $col){
		if ($col=='cost' && $fbCost[strtolower($row[$param])] && $ggCost[strtolower($row[$param])]){
			$one[$col]=$row[$col]+$fbCost[strtolower($row[$param])]-$adjustfbCost[strtolower($row[$param])]+$ggCost[strtolower($row[$param])]-$adjustggCost[strtolower($row[$param])];
// 			$total[$col]+=$row[$col]+$fbCost[strtolower($row[$param])]-$adjustfbCost[strtolower($row[$param])]+$ggCost[strtolower($row[$param])]-$adjustggCost[strtolower($row[$param])];
		}elseif ($col=='cost' && $fbCost[strtolower($row[$param])]){
			$one[$col]=$row[$col]+$fbCost[strtolower($row[$param])]-$adjustfbCost[strtolower($row[$param])];
// 			$total[$col]+=$row[$col]+$fbCost[strtolower($row[$param])]-$adjustfbCost[strtolower($row[$param])];
		}elseif ($col=='cost' && $ggCost[strtolower($row[$param])]){
			$one[$col]=$row[$col]+$ggCost[strtolower($row[$param])]-$adjustggCost[strtolower($row[$param])];
// 			$total[$col]+=$row[$col]+$ggCost[strtolower($row[$param])]-$adjustggCost[strtolower($row[$param])];
		}elseif ($col=='cost' && $applCost[strtolower($row[$param])]){
			$one[$col]=$row[$col]+$applCost[strtolower($row[$param])]-$adjustapplCost[strtolower($row[$param])];
		}else {
			$one[$col]=$row[$col];
			$total[$col]+=$row[$col];
		}
	}
	$oncost+=$one['cost'];
	$one['cpi']=number_format($one['cost']/$one['install'],2);
	$one['roi1']=$one['cost']>0? number_format($one['pay1'] / $one['cost'] *100, 2) :0;
	$one['roi3']=$one['cost']>0? number_format($one['pay3'] / $one['cost'] *100, 2) :0;
	$one['roi7']=$one['cost']>0? number_format($one['pay7'] / $one['cost'] *100, 2) :0;
	$one['roi14']=$one['cost']>0? number_format($one['pay14'] / $one['cost'] *100, 2) :0;
	$one['roi30']=$one['cost']>0? number_format($one['pay30'] / $one['cost'] *100, 2) :0;
	$one['rate1']=$one['install']>0? number_format($one['remain1'] / $one['install'] *100, 2) :0;
	$one['rate3']=$one['install']>0? number_format($one['remain3'] / $one['install'] *100, 2) :0;
	$one['rate7']=$one['install']>0? number_format($one['remain7'] / $one['install'] *100, 2) :0;
	$data[$temp]=$one;
}
// echo ($ggc-$adggc).",".$sumcost.",".$oncost."\n";
$total['cost']=$sumcost+($ggc-$adggc)+($fbc-$adfbc)+($appc-$adappc);
$total['cpi']=number_format($total['cost']/$total['install'],2);
$total['roi1']=$total['cost']>0? number_format($total['pay1'] / $total['cost'] *100, 2) :0;
$total['roi3']=$total['cost']>0? number_format($total['pay3'] / $total['cost'] *100, 2) :0;
$total['roi7']=$total['cost']>0? number_format($total['pay7'] / $total['cost'] *100, 2) :0;
$total['roi14']=$total['cost']>0? number_format($total['pay14'] / $total['cost'] *100, 2) :0;
$total['roi30']=$total['cost']>0? number_format($total['pay30'] / $total['cost'] *100, 2) :0;
$total['rate1']=$total['install']>0? number_format($total['remain1'] / $total['install'] *100, 2) :0;
$total['rate3']=$total['install']>0? number_format($total['remain3'] / $total['install'] *100, 2) :0;
$total['rate7']=$total['install']>0? number_format($total['remain7'] / $total['install'] *100, 2) :0;
foreach ($dbCol as $col){
	if ($col=="install" || $col='remain1' || $col='remain3' || $col='remain7'){
		$total[$col]=number_format($total[$col],0);
	}else {
		$total[$col]=number_format($total[$col],2);
	}
}
foreach ($data as $k=>$v){
	foreach ($dbCol as $col){
		if ($col=="install" || $col='remain1' || $col='remain3' || $col='remain7'){
			$data[$k][$col]=$v[$col]?number_format($v[$col],0) :0;
		}else {
			$data[$k][$col]=$v[$col]?number_format($v[$col],2) :0;
		}
	}
}

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
if ($_REQUEST['event']=='output'){
	$titleStr='';
	$prefix='';
	foreach ($titleArray as $tk=>$tv){
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
		foreach ($titleArray as $tk=>$tv){
// 			if ($tk=='date' && $param=='country' && $countryList[$dbData[$param]]){
// 				$Excel->setCellValue(getNameFromNumber($i).''.$row, $countryList[$dbData[$param]]);
// 			}else
			if ($tk=='responsible'){
				$Excel->setCellValue(getNameFromNumber($i).''.$row, $responsibles[$dbData[$param]]);
			}elseif ($tk=='date'){
				if ($param=='country' && $countrysAdd[$dbData[$param]]){
					$Excel->setCellValue(getNameFromNumber($i).''.$row, $countrysAdd[$dbData[$param]]);
				}else {
					$Excel->setCellValue(getNameFromNumber($i).''.$row, $dbData[$param]);
				}
			}else {
				$Excel->setCellValue(getNameFromNumber($i).''.$row, $dbData[$tk]);
			}
			$i++;
		}
		$row++;
	}
	//filename
	$file_name = 'ROI';
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
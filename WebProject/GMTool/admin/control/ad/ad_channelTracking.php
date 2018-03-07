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
if (!$_REQUEST['channelSecond']){
	$channelSecond='ALL';
}else {
	$channelSecond=$_REQUEST['channelSecond'];
}
$osList=array(
	'ALL'=>'ALL',
	'android'=>'Android',
	'ios'=>'IOS'
);
$timeArray=array(
		"none"=>'None',
		"day"=>'Day'
);
$dimensionArray=array(
		'os',
		'国家',
		'一级渠道',
		'二级渠道'
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
$showData=false;
$alertHead='';
$sql="select country,channelTop from ad_install_data_without_channelsecond group by country,channelTop;";
$res = query_bqresult($sql);
$countryChannel=array();
foreach ($res as $row){
	if (isset($countryList[$row['country']])){
		$countryChannel[$row['country']][]=$row['channelTop'];
	}
};

$titleArray=array(
	'date'=>'日期',
// 	'channelSecond'=>'二级渠道',
	'install'=>'安装',
	'cost'=>'花费',
	'cpi'=>'cpi',
	'pay1'=>'1日充值',
	'roi1'=>'1日roi(%)',
	'pay3'=>'3日充值',
	'roi3'=>'3日roi(%)',
	'pay7'=>'7日充值',
	'roi7'=>'7日roi(%)',
	'remain1'=>'1日登录',
	'rate1'=>'1日留存率(%)',
	'remain3'=>'3日登录',
	'rate3'=>'3日留存率(%)',
	'remain7'=>'7日登录',
	'rate7'=>'7日留存率(%)',
);

$dbCol=array(
		'install',
		'cost',
		'pay1',
		'pay3',
		'pay7',
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
	if (!$_REQUEST['topChannel_'.$currCountry]){
		$curTopChannel='ALL';
	}else {
		$curTopChannel=$_REQUEST['topChannel_'.$currCountry];
	}
	if (!$_REQUEST['channelSecond']){
		$curChannelSecond='ALL';
	}else {
		$curChannelSecond=$_REQUEST['channelSecond'];
	}
	if (!$_REQUEST['timeValue']){
		$currentTime='none';
	}else {
		$currentTime=$_REQUEST['timeValue'];
	}
	$currdimension=$_REQUEST['dimension'];
	$whereSql=" where date between '$startYmd' and '$endYmd' ";
	if($curOs && $curOs!='ALL'){
		$whereSql.=" and os='$curOs' ";
	}
	if ($currCountry && $currCountry!="ALL"){
		$whereSql .= " and country ='$currCountry' ";
	}
	if ($curTopChannel && $curTopChannel!='ALL'){
		$whereSql .=" and channelTop='$curTopChannel' ";
	}
	$flag=false;
	
	if ($currentTime=='none'){
		if ($currdimension==0){
			$sql="select os,$colStr from ad_install_data_without_channelsecond $whereSql and channelTop!='Organic' group by os;";
			$param='os';
			$titleArray['date']='系统';
		}elseif ($currdimension==1){
			$sql="select country,$colStr from ad_install_data_without_channelsecond $whereSql and channelTop!='Organic' group by country;";
			$param='country';
			$titleArray['date']='国家';
		}elseif ($currdimension==2){
			$sql="select channelTop,$colStr from ad_install_data_without_channelsecond $whereSql and channelTop!='Organic' group by channelTop;";
			$param='channelTop';
			$titleArray['date']='一级渠道';
		}else {
			$sql="select channelSecond,$colStr from ad_install_data $whereSql and channelTop!='Organic' group by channelSecond;";
			$param='channelSecond';
			$titleArray['date']='二级渠道';
		}
		
	}else {
		$param='date';
		if ($curTopChannel=='ALL'){
			$sql="select date,$colStr from ad_install_data_without_channelsecond $whereSql and channelTop!='Organic' group by date;";
		}else if ($curTopChannel!='ALL' && $curChannelSecond=='ALL'){
// 			if ($currentTime=='none'){
// 				$sql="select channelSecond,$colStr from adInstallData $whereSql group by channelSecond order by install desc;";
// 				$flag=true;
// 			}else {
				$sql="select date,$colStr from ad_install_data $whereSql group by date order by install desc;";
// 			}
		}else if ($curTopChannel!='ALL' && $curChannelSecond!='ALL'){
			$sql="select date,$colStr from ad_install_data $whereSql and channelSecond='$curChannelSecond' group by date order by install desc;";
		}
	}
	
	
	$res = query_bqresult($sql);
	$data=array();
	$total=array();
	foreach ($res as $row){
		$one=array();
// 		if ($flag){
// 			$one['channelSecond']=$row['channelSecond'];
// 			$temp=$row['channelSecond'];
// 		}else {
// 			$one['date']=$row['date'];
// 			$temp=date('Ymd',strtotime($row['date']));
// 		}
		$one[$param]=$row[$param];
		if ($param=='date'){
			$temp=date('Ymd',strtotime($row['date']));
		}else {
			$temp=$row[$param];
		}
		foreach ($dbCol as $col){
			$one[$col]=$row[$col];
			$total[$col]+=$row[$col];
		}
		$one['cpi']=number_format($one['cost']/$one['install'],2);
		$one['roi1']=$one['cost']>0? intval($one['pay1'] / $one['cost'] *10000)/100 :0;
		$one['roi3']=$one['cost']>0? intval($one['pay3'] / $one['cost'] *10000)/100 :0;
		$one['roi7']=$one['cost']>0? intval($one['pay7'] / $one['cost'] *10000)/100 :0;
		$one['rate1']=$one['install']>0? intval($one['remain1'] / $one['install'] *10000)/100 :0;
		$one['rate3']=$one['install']>0? intval($one['remain3'] / $one['install'] *10000)/100 :0;
		$one['rate7']=$one['install']>0? intval($one['remain7'] / $one['install'] *10000)/100 :0;
		$data[$temp]=$one;
	}
	
	$total['cpi']=number_format($total['cost']/$total['install'],2);
	$total['roi1']=$total['cost']>0? intval($total['pay1'] / $total['cost'] *10000)/100 :0;
	$total['roi3']=$total['cost']>0? intval($total['pay3'] / $total['cost'] *10000)/100 :0;
	$total['roi7']=$total['cost']>0? intval($total['pay7'] / $total['cost'] *10000)/100 :0;
	$total['rate1']=$total['install']>0? intval($total['remain1'] / $total['install'] *10000)/100 :0;
	$total['rate3']=$total['install']>0? intval($total['remain3'] / $total['install'] *10000)/100 :0;
	$total['rate7']=$total['install']>0? intval($total['remain7'] / $total['install'] *10000)/100 :0;
	
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
	}
	
// 	if ($flag){
// 		unset($titleArray['date']);
// 	}else {
// 		unset($titleArray['channelSecond']);
// 		krsort($data);
// 	}
	


include( renderTemplate("{$module}/{$module}_{$action}") );
?>
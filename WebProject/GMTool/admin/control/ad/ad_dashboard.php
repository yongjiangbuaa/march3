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
$organicFlag=false;
if($_REQUEST['includeOrganic']){
	$organicFlag =true;
}

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

//数据展示
$startYmd=date('Y-m-d',strtotime($start));
$endYmd=date('Y-m-d',strtotime($end));
$yesterday=date('Y-m-d',strtotime(date("Y-m-d",time()-86400)));
// $yesterday='2016-08-10';
// $twodaysago=date('Y-m-d',strtotime(date("Y-m-d",time()-86400*2)));
$twodaysago=date('Y-m-d',strtotime($yesterday)-86400);
if (!$_REQUEST['selectCountry']){
	$currCountry='ALL';
}else {
	$currCountry=$_REQUEST['selectCountry'];
}

$whereSql=" where dt between '$startYmd' and '$endYmd' ";
$oswhere='';
if($curOs && $curOs!='ALL'){
	$oswhere =" and os='$curOs' ";
}
$countrywhere='';
$table="user_info";
if ($currCountry && $currCountry!="ALL"){
	$countrywhere = " and country ='$currCountry' ";
	$table="user_info_country";
	$costsql="select date,sum(cost) cost from ad_roi_detail where date between '$startYmd' and '$endYmd' and country ='$currCountry' group by date;";
}else {
	$costsql="select date,sum(cost) cost from ad_roi_without_country where date between '$startYmd' and '$endYmd' group by date;";
}

$sql="select dt,sum(value) value ,valuetype from $table where dt in('$yesterday','$twodaysago') $countrywhere group by dt,valuetype;";
$pictureSql="select dt,sum(value) value ,valuetype from $table $whereSql $countrywhere group by dt,valuetype;";

$res = query_bqresult($sql);
$data=array();
$total=array();
foreach ($res as $row){
	$data[$row['dt']][$row['valuetype']]=$row['value'];
}

$yesterdaypay=number_format($data[$yesterday]['pay']);
$yesterdayadpay=number_format($data[$yesterday]['adpay']);
$yesterdayorganicpay=number_format($data[$yesterday]['pay']-$data[$yesterday]['adpay'],0);

$yesterdaydau=number_format($data[$yesterday]['dau']);
$yesterdayaddau=number_format($data[$yesterday]['addau']);
$yesterdayorganicdau=number_format($data[$yesterday]['dau']-$data[$yesterday]['addau'],0);

$yesterdayreg=number_format($data[$yesterday]['reg']);
$yesterdayadreg=number_format($data[$yesterday]['adreg']);
$yesterdayorganicreg=number_format($data[$yesterday]['reg']-$data[$yesterday]['adreg'],0);

$dauRate=$data[$twodaysago]['dau'] ? number_format(($data[$yesterday]['dau']-$data[$twodaysago]['dau'])/$data[$twodaysago]['dau']*100,2,'.',''): 0;
$regRate=$data[$twodaysago]['reg'] ? number_format(($data[$yesterday]['reg']-$data[$twodaysago]['reg'])/$data[$twodaysago]['reg']*100,2,'.',''): 0;
$payRate=$data[$twodaysago]['pay'] ? number_format(($data[$yesterday]['pay']-$data[$twodaysago]['pay'])/$data[$twodaysago]['pay']*100,2,'.',''): 0;
if ($dauRate>0){
	$dauRateStr="<strong><font color='red'>&#8593;".$dauRate."%</font></strong>";
}elseif ($dauRate<0){
	$dauRateStr="<strong><font color='green'>&#8595;".$dauRate."%</font></strong>";
}else {
	$dauRateStr="<strong><font>".$dauRate."%</font></strong>";
}
if ($regRate>0){
	$regRateStr="<strong><font color='red'>&#8593;".$regRate."%</font></strong>";
}elseif ($regRate<0){
	$regRateStr="<strong><font color='green'>&#8595;".$regRate."%</font></strong>";
}else {
	$regRateStr="<strong><font>".$regRate."%</font></strong>";
}
if ($payRate>0){
	$payRateStr="<strong><font color='red'>&#8593;".$payRate."%</font></strong>";
}elseif ($payRate<0){
	$payRateStr="<strong><font color='green'>&#8595;".$payRate."%</font></strong>";
}else {
	$payRateStr="<strong><font>".$payRate."%</font></strong>";
}

$organicDauRate=($data[$twodaysago]['dau']-$data[$twodaysago]['addau']) ? number_format(($data[$yesterday]['dau']-$data[$yesterday]['addau']-($data[$twodaysago]['dau']-$data[$twodaysago]['addau']))/($data[$twodaysago]['dau']-$data[$twodaysago]['addau'])*100,2,'.',''):0;
$adDauRate=$data[$twodaysago]['addau'] ? number_format(($data[$yesterday]['addau']-$data[$twodaysago]['addau'])/$data[$twodaysago]['addau']*100,2,'.',''): 0;
if ($organicDauRate>0){
	$organicDauRateStr="<strong><font color='red'>&#8593;".$organicDauRate."%</font></strong>";
}elseif ($organicDauRate<0){
	$organicDauRateStr="<strong><font color='green'>&#8595;".$organicDauRate."%</font></strong>";
}else {
	$organicDauRateStr="<strong><font>".$organicDauRate."%</font></strong>";
}
if ($adDauRate>0){
	$adDauRateStr="<strong><font color='red'>&#8593;".$adDauRate."%</font></strong>";
}elseif ($adDauRate<0){
	$adDauRateStr="<strong><font color='green'>&#8595;".$adDauRate."%</font></strong>";
}else {
	$adDauRateStr="<strong><font>".$adDauRate."%</font></strong>";
}

$organicRegRate=($data[$twodaysago]['reg']-$data[$twodaysago]['adreg']) ? number_format(($data[$yesterday]['reg']-$data[$yesterday]['adreg']-($data[$twodaysago]['reg']-$data[$twodaysago]['adreg']))/($data[$twodaysago]['reg']-$data[$twodaysago]['adreg'])*100,2,'.',''):0;
$adRegRate=$data[$twodaysago]['adreg'] ? number_format(($data[$yesterday]['adreg']-$data[$twodaysago]['adreg'])/$data[$twodaysago]['adreg']*100,2,'.',''): 0;
if ($organicRegRate>0){
	$organicRegRateStr="<strong><font color='red'>&#8593;".$organicRegRate."%</font></strong>";
}elseif ($organicRegRate<0){
	$organicRegRateStr="<strong><font color='green'>&#8595;".$organicRegRate."%</font></strong>";
}else {
	$organicRegRateStr="<strong><font>".$organicRegRate."%</font></strong>";
}
if ($adRegRate>0){
	$adRegRateStr="<strong><font color='red'>&#8593;".$adRegRate."%</font></strong>";
}elseif ($adRegRate<0){
	$adRegRateStr="<strong><font color='green'>&#8595;".$adRegRate."%</font></strong>";
}else {
	$adRegRateStr="<strong><font>".$adRegRate."%</font></strong>";
}

$organicPayRate=($data[$twodaysago]['pay']-$data[$twodaysago]['adpay']) ? number_format(($data[$yesterday]['pay']-$data[$yesterday]['adpay']-($data[$twodaysago]['pay']-$data[$twodaysago]['adpay']))/($data[$twodaysago]['pay']-$data[$twodaysago]['adpay'])*100,2,'.',''):0;
$adPayRate=$data[$twodaysago]['adpay'] ? number_format(($data[$yesterday]['adpay']-$data[$twodaysago]['adpay'])/$data[$twodaysago]['adpay']*100,2,'.',''): 0;
if ($organicPayRate>0){
	$organicPayRateStr="<strong><font color='red'>&#8593;".$organicPayRate."%</font></strong>";
}elseif ($organicPayRate<0){
	$organicPayRateStr="<strong><font color='green'>&#8595;".$organicPayRate."%</font></strong>";
}else {
	$organicPayRateStr="<strong><font>".$organicPayRate."%</font></strong>";
}
if ($adPayRate>0){
	$adPayRateStr="<strong><font color='red'>&#8593;".$adPayRate."%</font></strong>";
}elseif ($adPayRate<0){
	$adPayRateStr="<strong><font color='green'>&#8595;".$adPayRate."%</font></strong>";
}else {
	$adPayRateStr="<strong><font>".$adPayRate."%</font></strong>";
}

$picres =  query_bqresult($pictureSql);
$picdata=array();
foreach ($picres as $picrow){
	$picdata[date('Ymd',strtotime($picrow['dt']))][$picrow['valuetype']]=$picrow['value'];
}
$costres=query_bqresult($costsql);
foreach ($costres as $costrow){
	$picdata[date('Ymd',strtotime($costrow['date']))]['cost']=$costrow['cost'];
}

ksort($picdata);
$datestr='['.implode(',', array_keys($picdata)).']';

$curves=array();
foreach ($picdata as $datekey=>$dbdata){
	$dbdata['oganicdau']=$dbdata['dau']-$dbdata['addau'];
	$dbdata['oganicreg']=$dbdata['reg']-$dbdata['adreg'];
	$dbdata['oganicpay']=$dbdata['pay']-$dbdata['adpay'];
	$dbdata['rate1']=$dbdata['reg'] ? number_format($dbdata['retention1']/$dbdata['reg']*100,2,'.',''):0;
	$dbdata['rate3']=$dbdata['reg'] ? number_format($dbdata['retention3']/$dbdata['reg']*100,2,'.',''):0;
	$dbdata['rate7']=$dbdata['reg'] ? number_format($dbdata['retention7']/$dbdata['reg']*100,2,'.',''):0;
	$dbdata['adrate1']=$dbdata['adreg'] ? number_format($dbdata['adretention1']/$dbdata['adreg']*100,2,'.',''):0;
	$dbdata['adrate3']=$dbdata['adreg'] ? number_format($dbdata['adretention3']/$dbdata['adreg']*100,2,'.',''):0;
	$dbdata['adrate7']=$dbdata['adreg'] ? number_format($dbdata['adretention7']/$dbdata['adreg']*100,2,'.',''):0;
	foreach ($dbdata as $typekey=>$val){
		$curves[$typekey][$datekey]=$val;
	}
}
$showchart=array();
foreach ($curves as $tk=>$dval){
	$showchart[$tk]='['.implode(',', $dval).']';
}

if ($organicFlag){
	//包含organic
	$indexArr=array(
		'reg'=>'Install',
		'pay'=>'Revenue',
		'cost'=>'Cost',
	);
	$coordinates=array(
		'reg'=>0,
		'pay'=>1,
		'cost'=>1,
	);
	$curvesIndex=array(
		'rate1'=>'1日留存',
		'rate3'=>'3日留存',
		'rate7'=>'7日留存',
	);
}else {
	$indexArr=array(
		'adreg'=>'Install',
		'adpay'=>'Revenue',
		'cost'=>'Cost',
	);
	$coordinates=array(
		'adreg'=>0,
		'adpay'=>1,
		'cost'=>1,
	);
	$curvesIndex=array(
		'adrate1'=>'1日留存',
		'adrate3'=>'3日留存',
		'adrate7'=>'7日留存',
	);
}






include( renderTemplate("{$module}/{$module}_{$action}") );
?>
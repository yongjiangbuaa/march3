<?php
!defined('IN_ADMIN') && exit('Access Denied');

$html='';
$showData=false;

if (!$_REQUEST['selectCountry']) {
	$currCountry[] = 'ALL';
}else{
	$currCountry = $_REQUEST['selectCountry'];
}
if (!$_REQUEST['selectPf']) {
	$currPf[] = 'ALL';
}else{
	$currPf = $_REQUEST['selectPf'];
}

$alertHead='';
$titleArray=array(
	'os'=>'操作系统',
	'country'=>'国家',
	'firstchannel'=>'一级渠道',
	'secondchannel'=>'二级渠道',
	'cpa'=>'cpa',
	'lastmodifytime'=>'最后修改时间',
	'lastmodifypeople'=>'最后操作人'
);
$columns=array(
	'os',
	'country',
	'firstchannel',
	'secondchannel',
	'cpa',
	'lastmodifytime',
	'lastmodifypeople'
);

$link = mysqli_connect(new_AD_DB_SERVER_IP,new_AD_DB_SERVER_USER,new_AD_DB_SERVER_PWD,new_AD_DB_SERVER_DATABASE,3306);
if(!$link){
	exit('connect db error'.new_AD_DB_SERVER_IP.new_AD_DB_SERVER_USER.'----'.$link);
}

if ($_REQUEST['type']=='modify'){
	$id=$_REQUEST['uuid'];
	$param=$_REQUEST['param'];
	$paramValue=trim($_REQUEST['paramValue']);
	$originalVal=$_REQUEST['originalVal'];
	$time=date('Y-m-d H:i:s');
	$sql="update CPA set $param='$paramValue',lastmodifytime='$time',lastmodifypeople='".$_COOKIE['u']."' where id='$id';";
	$res = mysqli_query($link,$sql);
	$modifyValue="originalVal:$originalVal,update:$param=$paramValue,conditions:id=$id";
	$logSql="insert into cpa_log(editor,type,edittime,param) value('".$_COOKIE['u']."','modify','$time','$modifyValue')";
	$res = mysqli_query($link,$logSql);
	exit();
}
if ($_REQUEST['type']=='delete'){
	$id=$_REQUEST['uuid'];
	$searchSql="select os,firstchannel,secondchannel,country,cpa from CPA where id='$id';";
	$res = mysqli_query($link,$searchSql);
	$val='';
	while ($row = mysqli_fetch_assoc($res)){
		$val.="os=".$row['os'].",firstchannel=".$row['firstchannel'].",secondchannel=".$row['secondchannel'].",country=".$row['country'].",cpa=".$row['cpa'];
	}
	$deleteVal="delete: $val,conditions:id=$id";
	$time=date('Y-m-d H:i:s');
	$logSql="insert into cpa_log(editor,type,edittime,param) value('".$_COOKIE['u']."','delete','$time','$deleteVal')";
	$res = mysqli_query($link,$logSql);
	$sql="delete from CPA where id='$id' limit 1;";
	$res = mysqli_query($link,$sql);
	exit();
}
if ($_REQUEST['type']=='add'){
	$os=$_REQUEST['os'];
	$country=$_REQUEST['country'];
	$fc=$_REQUEST['fc'];
	$sc=$_REQUEST['sc'];
	$cpa=$_REQUEST['cpa'];
	$time=date('Y-m-d H:i:s');
	$sql="insert into CPA(os,firstchannel,secondchannel,country,cpa,lastmodifytime,lastmodifypeople) value('$os','$fc','$sc','$country','$cpa','$time','".$_COOKIE['u']."');";
	$res = mysqli_query($link,$sql);
	$addValue="os=$os,firstchannel=$fc,secondchannel=$sc,country=$country,cpa=$cpa";
	$logSql="insert into cpa_log(editor,type,edittime,param) value('".$_COOKIE['u']."','add','$time','$addValue')";
	$res = mysqli_query($link,$logSql);
}

$whereSql='';
if($currCountry&&(!in_array('ALL', $currCountry))){
	$countries=implode("','", $currCountry);
	$whereSql .=" and country in('$countries') ";
}
if ($currPf && !in_array('ALL', $currPf)){
	$fcs=implode("','", $currPf);
	$whereSql .=" and firstchannel in ('$fcs') ";
}

$sql="select id,os,country,firstchannel,secondchannel,cpa,lastmodifytime,lastmodifypeople from CPA where 1=1 $whereSql;";
$res = mysqli_query($link,$sql);
$data=array();
$countryArray=array();
$fcArray=array();
echo print_r($res,true).PHP_EOL.'ceshi';
while ($row = mysqli_fetch_assoc($res)){
	$one=array();
	$one['id']=$row['id'];
	foreach ($columns as $column){
		$one[$column]=$row[$column];
	}
	$data[]=$one;
	if (!in_array($row['country'], $countryArray)){
		$countryArray[]=$row['country'];
	}
	if (!in_array($row['firstchannel'], $fcArray)){
		$fcArray[]=$row['firstchannel'];
	}
}
if ($data){
	$showData=true;
}else {
	$alertHead='没有查到相关数据';
}


include( renderTemplate("{$module}/{$module}_{$action}") );
?>
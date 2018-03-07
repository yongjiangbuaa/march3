<?php
!defined('IN_ADMIN') && exit('Access Denied');
$showData = false;
$headAlert='';
$saveAuth=false;
$memberArray=array('maxiaoyu','yangchao','qinbinbin',); //人员权限
if (in_array($_COOKIE['u'], $memberArray)){
	$saveAuth=true;
}

$verArray=get_default_appversion();
if (isset($verArray['appVersion']) && $verArray['appVersion']){
	$appVersion=$verArray['appVersion'];
}
if ($_REQUEST['appVersion']){
	$appVersion = $_REQUEST['appVersion'];
}

function compareStrNum($str1,$str2){

}

$sql="select appVer from tbl_version_plan;";
$ret=query_deploy($sql);
$appArray=array();
$displayArr=array();
if (!$ret['error'] && $ret['ret']['data']){
	foreach ($ret['ret']['data'] as $row){
		$temp=explode(".", $row['appVer']);
		$numStr='';
		foreach ($temp as $trow){
			if(strlen($trow)==1){
				$trow='0'.$trow;
			}
			$numStr.=$trow;
		}
		$appArray[$numStr]=$row['appVer'];
	}
}

//print_r($appArray);

// 	$appNumArray=array_keys($appArray);
// 	array_multisort($appNumArray,SORT_ASC,$appArray);
ksort($appArray);
$appArray=array_values($appArray);


if (count($appArray)<=5){
	$displayArr=$appArray;
}else {
	$index=array_search($appVersion,$appArray);
	if (($index-2)>=0 && ($index+2)<=(count($appArray)-1)){
		$displayArr[]=$appArray[$index-2];
		$displayArr[]=$appArray[$index-1];
		$displayArr[]=$appArray[$index];
		$displayArr[]=$appArray[$index+1];
		$displayArr[]=$appArray[$index+2];
	}elseif (($index-2)>=0 && ($index+1)==(count($appArray)-1)){
		$displayArr[]=$appArray[$index-3];
		$displayArr[]=$appArray[$index-2];
		$displayArr[]=$appArray[$index-1];
		$displayArr[]=$appArray[$index];
		$displayArr[]=$appArray[$index+1];
	}elseif (($index-2)>=0 && $index==(count($appArray)-1)){
		$displayArr[]=$appArray[$index-4];
		$displayArr[]=$appArray[$index-3];
		$displayArr[]=$appArray[$index-2];
		$displayArr[]=$appArray[$index-1];
		$displayArr[]=$appArray[$index];
	}elseif (($index-1)==0 && ($index+2)<=(count($appArray)-1)){
		$displayArr[]=$appArray[$index-1];
		$displayArr[]=$appArray[$index];
		$displayArr[]=$appArray[$index+1];
		$displayArr[]=$appArray[$index+2];
		$displayArr[]=$appArray[$index+3];
	}elseif ($index==0 && ($index+2)<=(count($appArray)-1)){
		$displayArr[]=$appArray[$index];
		$displayArr[]=$appArray[$index+1];
		$displayArr[]=$appArray[$index+2];
		$displayArr[]=$appArray[$index+3];
		$displayArr[]=$appArray[$index+4];
	}
}

if (empty($appVersion)){
	$headAlert='游戏版本号不能为空!';
}else {
	$sql="select * from tbl_version_plan where appVer='$appVersion';";
	$ret=query_deploy($sql);
	if (!$ret['error'] && $ret['ret']['data']){
		$msg_cn= str_replace("<br>", "\n", $ret['ret']['data'][0]['msg_cn']);
		$msg_en= str_replace("<br>", "\n", $ret['ret']['data'][0]['msg_en']);
	}
	if (empty($msg_cn) && empty($msg_en)){
		$headAlert='没有查到相关数据';
	}
}

if ($_REQUEST['type']=='edit') {
	$appVersion = $_REQUEST['appVersion'];
	$msg_cn=addslashes($_REQUEST['msg_cn']?$_REQUEST['msg_cn']:'');
	$msg_en=addslashes($_REQUEST['msg_en']?$_REQUEST['msg_en']:'');
	$defaultVer=$_REQUEST['defaultVer'];
	if (empty($appVersion)){
		$headAlert='游戏版本号不能为空!';
	}else {
		$sql="insert into tbl_version_plan(appVer,msg_cn,msg_en) values('$appVersion','$msg_cn','$msg_en') ON DUPLICATE KEY UPDATE msg_cn='$msg_cn',msg_en='$msg_en';";
		$ret=query_deploy($sql,true);
		if (!$ret['error']){
			if($defaultVer==1){
				write_default_appversion($appVersion);
			}
			exit('保存成功');
		}else {
			exit('操作失败');
		}
	}
}

function get_default_appversion(){
	if (!file_exists('/tmp/default_appversion.txt')) {
		return array();
	}
	$appVer = parse_ini_file('/tmp/default_appversion.txt');
	return $appVer;
}
function write_default_appversion($version){
	file_put_contents('/tmp/default_appversion.txt', "appVersion=$version\n");
}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>
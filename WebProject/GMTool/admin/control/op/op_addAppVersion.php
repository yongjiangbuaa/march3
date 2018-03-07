<?php
!defined('IN_ADMIN') && exit('Access Denied');

//添加游戏版本
if($_REQUEST['event'] == 'add')
{
	$appVersion=trim($_REQUEST['appVersion']);
	if (empty($appVersion)){
		exit('游戏版本号不能为空');
	}
	$appVersionArray = require ADMIN_ROOT . '/etc/appVersionArray.php';
	$temp=array($appVersion=>$appVersion);
	$newAppVersionArray=array_merge($temp,$appVersionArray);
	if (empty($newAppVersionArray)){
		exit('游戏版本添加失败');
	}
	uksort($newAppVersionArray,"compare2");
	$strarr = var_export ( $newAppVersionArray, true );
	file_put_contents ( ADMIN_ROOT . '/etc/appVersionArray.php', "<?php\n \$appVersionArray= " . $strarr . ";\nreturn \$appVersionArray;\n?>" );
	exit('游戏版本添加成功');
}

//删除游戏版本
if($_REQUEST['event'] == 'del')
{
	$appVersion=trim($_REQUEST['appVersion']);
	if (empty($appVersion)){
		exit('游戏版本号不能为空');
	}
	$appVersionArray = require ADMIN_ROOT . '/etc/appVersionArray.php';
	if (!isset($appVersionArray[$appVersion])){
		exit('该游戏版本号不存在');
	}
	unset($appVersionArray[$appVersion]);
	$strarr = var_export ( $appVersionArray, true );
	file_put_contents ( ADMIN_ROOT . '/etc/appVersionArray.php', "<?php\n \$appVersionArray= " . $strarr . ";\nreturn \$appVersionArray;\n?>" );
	exit('删除版本添加成功');
}

function compare2($stra,$strb){
	return -1 * compare($stra,$strb);
}
function compare($stra,$strb){
	$stra = trim($stra,"'");
	$strb = trim($strb,"'");

	$a = explode('.', $stra);
	$b = explode('.', $strb);
	if($a[0] > $b[0]){
		return 1;
	}
	elseif($a[0] < $b[0]){
		return -1;
	}
	elseif($a[1] > $b[1]){
		return 1;
	}
	elseif($a[1] < $b[1]){
		return -1;
	}
	elseif($a[2] > $b[2]){
		return 1;
	}
	elseif($a[2] < $b[2]){
		return -1;
	}
	elseif($a[3] > $b[3]){
		return 1;
	}
	elseif($a[3] < $b[3]){
		return -1;
	}
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
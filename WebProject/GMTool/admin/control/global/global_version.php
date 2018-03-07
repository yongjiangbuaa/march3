<?php
!defined('IN_ADMIN') && exit('Access Denied');
$title = "版本信息修改";
if($_REQUEST['type'] == 'modify')
{
	$serverTemp = $_REQUEST['id'];
	$temp = explode('_', $serverTemp);
	$modifyid = $temp[0];
	$modifyActName = $temp[1];
	if($modifyActName=='name') {
		$fid = 'name';
	}
	if($modifyActName=='version') {
		$fid = 'version';
	}
	$modifySql = "UPDATE function_version SET $fid ='".$_REQUEST['newStr']."'  WHERE id = $modifyid ";
	$result = $page->globalExecute($modifySql,2);
}

	$sql_server_info = "select * from function_version;";
	$result = $page->globalExecute($sql_server_info, 3);
	$result = $result['ret']['data'];


include( renderTemplate("{$module}/{$module}_{$action}") );
?>
<?php
!defined('IN_ADMIN') && exit('Access Denied');
global $servers;
foreach ($_REQUEST as $server=>$value)
{
	if($servers[$server] && $value == 'on')
		$selectServer[] = $server;
}
if($_REQUEST['type'] == 'add')
{
	//生成sql
	$params = $_REQUEST;
	$temp = array();
	$startTime = strtotime($params['startTime'])*1000;
	$type = $params['configtype'];
	$contAll=$params['contents'];
	//$notSendArray=array();
	//$notSend=true;


	$datacontent = new stdClass();
	//$datanotification = new stdClass();
	foreach ($mailLangs as $lang=>$langV) {
		$datacontent->$lang = trim(trim($params['txtcontent'.ucfirst($lang)],'"'));
		//$datanotification->$lang = substr($params['txtnotification'.ucfirst($lang)], 0, 128);
	}
	$contAll = addslashes(json_encode($datacontent));
	if(strlen($contAll)>0)
	{
		$uid = md5($startTime.$type.$contAll.time());
		$sql = "INSERT INTO `server_push_config` (`uid`, `type`, `startTime`, `contents`, `state`) VALUES ('$uid', '$type', '$startTime', '$contAll' ,  '0')";
		$op_msg = '';
		foreach ($selectServer as $server) {
			$op_msg .= $server.' ';
			$result = $page->executeServer($server,$sql,2);
		}
	}
	/*
	foreach($datacontent as $contLang=>$contData)
	{
		if(strlen($contData)>0)
		{
			$notSend=false;
			$uid = md5($startTime.$type.$contData.$contLang.time());
			$sql = "INSERT INTO `server_push_config` (`uid`, `type`, `startTime`, `contents`,`lang`, `state`) VALUES ('$uid', '$type', '$startTime', '$contData' , '$contLang' ,  '0')";
			$op_msg = '';
			foreach ($selectServer as $server) {
				$op_msg .= $server.' ';
				$result = $page->executeServer($server,$sql,2);
			}
		}else
		{
			$notSendArray[]=$contLang;
		}

	}
	
	if($notSend)
	{
		if(strlen($contAll)>0)
		{
			$uid = md5($startTime.$type.$contAll.time());
			$sql = "INSERT INTO `server_push_config` (`uid`, `type`, `startTime`, `contents`, `state`) VALUES ('$uid', '$type', '$startTime', '$contAll' ,  '0')";
			$op_msg = '';
			foreach ($selectServer as $server) {
				$op_msg .= $server.' ';
				$result = $page->executeServer($server,$sql,2);
			}
		}

	}
	else{
		if(strlen($contAll)>0)
		{
			foreach($notSendArray as $notSendLang)
			{
				$uid = md5($startTime.$type.$contAll.$notSendLang.time());
				$sql = "INSERT INTO `server_push_config` (`uid`, `type`, `startTime`, `contents`,`lang`, `state`) VALUES ('$uid', '$type', '$startTime', '$contAll' , '$notSendLang' ,  '0')";
				$op_msg = '';
				foreach ($selectServer as $server) {
					$op_msg .= $server.' ';
					$result = $page->executeServer($server,$sql,2);
				}
			}
		}

	}
	*/


}

if($_REQUEST['type'] == 'deleteAll')//删除
{
	$uid = $_REQUEST['uid'];
	foreach ($servers as $server=>$info) {
		$sql = "DELETE FROM `server_push_config` WHERE (`uid`='$uid')";
		$result = $page->executeServer($server,$sql,2);
	}

}
if($_REQUEST['type'] == 'delete')//删除
{
	$uid = $_REQUEST['uid'];
	$sql = "DELETE FROM `server_push_config` WHERE (`uid`='$uid')";
	$result = $page->execute($sql,2);
}

if($_REQUEST['type'] == 'modify')//修改
{
	$uid = $_REQUEST['uid'];
	$temp = explode('_', $uid);
	$modifyUid = $temp[0];
	$modifyKey = $temp[1];
	if($modifyKey == 'startTime')
		$newData = strtotime($_REQUEST['newDate'])*1000;
	else
		$newData = trim($_REQUEST['newData']);
	$sql = "UPDATE `server_push_config` SET `$modifyKey`='$newData' WHERE (`uid`='$modifyUid')";
	$result = $page->execute($sql,2);
}
$sql = "select * from server_push_config order by startTime desc";
$result = $page->execute($sql,3);
$dbData = $result['ret']['data'];
foreach ($dbData as &$curRow){
	$curRow['startTime'] = date('Y-m-d H:i:s',$curRow['startTime']/1000);
	switch ($curRow['state']){
		case 0 : $curRow['stateMsg'] = '未推送';break;
		case 1 : $curRow['stateMsg'] = '正在推送';break;
		case 2 : $curRow['stateMsg'] = '已推送完毕';break;
	}
	if($curRow['lang']==null)
	{
		$curRow['lang']='全部';
	}else
	{
		if($curRow['lang']=='zh_CN')
		{
			$curRow['lang']='zh-Hans';
		}else if($curRow['lang']=='zh_TW')
		{
			$curRow['lang']='zh-Hant';
		}
		foreach ($mailLangs as $lang=>$langV) {
			if($curRow['lang']==$lang)
			{
				$curRow['lang']=$langV;
				break;
			}
		}
	}
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
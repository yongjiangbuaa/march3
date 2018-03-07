<?php
!defined('IN_ADMIN') && exit('Access Denied');
global $servers;
// foreach ($_REQUEST as $server=>$value)
// {
// 	if($servers[$server] && $value == 'on'){
// 		$selectServer[] = $server;
// 		$serverArray[] = substr($server, 1);
// 	}
// }

$sttt = $_REQUEST['selectServer'];
$serverDiv=loadDiv($sttt);
$erversAndSidsArr=getSelectServersAndSids($sttt);
$selectServer=$erversAndSidsArr['withS'];
$serverArray=$erversAndSidsArr['onlyNum'];

if($_REQUEST['type'] == 'add')
{
	//生成sql
	$params = $_REQUEST;
	$temp = array();
	foreach ($params as $key=>$value){
		if(substr($key,0,6) != 'reward' || $value == null)
			continue;
		$realKey = substr($key,7,strlen($key));
		$temp[$realKey] = $value;
	}
	$reward = '';
	$checkArr = array('general'=>'genNum', 'goods'=>'goodsNum');
	$filterArr = array('genNum', 'goodsNum');
	foreach ($checkArr as $a=>$b){
		if($temp[$a] && !$temp[$b])
			unset($temp[$a]);
		elseif($temp[$b] && !$temp[$a])
			unset($temp[$b]);
	}
	foreach ($temp as $key=>$value){
		if(in_array($key,$filterArr))
			continue;
		if(in_array($key, array_keys($checkArr))) {
			$rewardArray = explode('|', $value);
			$rewardNumArray = explode('|', $temp[$checkArr[$key]]);
			for ($index = 0; $index < count($rewardArray); $index++) {
				if($rewardArray[$index] && $rewardNumArray[$index]) {
					if($reward)
						$reward .= '|';
					$reward .= $key.','.$rewardArray[$index].','.$rewardNumArray[$index];
				}
			}
		}
		else {
			if($reward)
				$reward .= '|';
			$reward .= $key.',0,'.$value;
		}
	}
	foreach ($mailPf as $pfValue){
		$startTimes[$pfValue]=strtotime($params[$pfValue.'_startTime'])*1000;
		$updateVersions[$pfValue]=trim($params[$pfValue.'_updateVersion']);
		if(preg_match('/[^0-9.]/',$updateVersions[$pfValue])){
			exit('error appVersion ,only number and '.' !!');
		}
	}
//	echo preg_replace('/[^0-9.]+/',"",$string).PHP_EOL;
	$activationId = $params['activationId'];
	
	$defaulttime = 86400*365;//365 days
	$regStartTime = 0;
	$regEndTime = (time()+3600)*1000;
	$lastOnlineTime = (time()+$defaulttime)*1000;
	$lastOnlineTimeBegin = 0;
	$levelMin = 1;
	$levelMax = 999;
	
	$datatitle = new stdClass();
	$datacontent = new stdClass();
//	$datanotification = new stdClass();
	foreach ($mailLangs as $lang=>$langV){
		$datatitle ->$lang = $params['txttitle'.ucfirst($lang)];
		$datacontent ->$lang =$params['txtcontent'.ucfirst($lang)];
//		$datanotification->$lang =$params['txtnotification'.ucfirst($lang)];
	}
// 	$title = $params['title'];
// 	$contents = $params['contents'];
//	$updateVersion = $params['updateVersion'];
	$title = addslashes(json_encode($datatitle));
	$contents = addslashes(json_encode($datacontent));
	$notification = addslashes($params['txtnotificationEn']);
	$notification = substr($notification, 0, 128);
	foreach ($startTimes as $pfKey=>$sTime){
		if(!$sTime){
			continue;
		}
		$uid = md5($pfKey.$title.$sTime.time());
		$sql = "INSERT INTO `server_push` (`uid`, `type`, `mailType`, `startTime`, `endTime`, `regStartTime`, `regEndTime`, `lastOnlineTimeBegin`, `lastOnlineTime`, `levelMin`, `levelMax`, `title`, `contents`, `reward`, `updateVersion`, `state`, `parse`, `notification`, `platform`,`activityId`) 
		VALUES ('$uid', '0', '16', '$sTime', '0', '$regStartTime', '$regEndTime', '$lastOnlineTimeBegin', '$lastOnlineTime', '$levelMin', '$levelMax', '$title', '$contents', '$reward', '{$updateVersions[$pfKey]}', '0','0','$notification','$pfKey','$activationId')";
		$op_msg = '';
// 		foreach ($selectServer as $server=>$serInfo) {
// 			$op_msg .= $server.' ';
// 			$result = $page->executeServer($server,$sql,2);
// 		}
		
		$host = gethostbyname(gethostname());
//		if ($host == '10.1.16.211' || $host == 'localhost' || $host == 'URLIP' || $host == 'IPIPIP') {
			foreach ($selectServer as $server=>$serInfo) {
				$op_msg .= $server.' ';
				$result = $page->executeServer($server,$sql,2);
			}
//		}else {
//			$serverIds=implode(",", $serverArray);
//			$sql=addslashes($sql);
//			$globalSql="insert into server_push_auxiliary(`uid`, `serverStr`, `sqlStr`, `status`) values('$uid','$serverIds','$sql',0) ON DUPLICATE KEY UPDATE uid='$uid',serverStr='$serverIds',sqlStr='$sql',status=0;";
//			$page->globalExecute($globalSql, 2);
//		}
//		echo $uid;
	}
}
if($_REQUEST['type'] == 'confirm')//确认
{
	$uid = $_REQUEST['uid'];
	$sql = "update server_push set confirm =1 WHERE (`uid`='$uid')";
	foreach ($servers as $server=>$serverInfo){
		$result = $page->executeServer($server,$sql,2);
	}
}
if($_REQUEST['type'] == 'delete')//删除
{
	$uid = $_REQUEST['uid'];
	
	$host = gethostbyname(gethostname());
//	if ($host == 'IPIPIP' || $host == 'IPIPIP' || $host == 'URLIP' || $host == 'IPIPIP') {
		$sql = "DELETE FROM `server_push` WHERE (`uid`='$uid')";
		$result = $page->execute($sql,2);
//	}else {
//		$globalSql="delete from server_push_auxiliary where uid='$uid';";
//		$page->globalExecute($globalSql, 2);
//
//		$sql = "DELETE FROM `server_push` WHERE (`uid`='$uid')";
//		$result = $page->execute($sql,2);
//	}
}
if($_REQUEST['type'] == 'deleteAll')//全服删除
{
	$uid = $_REQUEST['uid'];
	
	$host = gethostbyname(gethostname());
//	if ($host == 'IPIPIP' || $host == 'IPIPIP' || $host == 'URLIP' || $host == 'IPIPIP') {
		$sql = "DELETE FROM `server_push` WHERE (`uid`='$uid')";
		foreach ($servers as $server=>$serverInfo){
			$result = $page->executeServer($server,$sql,2);
		}
//	}else {
//		$globalSql="delete from server_push_auxiliary where uid='$uid';";
//		$page->globalExecute($globalSql, 2);
//
//		$sql = "DELETE FROM `server_push` WHERE (`uid`='$uid')";
//		foreach ($servers as $server=>$serverInfo){
//			$result = $page->executeServer($server,$sql,2);
//		}
//	}
}

if($_REQUEST['type'] == 'modify')//修改
{
	$uid = $_REQUEST['uid'];
	$pos=strripos($uid, '_');
	$modifyUid = substr($uid, 0,$pos);
	$modifyKey = substr($uid, $pos+1);
	if(in_array($modifyKey, array('startTime','regStartTime','regEndTime','lastOnlineTime')))
		$newData = strtotime($_REQUEST['newDate'])*1000;
	else
		$newData = trim($_REQUEST['newDate']);
	$sql = "UPDATE `server_push` SET `$modifyKey`='$newData' WHERE (`uid`='$modifyUid')";
	$result = $page->execute($sql,2);
}
// $sql = "select count(1) sum,status,actId from user_activity group by actId,status";
// $result = $page->execute($sql,3);
// foreach ($result['ret']['data'] as $curRow){
// 	$detail[$curRow['actId']][$curRow['status']] = $curRow['sum'];
// }
$sql = "select * from server_push where type = 0 and mailType = 16 order by startTime desc limit 10";
$result = $page->execute($sql,3);
$dbData = $result['ret']['data'];
foreach ($dbData as &$curRow){
	$title = json_decode($curRow['title'], true);
	$contents = json_decode($curRow['contents'], true);
	$curRow['title'] = ($title===null)?$curRow['title']:$title['en'].$title['zh_Hans'];
	$curRow['contents'] = ($contents===null)?$curRow['contents']:$contents['en'].$contents['zh_Hans'];	
	$curRow['startTime'] = date('Y-m-d H:i',$curRow['startTime']/1000);
	$curRow['regStartTime'] = date('Y-m-d H:i',$curRow['regStartTime']/1000);
	$curRow['regEndTime'] = date('Y-m-d H:i',$curRow['regEndTime']/1000);
	$curRow['lastOnlineTimeBegin'] = date('Y-m-d H:i',$curRow['lastOnlineTimeBegin']/1000);
	$curRow['lastOnlineTime'] = date('Y-m-d H:i',$curRow['lastOnlineTime']/1000);
	$curRow['pushCount'] = $detail[$curRow['uid']][0]+$detail[$curRow['uid']][1];
	$curRow['rewardCount'] = $detail[$curRow['uid']][1];
	if($curRow['reward']){
		$tmpArr = explode('|', $curRow['reward']);
		$temp = '';
		$reward = array();
		foreach ($tmpArr as $item)
		{
			$tmpItem = explode(',', $item);
			$reward[] = $tmpItem;
		}
		foreach ($reward as $value){
			$key = $value[0];
			if($rewardLink[$key]){
				if($temp)
					$temp .= '<br />';
				if($value[1])
					$temp .= $rewardLink[$key].':'.$value[1].'<br />数量:'.$value[2];
				else
					$temp .= $rewardLink[$key].':'.$value[2];
			}
		}
		$curRow['reward'] = $temp;
	}
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>

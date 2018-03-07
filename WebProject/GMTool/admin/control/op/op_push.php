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
	$startTimes=array();
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
	$defaulttime = 86400*365;//365 days
	if ($params['target'] == 'all') {
		$startTime = strtotime($params['startTime'])*1000;
		$startTimes['all']=$startTime;
		
		$regStartTime = 0;
		$regEndTime = (strtotime($params['startTime'])+3600)*1000;
		$lastOnlineTime = (strtotime($params['startTime'])+$defaulttime)*1000;
		$lastOnlineTimeBegin = 0;
		$levelMin = 1;
		$levelMax = 999;
		$bLvMin = 1;
		$bLvMax = 999;
// 		$platform = '';
	}else{
		foreach ($mailPf as $pfValue){
			$startTimes[$pfValue]=strtotime($params[$pfValue.'_startTime'])*1000;
		}
		
		$regStartTime = $params['regStartTime']?strtotime($params['regStartTime'])*1000:0;
		$regEndTime = $params['regEndTime']?strtotime($params['regEndTime'])*1000:0;
		$lastOnlineTime = $params['lastOnlineTime']?strtotime($params['lastOnlineTime'])*1000:0;
		$lastOnlineTimeBegin = $params['lastOnlineTimeBegin']?strtotime($params['lastOnlineTimeBegin'])*1000:0;
		$levelMin = $params['levelMin']?$params['levelMin']:1;
		$levelMax = $params['levelMax']?$params['levelMax']:999;
		$bLvMin = $params['bLvMin']?$params['bLvMin']:1;
		$bLvMax = $params['bLvMax']?$params['bLvMax']:999;
// 		$platform = strval($params['target_platform']);
// 		if ($platform == 'all') {
// 			$platform = '';
// 		}
	}
	
	$datatitle = new stdClass();
	$datacontent = new stdClass();
	//$datanotification = new stdClass();
	foreach ($mailLangs as $lang=>$langV) {
		$datatitle->$lang = trim(trim($params['txttitle'.ucfirst($lang)],'"'));
		$datacontent->$lang = trim(trim($params['txtcontent'.ucfirst($lang)],'"'));
		//$datanotification->$lang = substr($params['txtnotification'.ucfirst($lang)], 0, 128);
	}
	$countries=$params['countries'];
	$CDKeySeries=intval($params['CDKeySeries']);
// 	$title = $params['title'];
// 	$contents = $params['contents'];
	$title = addslashes(json_encode($datatitle));
	$contents = addslashes(json_encode($datacontent));
	//$notification = addslashes(json_encode($datanotification));
	$notification = substr($params['notification'], 0, 128);
	$notification = addslashes($notification);
	$reply = 'true' == $params['reply'] ? 1 : 0;
	$like = 'true' == $params['like'] ? 1 : 0;
	$deviceLimit = 'true' == $params['deviceLimit'] ? 1 : 0;
	$bind = 'true' == $params['bind'] ? 1 : 0;
	$HDflag = 'true' == $params['isHD'] ? 1 : 0;
	
	foreach ($startTimes as $pfKey=>$startTime){
		if(!$startTime){
			continue;
		}
		if ($pfKey=='all'){
			$pfKey='';
		}
		if (!$regEndTime){
			$regEndTime = $startTime+3600000;
		}
		if (!$lastOnlineTime){
			$lastOnlineTime = $startTime+$defaulttime*1000;
		}
		$uid = md5($pfKey.$title.$startTime.time());
		//这个sql是插入到server_push_auxiliary 表里的
		$sql = "INSERT INTO `server_push` (`uid`, `type`, `mailType`, `startTime`, `endTime`, `regStartTime`, `regEndTime`, `lastOnlineTimeBegin`, `lastOnlineTime`, `levelMin`, `levelMax`, `title`, `contents`, `reward`, `state`, `parse`, `notification`, `reply`, `likeStatus`, `platform`, `activityId`,`countries`,`deviceLimit`,`CDKeySeries`, `bind`, `bLvMin`, `bLvMax`, `HDflag`)
		VALUES ('$uid', '0', '13', '$startTime', '0', '$regStartTime', '$regEndTime', '$lastOnlineTimeBegin', '$lastOnlineTime', '$levelMin', '$levelMax', '$title', '$contents', '$reward', '0','0','$notification', $reply,$like,'$pfKey','$uid','$countries',$deviceLimit,$CDKeySeries,$bind,$bLvMin,$bLvMax,$HDflag)";
		$op_msg = '';
		
		$host = gethostbyname(gethostname());

		if ($host == 'IPIPIP' || $host == 'IPIPIP' || $host == 'URLIP' || $host == 'IPIPIP') {
			foreach ($selectServer as $server=>$serInfo) {
				$op_msg .= $server.' ';
				$result = $page->executeServer($server,$sql,2);
			}
		}else {
			$serverIds=implode(",", $serverArray);
			$sql=addslashes($sql);
			$globalSql="insert into server_push_auxiliary(`uid`, `serverStr`, `sqlStr`, `status`) values('$uid','$serverIds','$sql',0) ON DUPLICATE KEY UPDATE uid='$uid',serverStr='$serverIds',sqlStr='$sql',status=0;";
			$page->globalExecute($globalSql, 2,true);
		}
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
	if ($host == 'IPIPIP' || $host == 'IPIPIP' || $host == 'URLIP' || $host == 'IPIPIP') {
		$sql = "DELETE FROM `server_push` WHERE (`uid`='$uid')";
		$result = $page->execute($sql,2);
	}else {
		$globalSql="delete from server_push_auxiliary where uid='$uid';";
		$page->globalExecute($globalSql, 2);
		
		$sql = "DELETE FROM `server_push` WHERE (`uid`='$uid')";
		$result = $page->execute($sql,2);
	}
	
}
if($_REQUEST['type'] == 'deleteAll')//全服删除
{
	$uid = $_REQUEST['uid'];
	
	$host = gethostbyname(gethostname());
	if ($host == 'IPIPIP' || $host == 'IPIPIP' || $host == 'URLIP' || $host == 'IPIPIP') {
		$sql = "DELETE FROM `server_push` WHERE (`uid`='$uid')";
		foreach ($servers as $server=>$serverInfo){
			$result = $page->executeServer($server,$sql,2);
		}
	}else {
		$globalSql="delete from server_push_auxiliary where uid='$uid';";
		$page->globalExecute($globalSql, 2);
	
		$sql = "DELETE FROM `server_push` WHERE (`uid`='$uid')";
		foreach ($servers as $server=>$serverInfo){
			$result = $page->executeServer($server,$sql,2);
		}
	}
	
}

if($_REQUEST['type'] == 'modify')//修改
{
	$uid = $_REQUEST['uid'];
	$temp = explode('_', $uid);
	$modifyUid = $temp[0];
	$modifyKey = $temp[1];
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

$sql = "select * from server_push where type = 0 and mailType = 13 order by startTime desc";
$result = $page->execute($sql,3);
$dbData = $result['ret']['data'];
$enlang = loadLanguage('en');

foreach ($dbData as &$curRow){

	$title = json_decode($curRow['title'], true);
	//echo "3".$title.$curRow['uid']."</br>";
	$contents = json_decode($curRow['contents'], true);
	$curRow['title'] = ($title===null)?$curRow['title']:($title['en']===null?$enlang[$title]:$title['en'].$title['zh_Hans']);
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
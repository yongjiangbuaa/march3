<?php
!defined('IN_ADMIN') && exit('Access Denied');
if (array_key_exists ( 'type', $_REQUEST )) {
	$type = $_REQUEST ['type'];
}
if (array_key_exists ( 'deviceToken', $_REQUEST )) {
	$deviceToken = $_REQUEST ['deviceToken'];
}

require_once ADMIN_ROOT.'/push/Push.php';
$push = new Push();

if($_REQUEST['type'] == 'view')
{
	$showpage = $_REQUEST['page'];
	$page_limit = 15;
	$sql = "select count(1) DataCount from server_push_person";
	$result = $page->execute($sql,3);
	$count = $result['ret']['data'][0]['DataCount'];

	$pager = page($count, $showpage, $page_limit);
	$index = $pager['offset'];
	
	$sql = "select * from `server_push_person` order by `startTime` desc limit $index,$page_limit";
	$result = $page->execute($sql,3);
	$dbData=$result['ret']['data'];
//	if($result['error'] == 'no data')
//		echo '没有发送过单人推送';
//	else{
		foreach ($dbData as $mailItem)
		{
			$mailItem['startTime'] = date('Y-m-d H:i:s',$mailItem['startTime']/1000);

			switch ($mailItem['state']){
				case 0 : $mailItem['stateMsg'] = '未推送';break;
				case 1 : $mailItem['stateMsg'] = '正在推送';break;
				case 2 : $mailItem['stateMsg'] = '已推送完毕';break;
			}

			//$mailData[] = array('时间'=>$mailItem['startTime'],'推送帐号信息'=>$mailItem['userid'],'推送信息'=>$mailItem['contents'],'状态'=>$mailItem['state']==0?"未推送":"已推送");
		}
	//}
//	$html = "<div style='TABLE-LAYOUT:fixed;WORD-WRAP:break-word;word-break:break-all;float:left;width:105%;height:480px;text-align:center;overflow-x:auto;overflow-y:auto;'>
//	<table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
//	$i = 1;
//	$title = false;
//	foreach ($mailData as $sort=>$sqlData)
//	{
//		if(!$title)
//		{
//			$html .= "<tr class='listTr'><th>编号</th>";
//			foreach ($sqlData as $key=>$value){
//				$html .= "<th>" . $key . "</th>";
//			}
//			$html .= "</tr>";
//			$title = true;
//		}
//		$html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
//		$html .= "<td>$i</td>";
//		$i++;
//		foreach ($sqlData as $key=>$value){
//			if($key == '奖励' && $value){
//				$html .= "<td><button type='button' class='btn btn-info' id=rewardButton{$sort} name='btn_set' onclick=showReward('"."rewardButton{$sort}','rewardTd{$sort}"."')>展开附件列表</button><li id=rewardTd{$sort} style='display:none'>" . $value . "</li></td>";
//			}else{
//				$html .= "<td>" . $value . "</td>";
//			}
//		}
//		$html .= "</tr>";
//	}
//	$html .= "</table></div><br/>";
//	if($pager['pager'])
//		$html .= "<div class='span11' style='TEXT-ALIGN:center;'>" . $pager['pager'] . "</div>";
//	echo $html;
//	exit;
}
//单人推送
if($_REQUEST['type'] == 'push'){
	$deviceToken = $_REQUEST['deviceToken'];
	$message = $_REQUEST['message'];
	$time = microtime(true)*1000;
	if($deviceToken && $message){
		$result = $push->pushToUser($deviceToken,$message);
		if (!isset($result['errmsg'])) {
			$status = 1;
			$op_msg = "send push notification success<br />".
				"http code:".$result['http_code']."<br />".
				"response:".$result['data']."<br />".
				"cost time:".$result['time']."<br />";
		}else{
			$status = 0;
			$error_msg = "send push notification fail. errno:".$result['errno'].
				', errmsg:'.$result['errmsg'].
				', http code:'.$result['http_code'].
				"<br />";
		}
		//添加记录
		global $servers;
		$logServer = reset(array_keys($servers));
		$sql = "INSERT INTO `push_log` (`uid`, `time`, `message`, `status`) VALUES ('$deviceToken', '$time', '$message', '$status')";
		$page->executeServer($logServer,$sql,2);
	}else{
		$error_msg = 'get none deviceToken or message';
	}
}

if($_REQUEST['type'] == 'deleteAll')//删除
{
	$uid = $_REQUEST['uid'];
	foreach ($servers as $server=>$info) {
		$sql = "DELETE FROM `server_push_person` WHERE (`uid`='$uid')";
		$result = $page->executeServer($server,$sql,2);
	}

}
if($_REQUEST['type'] == 'delete')//删除
{
	$uid = $_REQUEST['uid'];
	$sql = "DELETE FROM `server_push_person` WHERE (`uid`='$uid')";
	$result = $page->execute($sql,2);
}

if($_REQUEST['type'] == 'add'){

	$startTime=strtotime($_REQUEST['startTime'])*1000;
	$sendListStr=$_REQUEST ['txtlist'];
	$content=$_REQUEST ['contents'];
	if(strlen($sendListStr)>0)
	{
		$arr=explode("|",$sendListStr);
		foreach($arr as $u)
		{
			$uarr=explode("_",$u);
			$list[$uarr[1]]=$uarr[0];
		}
	}
	foreach($list as $uid=>$serid)
	{
		foreach ($servers as $server=>$info) {
			if($server==$serid)
			{
				$uidmd5 = md5($startTime.$server.$content.$uid.time());
				$sql = "INSERT INTO `server_push_person` (`uid`,`userid`,`startTime`,`contents`,`state`) VALUES ('$uidmd5','$uid','$startTime','$content','3')";
				print_r($sql);
				$result = $page->executeServer($server,$sql,2);
			}
		}
	}

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
		$newData = trim($_REQUEST['newDate']);
	$sql = "UPDATE `server_push_person` SET `$modifyKey`='$newData' WHERE (`uid`='$modifyUid')";
	$result = $page->execute($sql,2);
}

if($_REQUEST['type'] == 'confirm')//确认
{
	$uid = $_REQUEST['uid'];
	$sql = "UPDATE `server_push_person` SET `state`=0 WHERE (`uid`='$uid')";
	$result = $page->execute($sql,2);
}

$sql = "select * from `server_push_person` order by `startTime` desc";
$result = $page->execute($sql,3);
$dbData=$result['ret']['data'];
foreach ($dbData as &$mailItem)
{
	$mailItem['startTime'] = date('Y-m-d H:i:s',$mailItem['startTime']/1000);
	switch ($mailItem['state']){
		case 0 : $mailItem['stateMsg'] = '未推送';break;
		case 1 : $mailItem['stateMsg'] = '正在推送';break;
		case 2 : $mailItem['stateMsg'] = '已推送完毕';break;
		case 3 : $mailItem['stateMsg'] = '未确认';break;
	}
	//$mailData[] = array('时间'=>$mailItem['startTime'],'推送帐号信息'=>$mailItem['userid'],'推送信息'=>$mailItem['contents'],'状态'=>$mailItem['state']==0?"未推送":"已推送");
}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>
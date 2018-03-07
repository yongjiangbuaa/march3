<?php
!defined('IN_ADMIN') && exit('Access Denied');
if (array_key_exists ( 'type', $_REQUEST )) {
	$type = $_REQUEST ['type'];
}
if (array_key_exists ( 'page', $_REQUEST )) {
	$showpage = $_REQUEST ['page'];
}
$defaultDate = date('Y-m-d H:i:s',time()); 
if ($type == 'add') 
{
	$params = $_POST;
	$sendBy = $page->getAdmin();
	switch ($params['userType']){
		case 'name'://角色名
			$sql = "select uid from userprofile where name = '{$_REQUEST['user']}'";
			break;
		case 'uid'://UID
			$sql = "select uid from userprofile where uid = '{$_REQUEST['user']}'";
			break;
	}
	$tmp = $page->execute($sql,3);
	$uid = $tmp['ret']['data'][0]['uid'];
	if(!$uid){
		$error_msg = "user not found";
	}else{
		$temp = array();
		foreach ($params as $key=>$value){
			if(substr($key,0,6) != 'reward' || $value == null)
				continue;
			$realKey = substr($key,7,strlen($key));
			$temp[$realKey] = $value;
		}
		$reward = '';
		$checkArr = array('general'=>'genNum', 'goods'=>'goodsNum');//领主(弃用) 和 道具
		$filterArr = array('genNum', 'goodsNum');
		//道具和道具数量必须同时都有,否则就放弃
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
				$rewardArray = explode('|', $value);//道具id  分开
				$rewardNumArray = explode('|', $temp[$checkArr[$key]]);//数量
				for ($index = 0; $index < count($rewardArray); $index++) {
					if($rewardArray[$index] && $rewardNumArray[$index]) {
						if($reward)
							$reward .= '|';
						$reward .= $key.','.$rewardArray[$index].','.$rewardNumArray[$index];//goods,id,num|goods,id,num
					}
				}
			}
			else {
				if($reward)
					$reward .= '|';
				$reward .= $key.',0,'.$value;
			}
		}

		$toUser = $uid;
		$sendTime = microtime(true)*1000;
		$title = addslashes($params['title']);//
		$contents = addslashes($params['contents']);//
		$uid = md5($toUser.$sendBy.$sendTime.$title.$contents.$reward.time());
		$sql = "INSERT INTO `server_usermail` (`uid`, `toUser`, `sendBy`, `sendTime`, `title`, `contents`, `reward`) VALUES ('$uid', '$toUser', '$sendBy', '$sendTime', '$title', '$contents', '$reward')";
		$page->execute($sql,2,true);
		$rewardStatus = 1;
		if($reward)
			$rewardStatus = 0;
		$sql = "insert into mail (uid, toUser, fromUser, fromName, status, type, rewardStatus, saveFlag, title, contents, rewardId, createTime, itemIdFlag, reply,srctype) values ('$uid', '$toUser', '', 'system', 0, 13, $rewardStatus, 0, '$title', '$contents', '$reward', $sendTime, 0, 1,5)";
		$page->execute($sql,2,true);
		sendReward($uid);
		
		adminLogUser ( $adminid, $uid, $currentServer, array (
			'userMail'=>'add',
			'reward' => $reward,
			'sendTime' => $sendTime
			)
		);
		
		//回复玩家
		if($params['mailUid']){
			$uid = $params['mailUid'];
			$time = time() * 1000;
			$sql = "update suggestion set replyContent= '$contents',replyTime = '$time'  where uid = '$uid'";
			$result = $page->execute($sql,2,true);
			exit();
		}
		
		
		
		// $sendServer = $page->getAppId();//发送请求到哪个服了
		// $sendParam = array('data'=>$param);
		// $result = $page->call( 'gm/gm/Server', $sendParam );
		// if($result['msg'])
		// 	$op_msg = $result['msg'];
		// //加邮件的时候加入统计
		// global $servers;
		// $logServer = reset(array_keys($servers));
		// if($logServer && $result['ret']['userUid']){
		// 	$sendUid = $result['ret']['userUid'];
		// 	$sendName = $result['ret']['userName'];
		// 	$title = $result['ret']['title'];
		// 	$mailUid = $result['ret']['mail'];
		// 	$content = $result['ret']['contents'];
		// 	$reward = $result['ret']['reward'];
		// 	$sendTime = $result['ret']['sendTime'];
		// 	$param = array(
		// 			'uid'=>$mailUid,
		// 			'sendBy'=>$sendBy,
		// 			'sendServer'=>$sendServer,
		// 			'sendTime'=>$sendTime,
		// 			'sendUid'=>$sendUid,
		// 			'sendName'=>$sendName,
		// 			'title'=>$title,
		// 			'contents'=>$content,
		// 			'reward'=>json_encode($reward),
		// 	);
		// 	$param = array(
		// 			"changes"=>null,
		// 			"params"=>array("type"=>21,"data"=>$param),
		// 	);
		// 	$sendParam = array('data'=>$param);
		// 	$page->callByServer($logServer, 'gm/gm/Server', $sendParam );
		// }
	}
}
if ($type == 'sendReward') 
{
	$params = $_POST;
	sendReward($params['mailUid']);
}
function sendReward($mailUid){
	$page = new BasePage();
	$page->webRequest('sendmail',array('uid'=>$mailUid));
	// $sql = "SELECT * FROM mail where uid = '$mailUid'";
	// $result = $page->execute($sql);
	// if(!$result['error'] && $result['ret']['data']){
	// 	if($result['ret']['data'][0]['rewardStatus'] == 1){
	// 		$sql = "UPDATE server_usermail set rewardStatus = 1 where uid = '$mailUid'";
	// 		$page->execute($sql);
	// 	}
	// }
}
if($showpage > 0)
{
	$page_limit = 15;
	$sql = "select count(1) DataCount from server_usermail";
	$result = $page->execute($sql,3);
	$count = $result['ret']['data'][0]['DataCount'];

	$pager = page($count, $showpage, $page_limit);
	$index = $pager['offset'];
	
	$sql = "select s.*,u.name from server_usermail s left join userprofile u on s.toUser = u.uid order by `sendTime` desc limit $index,$page_limit";

	$result = $page->execute($sql,3);
	if($result['error'] == 'no data')
		echo '没有邮件';
	else{
		foreach ($result['ret']['data'] as $mailItem)
		{
			$mailItem['sendTime'] = date('Y-m-d H:i:s',$mailItem['sendTime']/1000);
			$rewardStatus = '';
			if($mailItem['reward']){
				$tmpArr = explode('|', $mailItem['reward']);
				$temp = '';
				$reward = array();
				foreach ($tmpArr as $item)
				{
					$tmpItem = explode(',', $item);
					$reward[$tmpItem[0]] = array($tmpItem[1],$tmpItem[2]);
				}
				foreach ($reward as $key=>$value){
					if($rewardLink[$key]){
						if($temp)
							$temp .= '<br />';
						if($value[0])
							$temp .= $rewardLink[$key].':'.$value[0].'<br />数量:'.$value[1];
						else
							$temp .= $rewardLink[$key].':'.$value[1];
					}
				}
				$mailItem['reward'] = $temp;
				if(!$mailItem['rewardStatus'])
					$rewardStatus = "<button type='button' class='btn btn-info' name='btn_set' onclick=sendReward('".$mailItem['uid']."')>重发</button>";
			}
			$mailItem['rewardFail'] = $rewardStatus;
			$mailData[] = $mailItem;
		}
		$titleData = array('toUser'=>'玩家UID','sendBy'=>'发送人','name'=>'玩家名字','sendTime'=>'发送时间','title'=>'标题','contents'=>'内容','reward'=>'奖励');
		$titleWidth = array('contents'=>'40%');
	}
	$html = "<div style='TABLE-LAYOUT:fixed;WORD-WRAP:break-word;word-break:break-all;float:left;width:105%;height:480px;text-align:center;overflow-x:auto;overflow-y:auto;'>
	<table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
	$html .= "<tr class='listTr'><th>编号</th>";
	foreach ($titleData as $key=>$value){
		if($titleWidth[$key])
			$html .= "<th width='{$titleWidth[$key]}'>" . $value . "</th>";
		else
			$html .= "<th>" . $value . "</th>";
	}
	$html .= "</tr>";
	$i = 1;

	foreach ($mailData as $sort=>$sqlData)
	{
		$html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
		$html .= "<td>$i</td>";
		$i++;
		foreach ($titleData as $key=>$value){
			$td = $sqlData[$key];
			if($key == 'reward' && $td){
				$html .= "<td><button type='button' class='btn btn-info' id=rewardButton{$sort} name='btn_set' onclick=showReward('"."rewardButton{$sort}','rewardTd{$sort}"."')>展开</button><li id=rewardTd{$sort} style='display:none'>" . $td . "</li></td>";
			}else{
				$html .= "<td>" . $td . "</td>";
			}
		}
		$html .= "</tr>";
	}
	$html .= "</table></div><br/>";
	if($pager['pager'])
		$html .= "<div class='span11' style='TEXT-ALIGN:center;'>" . $pager['pager'] . "</div>";
	echo $html;
	exit;
}
include( renderTemplate("{$module}/{$module}_{$action}") );
?>
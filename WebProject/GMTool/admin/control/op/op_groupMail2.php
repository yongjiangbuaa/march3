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
	$uidStr=$params['uids'];
	$rewardsStr=$params['rewards'];
	$uidStr=str_replace('；', ';', $uidStr);
	$rewardsStr=str_replace('；', ';', $rewardsStr);
	trim($uidStr,';');
	trim($rewardsStr,';');

	$uids=explode(';', $uidStr);
	$rewards=explode(';', $rewardsStr);

	$alldata = array();
	for ($index = 0; $index < count($uids); $index++) {
		if($uids[$index] && $rewards[$index]) {
			$alldata[$uids[$index]] = $rewards[$index];
		}
	}

	if(count($rewards) != count($uids)){
		exit("uids and reards not the same length");
	}else if(!$uids || !$rewards ){
		exit("user or rewards not found");
	}
	else {

		$arruids2 = array_chunk($uids,200);

//		unset($arruids);
		$uidServerArray = array();
		foreach($arruids2 as $key=>$value){//$key是0 1 2 $value 是数组
			$value1 = array_values($value);
//
			$result['ret']['data'] = cobar_getAccountInfoByGameuids($value1);
			foreach ($result['ret']['data'] as $curRow){
				$uidServerArray[$curRow['gameUid']]=$curRow['server'];
			}
		}

		$title = addslashes($params['title']);//
		$contents = addslashes($params['contents']);//
		$sendTime = floor(microtime(true)*1000);
		foreach ($uidServerArray as $uidValue=>$serverKey){
			$toUser = $uidValue;
			$uid = md5($toUser.$serverKey.$uidValue.floor(microtime(true)*1000));
			$reward = $alldata[$uidValue];
			$rewardStatus = 1;
			if($reward){
				$rewardStatus = 0;
			}

			$sql = "INSERT INTO `server_usermail` (`uid`, `toUser`, `sendBy`, `sendTime`, `title`, `contents`, `reward`,`rewardStatus`) VALUES ('$uid', '$toUser', '$sendBy', '$sendTime', '$title', '$contents', '$reward',$rewardStatus)";
			$page->executeServer('s'.$serverKey, $sql, 2);
			$sql = "insert into mail (uid, toUser, fromUser, fromName, status, type, rewardStatus, saveFlag, title, contents, rewardId, createTime, itemIdFlag, reply,srctype) values ('$uid', '$toUser', '', 'system', 0, 13, $rewardStatus, 0, '$title', '$contents', '$reward', $sendTime, 0, 0,4)";
			$page->executeServer('s'.$serverKey, $sql, 2);
			sendReward2($uid,'s'.$serverKey);
			
			adminLogUser ( $adminid, $uidValue, 's'.$serverKey, array (
				'groupMail'=>'add',
				'reward' => $reward,
				'sendTime' => $sendTime
				)
			);
		}
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
}
function sendReward2($mailUid,$serv){
	$page = new BasePage();
	$page->webRequest('sendmail',array('uid'=>$mailUid),$serv);
}

if($showpage > 0)  //前端传入 1
{
	$page_limit = 20;
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

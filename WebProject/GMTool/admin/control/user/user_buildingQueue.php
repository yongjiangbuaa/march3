<?php
!defined('IN_ADMIN') && exit('Access Denied');
$showData = false;
$afterData = false;
$type = $_REQUEST['action'];
if($_REQUEST['ownerId'])
	$ownerId = $_REQUEST['ownerId'];
if($_REQUEST['minusDay'])
	$minusDay = $_REQUEST['minusDay'];
if($type=='view'){
	$sql="select uuid,ownerId,qid,type,startTime,endTime,updateTime from queue where ownerId ='$ownerId' and qid=2 and type=0;";
	$result = $page->execute($sql, 3);
	$curRow=$result['ret']['data'][0];
	$uidTime['endTime']=$curRow['endTime'];
	$uidTime['updateTime']=$curRow['updateTime'];
	$info['endTime'] = ($curRow['endTime']==9223372036854775807?'-':date('Y-m-d',$curRow['endTime']/1000));
	$info['updateTime'] = ($curRow['updateTime']==9223372036854775807?'-':date('Y-m-d',$curRow['updateTime']/1000));
	$showData = true;
	$afterData = false;
}
if($type=='minus'){
	if($_REQUEST['endTime'])
		$endTime =$_REQUEST['endTime'];
	if($_REQUEST['updateTime'])
		$updateTime =$_REQUEST['updateTime'];
	$minusDay=intval($minusDay);
	if($minusDay<=0){
		return ;
	}
	$eTime=$endTime - $minusDay* 24 * 3600 * 1000;
	$nowTime=time()*1000;
	if($eTime>=$nowTime){
		$ret = $page->webRequest('kickuser',array('uid'=>$ownerId));//踢下线
		if($ret == 'ok'){
			if($updateTime=='9223372036854775807'){
				$sql="UPDATE queue SET endTime = $eTime  WHERE ownerId = '$ownerId' and qid=2 and type=0;";
			}else{
				if($eTime>=$updateTime){
					$sql="UPDATE queue SET endTime = $eTime  WHERE ownerId = '$ownerId' and qid=2 and type=0;";
				}
			}
			$result = $page->execute($sql, 2);
		 	$toUser = $ownerId;
			$sendTime = microtime(true)*1000;
			$title = addslashes("Mail on return gold coins");
			$contents = addslashes("My lord,
Sorry for making you buy the second builder for many times because of the network delay.
According to your request,we deducted the build time on extra builders you bought and returned corresponding gold coins. Hope you have fun.
CLash of Kings  studio");
			$mailUid = md5($toUser.$sendTime.$title.$contents.time());
			$gold=$minusDay*125;
			$reward="gold,0,$gold";
			$rewardStatus = 0;
			$sql = "insert into mail (uid, toUser, fromUser, fromName, status, type, rewardStatus, saveFlag, title, contents, rewardId, createTime, itemIdFlag, reply) values ('$mailUid', '$toUser', '', 'system', 0, 13, $rewardStatus, 0, '$title', '$contents', '$reward', $sendTime, 0, 1)";
			$page->execute($sql,2);
			sendReward($mailUid);
			echo  '<script language="JavaScript">alert("已扣除玩家第二建筑队列，且返还玩家对应金币！");</script>'; 
			$sql="select uuid,ownerId,qid,type,startTime,endTime,updateTime from queue where ownerId ='$ownerId' and qid=2 and type=0;";
			$result = $page->execute($sql, 3);
			$curRow=$result['ret']['data'][0];
			$infoAfter['endTime'] = ($curRow['endTime']==9223372036854775807?'-':date('Y-m-d',$curRow['endTime']/1000));
			$infoAfter['updateTime'] = ($curRow['updateTime']==9223372036854775807?'-':date('Y-m-d',$curRow['updateTime']/1000));
	        adminLogUser($adminid,$ownerId,$currentServer,array('minus_buildingqueue'=>$minusDay.' day'));
			$showData = false;
			$afterData = true;
		}
	}else {
		echo  '<script language="JavaScript">alert("第二建筑队列剩余时间不足！");</script>';
	}
}
function sendReward($mailUid){
	$page = new BasePage();
	$page->webRequest('sendmail',array('uid'=>$mailUid));
}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>

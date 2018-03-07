<?php
!defined('IN_ADMIN') && exit('Access Denied');
$showData = false;
$afterData = false;
$type = $_REQUEST['action'];
if($_REQUEST['uid'])
	$uid = $_REQUEST['uid'];
if($_REQUEST['minusHour'])
	$minusHour = $_REQUEST['minusHour'];
if($type=='view'){
	$sql="select uid,score,vipEndTime from user_vip where uid ='$uid';";
	$result = $page->execute($sql, 3);
	$curRow=$result['ret']['data'][0];
	$time=$curRow['vipEndTime'];
	$info['uid'] = $curRow['uid'];
	$info['score'] = $curRow['score'];
	$info['vipEndTime'] = ($curRow['vipEndTime']?date('Y-m-d H',$curRow['vipEndTime']/1000):0);
	$showData = true;
	$afterData = false;
}
if($type=='minus'){
	if($_REQUEST['endTime'])
		$endTime = $_REQUEST['endTime'];
	if ($minusHour<=0){
		return ;
	}
	if($endTime){
		$eTime=$endTime-$minusHour*60*60*1000;
		$sql="UPDATE user_vip SET vipEndTime = $eTime  WHERE uid = '$uid';";
		$ret = $page->webRequest('kickuser',array('uid'=>$uid));
		if($ret == 'ok'){
			$result = $page->execute($sql, 2);
			$sql="select uid,score,vipEndTime from user_vip where uid ='$uid';";
			$result = $page->execute($sql, 3);
			$curRow=$result['ret']['data'][0];
			$infoAfter['uid'] = $curRow['uid'];
			$infoAfter['score'] = $curRow['score'];
			$infoAfter['vipEndTime'] = ($curRow['vipEndTime']?date('Y-m-d H',$curRow['vipEndTime']/1000):0);
            adminLogUser($adminid,$uid,$currentServer,array('minus_viptime'=>$minusHour.' hour'));
			$showData = false;
			$afterData = true;
		}
	}else{
		$output="VIP没有剩余时间!";
	}
}



include( renderTemplate("{$module}/{$module}_{$action}") );
?>
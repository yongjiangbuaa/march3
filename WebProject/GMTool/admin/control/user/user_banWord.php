<?php
/* 
	CREATE TABLE IF NOT EXISTS `banWordRecord` (
	`uid` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
	`server` int(4) NOT NULL,
	`banTimes` int(4) DEFAULT 0,
	PRIMARY KEY (`uid`, `server`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
 */

//exit('TODO.');

!defined('IN_ADMIN') && exit('Access Denied');
include ADMIN_ROOT . '/language/alertMail.php';
$type = $_REQUEST['action'];
if($_REQUEST['useruid'])
	$useruid = $_REQUEST['useruid'];

function sendReward($mailUid){
	$page = new BasePage();
	$page->webRequest('sendmail',array('uid'=>$mailUid));
}
if ($type=='process') {
	$bandreason = $_REQUEST['userreason'];
	$banTimes=-1;
	
	if(substr($currentServer, 0,1)=='s'){
		$serverId=substr($currentServer, 1);
	}else{
		$serverId=1;
	}
	$time=time()*1000;
	$sendBy = $page->getAdmin();
	$sql="insert into banWordRecord(uid,server) values('$useruid',$serverId) ON DUPLICATE KEY UPDATE banTimes=banTimes+1, operator='$sendBy', opTime=$time, bandreason='$bandreason';";
	$result = $page->globalExecute($sql, 2,true);
	if($result['error']){
		$html="<span><strong>banWordRecord表插入失败</strong></span>";
	}
	$sql="select * from banWordRecord where uid='$useruid' and server=$serverId;";
	$result = $page->globalExecute($sql, 3);
	if($result['error']){
		$html="<span><strong>禁言次数查询失败</strong></span>";
	}
	//print_r($result);
	$banTimes=$result['ret']['data'][0]['banTimes'];
	$html="<span><strong>玩家:$useruid,第".($banTimes+1)."次警告操作完毕</strong></span>";
	adminLogUser($adminid,$useruid,$currentServer,array('banTimes'=>$bantime));

	$sql="select lang from userprofile where uid='$useruid';";
    $ret=$page->execute($sql, 3);
    $lang='';
    if (!$ret['error'] && $ret['ret']['data']){
    		$lang=$ret['ret']['data'][0]['lang'];
   	}
   	if (empty($lang) || (!isset($contentsArray[$lang]))){
   		$lang='en';
   	}
    //1次禁言警告,2次禁言3天, 3次禁言7天  4次封号1天  5次封号3天  6次封号5天  7次封号5天
    
	$toUser = $useruid;
	$sendTime = microtime(true)*1000;
	if($banTimes==0){
		$tmp = "update banWordRecord set operator='$sendBy', opTime=$time ,bandreason='$bandreason' where uid='$useruid' and server=$serverId;";
		$page->globalExecute($tmp, 2,true);
		$title = addslashes($titleArray[$lang]['0']);
		$contents = addslashes($contentsArray[$lang]['0']);
		$uid = md5($toUser.$serverId.$banTimes.time());
		$sql = "INSERT INTO `server_usermail` (`uid`, `toUser`, `sendBy`, `sendTime`, `title`, `contents`) VALUES ('$uid', '$toUser', '$sendBy', '$sendTime', '$title', '$contents')";
		$result=$page->execute($sql, 2,true);
		$sql = "insert into mail (uid, toUser, fromUser, fromName, status, type, rewardStatus, saveFlag, title, contents, createTime, itemIdFlag, reply,srctype) values ('$uid', '$toUser', '', 'system', 0, 13, 0, 0, '$title', '$contents', $sendTime, 0, 1,104)";
		$result2=$page->execute($sql,2,true);
		sendReward($uid);
		if($result['error'] || $result2['error']){
			$html="<span><strong>警告邮件发送失败</strong></span>";
		}
	}else if($banTimes==1){
		$title = addslashes($titleArray[$lang]['0']);
		$contents = addslashes($contentsArray[$lang]['1']);
		$uid = md5($toUser.$serverId.$banTimes.time());
		$sql = "INSERT INTO `server_usermail` (`uid`, `toUser`, `sendBy`, `sendTime`, `title`, `contents`) VALUES ('$uid', '$toUser', '$sendBy', '$sendTime', '$title', '$contents');";
		$result=$page->execute($sql, 2,true);
		$sql = "insert into mail (uid, toUser, fromUser, fromName, status, type, rewardStatus, saveFlag, title, contents, createTime, itemIdFlag, reply,srctype) values ('$uid', '$toUser', '', 'system', 0, 13, 0, 0, '$title', '$contents', $sendTime, 0, 1,104);";
		$result2=$page->execute($sql,2,true);
		sendReward($uid);
		if($result['error'] || $result2['error']){
			$html="<span><strong>警告邮件发送失败</strong></span>";
		}else {
			$nowTime=(time()+3*86400)*1000;
			$sql="select chatBanTime from userprofile where uid='$useruid';";
			$result=$page->execute($sql, 3);
			$value=$result['ret']['data'][0]['chatBanTime']+3*86400000;
			$chatBanTime=max($value,$nowTime);
			$sql="update userprofile set chatBanTime=$chatBanTime where uid='$useruid'";
			$result=$page->execute($sql, 2,true);
			if($result['error']){
				$html="<span><strong>禁言操作失败</strong></span>";
			}else{
				$ret = $page->webRequest('gmchatban',array('uid'=>$useruid, 'time'=>3*86400000, 'gmName'=>$sendBy, 'reason'=>$bandreason, "content"=>"0"),$currentServer);
			}
		}
	}else if($banTimes==2){
		$title = addslashes($titleArray[$lang]['0']);//
		$contents = addslashes($contentsArray[$lang]['2']);//
		$uid = md5($toUser.$serverId.$banTimes.time());
		$sql = "INSERT INTO `server_usermail` (`uid`, `toUser`, `sendBy`, `sendTime`, `title`, `contents`) VALUES ('$uid', '$toUser', '$sendBy', '$sendTime', '$title', '$contents')";
		$result=$page->execute($sql, 2,true);
		$sql = "insert into mail (uid, toUser, fromUser, fromName, status, type, rewardStatus, saveFlag, title, contents, createTime, itemIdFlag, reply,srctype) values ('$uid', '$toUser', '', 'system', 0, 13, 0, 0, '$title', '$contents', $sendTime, 0, 1,104)";
		$result2=$page->execute($sql,2,true);
		sendReward($uid);
		if($result['error'] || $result2['error']){
			$html="<span><strong>警告邮件发送失败</strong></span>";
		}else{
			$nowTime=(time()+7*86400)*1000;
			$sql="select chatBanTime from userprofile where uid='$useruid';";
			$result=$page->execute($sql, 3);
			$value=$result['ret']['data'][0]['chatBanTime']+7*86400000;
			$chatBanTime=max($value,$nowTime);
			$sql="update userprofile set chatBanTime=$chatBanTime where uid='$useruid'";
			$result=$page->execute($sql, 2,true);
			if($result['error']){
				$html="<span><strong>禁言操作失败</strong></span>";
			}else{
				$ret = $page->webRequest('gmchatban',array('uid'=>$useruid, 'time'=>7*86400000, 'gmName'=>$sendBy, reason=>$bandreason, "content"=>"0"),$currentServer);
			}
		}
	}else if($banTimes==3){
		$title = addslashes($titleArray[$lang]['0']);//
		$contents = addslashes($contentsArray[$lang]['3']);//
		$uid = md5($toUser.$serverId.$banTimes.time());
		$sql = "INSERT INTO `server_usermail` (`uid`, `toUser`, `sendBy`, `sendTime`, `title`, `contents`) VALUES ('$uid', '$toUser', '$sendBy', '$sendTime', '$title', '$contents')";
		$result=$page->execute($sql, 2,true);
		$sql = "insert into mail (uid, toUser, fromUser, fromName, status, type, rewardStatus, saveFlag, title, contents, createTime, itemIdFlag, reply,srctype) values ('$uid', '$toUser', '', 'system', 0, 13, 0, 0, '$title', '$contents', $sendTime, 0, 1,104)";
		$result2=$page->execute($sql,2,true);
		sendReward($uid);
		if($result['error'] || $result2['error']){
			$html="<span><strong>警告邮件发送失败</strong></span>";
		}else{
			$banTime=(time()+86400)*1000;
			$sql="update userprofile set banTime=$banTime where uid='$useruid'";
			$result=$page->execute($sql, 2,true);
			if($result['error']){
				$html="<span><strong>封号操作失败</strong></span>";
			}else{
				$ret = $page->webRequest('gmchatban',array('uid'=>$useruid, 'time'=>1*86400000, 'gmName'=>$sendBy, reason=>$bandreason, "content"=>"1"),$currentServer);
			}
		}
	}else if($banTimes==4){
		$title = addslashes($titleArray[$lang]['0']);//
		$contents = addslashes($contentsArray[$lang]['4']);//
		$uid = md5($toUser.$serverId.$banTimes.time());
		$sql = "INSERT INTO `server_usermail` (`uid`, `toUser`, `sendBy`, `sendTime`, `title`, `contents`) VALUES ('$uid', '$toUser', '$sendBy', '$sendTime', '$title', '$contents')";
		$result=$page->execute($sql, 2,true);
		$sql = "insert into mail (uid, toUser, fromUser, fromName, status, type, rewardStatus, saveFlag, title, contents, createTime, itemIdFlag, reply,srctype) values ('$uid', '$toUser', '', 'system', 0, 13, 0, 0, '$title', '$contents', $sendTime, 0, 1,104)";
		$result2=$page->execute($sql,2,true);
		sendReward($uid);
		if($result['error'] || $result2['error']) {
			$html = "<span><strong>警告邮件发送失败</strong></span>";
		} else {
			$banTime=(time()+3*86400)*1000;
			$sql="update userprofile set banTime=$banTime where uid='$useruid'";
			$result=$page->execute($sql, 2,true);
			if($result['error']){
				$html="<span><strong>封号操作失败</strong></span>";
			}else{
				$html="<span><strong>++++++++++++++++++++</strong></span>";
				$ret = $page->webRequest('gmchatban',array('uid'=>$useruid, 'time'=>3*86400000, 'gmName'=>$sendBy, reason=>$bandreason, "content"=>"1"),$currentServer);
			}
	}
}else if($banTimes>=5){
		$title = addslashes($titleArray[$lang]['0']);//
		$contents = addslashes($contentsArray[$lang]['4']);//
		$uid = md5($toUser.$serverId.$banTimes.time());
		$sql = "INSERT INTO `server_usermail` (`uid`, `toUser`, `sendBy`, `sendTime`, `title`, `contents`) VALUES ('$uid', '$toUser', '$sendBy', '$sendTime', '$title', '$contents')";
		$result=$page->execute($sql, 2,true);
		$sql = "insert into mail (uid, toUser, fromUser, fromName, status, type, rewardStatus, saveFlag, title, contents, createTime, itemIdFlag, reply,srctype) values ('$uid', '$toUser', '', 'system', 0, 13, 0, 0, '$title', '$contents', $sendTime, 0, 1,104)";
		$result2=$page->execute($sql,2,true);
		sendReward($uid);
		if($result['error'] || $result2['error']){
			$html="<span><strong>警告邮件发送失败</strong></span>";
		}else {
			$banTime=(time()+5*86400)*1000;
			$sql="update userprofile set banTime=$banTime where uid='$useruid'";
			$result=$page->execute($sql, 2,true);
			if($result['error']){
				$html="<span><strong>封号操作失败</strong></span>";
			}else{
				$ret = $page->webRequest('gmchatban',array('uid'=>$useruid, 'time'=>5*86400000, 'gmName'=>$sendBy, reason=>$bandreason, "content"=>"1"),$currentServer);
			}
		}
	}
}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>
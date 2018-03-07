<?php
define('IN_ADMIN',true);
define('ADMIN_ROOT', realpath(dirname(__FILE__) . '/../../'));
include ADMIN_ROOT.'/config.inc.php';
include ADMIN_ROOT.'/servers.php';
include ADMIN_ROOT.'/admins.php';
ini_set('mbstring.internal_encoding','UTF-8');
includeModel("BasePage");
global $servers;
set_time_limit(0);

//插入待发奖励
$page = new BasePage();
$task = $argv[1]?$argv[1]:0;
$taskType = $argv[2]?$argv[2]:0;

switch ($task){
// 	case 1://攻打别人帝国50000粮食
// 		$sql = "select * from (select r.*,t.id from userprofile u inner join stat_reg r on u.uid = r.uid inner join user_task t on r.uid = t.uid where (u.pf='nstore' or r.pf= 'nstore') and state > 0 and (t.id=2206601 or t.id=2206801 or t.id=2206901)) a group by uid having count(1) = 3";
// 		$sql = "select r.* from userprofile u inner join stat_reg r on u.uid = r.uid inner join user_task t on r.uid = t.uid where (u.pf='nstore' or r.pf= 'nstore') and state > 0 and t.id=2206601";
// 		$type = 'nstore1';
// 		$reward = 'food,0,50000';
// 		break;
// 	case 2://攻打怪物50000木材
// 		$sql = "select r.* from userprofile u inner join stat_reg r on u.uid = r.uid inner join user_task t on r.uid = t.uid where (u.pf='nstore' or r.pf= 'nstore') and state > 0 and t.id=2206801";
// 		$type = 'nstore2';
// 		$reward = 'wood,0,50000';
// 		break;
// 	case 3://加入联盟 5个1小时加速卡
// 		$sql = "select r.* from userprofile u inner join stat_reg r on u.uid = r.uid inner join alliance_member m on r.uid = m.uid where (u.pf='nstore' or r.pf= 'nstore')";
// 		$type = 'nstore3';
// 		$reward = 'goods,104000,5';
// 		break;
	case 4://20150903-20150909期间登陆
		$sql = "select distinct(uid) uid from stat_login_2015_8 where time > 1441206000000 and time < 1441810800000 and pf='tstore'";
		$type = '20150914_tstore_login';
		/**
		小材料宝箱*1
		5分钟加速卡*4
		 */
		$reward = 'goods,200600,1|goods,200201,4';
		break;
	case 5://20150903-20150909期间充值达到档位<10,000韩元
		$sql = "select * from (select *,sum(paid) paytotal from paylog where time > 1441206000000 and time < 1441810800000 and pf='tstore' group by uid) a where paytotal < 10000";
		$type = '20150914_tstore_pay_1';
		/**
		小材料宝箱*1 
		行军加速25%*1 
		50000粮食*2 
		 */
		$reward = 'goods,200600,1|goods,200220,1|goods,200331,2';
		break;
	case 6://20150903-20150909期间充值达到档位10,000韩元
		$sql = "select * from (select *,sum(paid) paytotal from paylog where time > 1441206000000 and time < 1441810800000 and pf='tstore' group by uid) a where paytotal >= 10000 and paytotal < 100000";
		$type = '20150914_tstore_pay_2';
		/**
		小材料宝箱*2 
		行军加速25%*1 
		1小时加速*4 
		喇叭*1 
		 */
		$reward = 'goods,200600,2|goods,200220,1|goods,200200,4|goods,200011,1';
		break;
	case 7://20150903-20150909期间充值达到档位100,000韩元
		$sql = "select * from (select *,sum(paid) paytotal from paylog where time > 1441206000000 and time < 1441810800000 and pf='tstore' group by uid) a where paytotal >= 100000 and paytotal < 200000";
		$type = '20150914_tstore_pay_3';
		/**
		小材料宝箱*2 
		行军加速25%*2 
		12h防御加成*1 
		12h攻击加成*1 
		喇叭*1 
		50HP*1 
		 */
		$reward = 'goods,200600,2|goods,200220,2|goods,200416,1|goods,200414,1|goods,200011,1|goods,200381,1';
		break;
	case 8://20150903-20150909期间充值达到档位200,000韩元
		$sql = "select * from (select *,sum(paid) paytotal from paylog where time > 1441206000000 and time < 1441810800000 and pf='tstore' group by uid) a where paytotal >= 200000";
		$type = '20150914_tstore_pay_4';
		/**
		小材料宝箱*2 
		行军加速25%*2 
		12h防御加成*2 
		12h攻击加成*2 
		50000木材*2 
		50000粮食*2 
		改头像*1 
		 */
		$reward = 'goods,200600,2|goods,200220,2|goods,200416,2|goods,200414,2|goods,200301,2|goods,200331,2|goods,200026,1';
		break;
	default:
		exit('no task');
		break;
}
if ($taskType == 1){
	echo "\n";
	$insertHead = "insert into specialreward (`gameuid`, `type`) values";
	$insertArray = array();
	foreach ($servers as $server=>$serverInfo){
		echo $server."\n";
		$sqlData = $page->executeServer($server,$sql,3,false);
		$rewardList = array();
		foreach ($sqlData['ret']['data'] as $curRow){
			$rewardList[$curRow['uid']] = $curRow['uid'];
		}
		foreach ($rewardList as $gameuid){
			$insertArray[] = "'$gameuid','$type'";
			if(count($insertArray) >= 100){
				$insertSql = $insertHead.'('.implode('),(', $insertArray).') on duplicate key update state = state';
				$page->executeServer('global',$insertSql,1,true);
				$insertArray = array();
			}
		}
	}
	if($insertArray){
		$insertSql = $insertHead.'('.implode('),(', $insertArray).') on duplicate key update state = state';
		$page->executeServer('global',$insertSql,1,true);
	}
}else if ($taskType == 2){
	//根据uid发奖
	$sendTime = floor(microtime(true)*1000);
	$sqlData = $page->executeServer('global',"select * from specialreward where type = '$type' and state = 0 limit 1000",3,true);
	foreach ($sqlData['ret']['data'] as $curRow){
		$gameuid = $curRow['gameuid'];
		$gameAccount = null;
		$accountInfo = cobar_getAccountInfoByGameuids($gameuid);
		$gameAccount = $accountInfo[0];
		if($gameAccount){
			$toUser = $gameAccount['gameUid'];
			$title = '‘T스토어/ 올레마켓/ U+스토어’이벤트 보너스';
			$contents = '안녕하세요.
T스토어/ 올레마켓/ U+스토어’이벤트에 참여 해주셔서 감사드립니다. 이벤트 보너스의 발송 방법이 변경되여 불편이 있으셨다면 사과 드립니다. 아래 이벤트 보너스 받으시고 즐거운 게임 플레이가 되시길 바랍니다.감사합니다. ';
			$uid = getGUID();
			$newMail = "insert into mail (uid, toUser, fromUser, fromName, status, type, rewardStatus, saveFlag, title, contents, rewardId, createTime, itemIdFlag, reply) values ('$uid', '$toUser', '', '', 0, 13, 0, 0, '$title', '$contents', '$reward', $sendTime, 0, 0)";
			$page->executeServer(getServer($gameAccount['server']),$newMail,1,true);
			$page->webRequest('sendmail',array('uid'=>$uid),getServer($gameAccount['server']));
			$page->executeServer('global',"update specialreward set state=1,sendTime=$sendTime where gameuid = '$gameuid' and type ='$type'",1,true);
		}
	}
}

function getServer($serverId){
	return 's'.$serverId;
// 	return 'localhost';
}
function getGUID() {
	$ip = "127001";
	$unknown = 'unknown';
	if ( isset($_SERVER['HTTP_X_FORWARDED_FOR'])
	&& $_SERVER['HTTP_X_FORWARDED_FOR']
	&& strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'],
			$unknown) ) {
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	}
	elseif ( isset($_SERVER['REMOTE_ADDR'])
			&& $_SERVER['REMOTE_ADDR'] &&
			strcasecmp($_SERVER['REMOTE_ADDR'], $unknown) ) {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	$ip = str_replace(".","", $ip);
	$ip = str_replace(",","", $ip);
	$ip = trim($ip);
	return uniqid($ip.'COK');
}
?>

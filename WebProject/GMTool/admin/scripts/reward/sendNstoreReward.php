<?php
define('IN_ADMIN',true);
define('ADMIN_ROOT', realpath(dirname(__FILE__) . '/../../'));
include ADMIN_ROOT.'/config.inc.php';
include ADMIN_ROOT.'/servers.php';
ini_set('mbstring.internal_encoding','UTF-8');
includeModel("BasePage");
global $servers;
set_time_limit(0);

//插入待发奖励
$page = new BasePage();
$task = $argv[1]?$argv[1]:0;
$ttype = $argv[2]?$argv[2]:0;

switch ($task){
	case 1://攻打别人帝国50000粮食
		$sql = "select * from (select r.*,t.id from userprofile u inner join stat_reg r on u.uid = r.uid inner join user_task t on r.uid = t.uid where (u.pf='nstore' or r.pf= 'nstore') and state > 0 and (t.id=2206601 or t.id=2206801 or t.id=2206901)) a group by uid having count(1) = 3";
		$sql = "select r.* from userprofile u inner join stat_reg r on u.uid = r.uid inner join user_task t on r.uid = t.uid where (u.pf='nstore' or r.pf= 'nstore') and state > 0 and t.id=2206601";
		$type = 'nstore1';
		$reward = 'food,0,50000';
		break;
	case 2://攻打怪物50000木材
		$sql = "select r.* from userprofile u inner join stat_reg r on u.uid = r.uid inner join user_task t on r.uid = t.uid where (u.pf='nstore' or r.pf= 'nstore') and state > 0 and t.id=2206801";
		$type = 'nstore2';
		$reward = 'wood,0,50000';
		break;
	case 3://加入联盟 5个1小时加速卡
		$sql = "select r.* from userprofile u inner join stat_reg r on u.uid = r.uid inner join alliance_member m on r.uid = m.uid where (u.pf='nstore' or r.pf= 'nstore')";
		$type = 'nstore3';
		$reward = 'goods,104000,5';
		break;
	default:
		exit('no task');
		break;
}
if ($ttype == 1){
	$rewardList = array();
	echo "\n";
	foreach ($servers as $server=>$serverInfo){
		echo $server."\n";
		$sqlData = $page->executeServer($server,$sql,3,true);
		foreach ($sqlData['ret']['data'] as $curRow){
			$rewardList[$curRow['uid']] = $curRow['uid'];
		}
	}
	$insertHead = "insert into specialreward (`gameuid`, `type`) values";
	$insertArray = array();
	foreach ($rewardList as $gameuid){
		$insertArray[] = "'$gameuid','$type'";
		if(count($insertArray) >= 100){
			$insertSql = $insertHead.'('.implode('),(', $insertArray).') on duplicate key update state = state';
			$page->executeServer('global',$insertSql,1,true);
			$insertArray = array();
		}
	}
	if($insertArray){
		$insertSql = $insertHead.'('.implode('),(', $insertArray).') on duplicate key update state = state';
		$page->executeServer('global',$insertSql,1,true);
	}
}else if ($ttype == 2){
	//根据uid发奖
	$sendTime = floor(microtime(true)*1000);
	$sqlData = $page->executeServer('global',"select * from specialreward where type = '$type' and state = 0 limit 1000",3,true);
	foreach ($sqlData['ret']['data'] as $curRow){
		$gameuid = $curRow['gameuid'];
		$gameAccount = null;
		$accountSqlData = $page->executeServer('global',"select * from account_new where gameuid = '$gameuid' limit 1",1,true);
		$gameAccount = $accountSqlData['ret']['data'][0]; 
		if($gameAccount){
			$toUser = $gameAccount['gameUid'];
			$title = '네이버 출시 이벤트';
			$contents = 'C.O.K 엔스토어 출시이벤트 보상 드립니다. ';
			$uid = getGUID();
			$newMail = "insert into mail (uid, toUser, fromUser, fromName, status, type, rewardStatus, saveFlag, title, contents, rewardId, createTime, itemIdFlag, reply) values ('$uid', '$toUser', '', 'system', 0, 13, 0, 0, '$title', '$contents', '$reward', $sendTime, 1, 0)";
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

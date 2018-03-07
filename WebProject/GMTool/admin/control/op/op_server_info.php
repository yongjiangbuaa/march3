<?php
!defined('IN_ADMIN') && exit('Access Denied');

$title = "活动时间配置";
global $servers;

////////
$selectedServers = array();

$host = gethostbyname(gethostname());
if ($host == 'IPIPIP' || $host == 'IPIPIP') {
	$selectedServers[] = 'localhost';
	$selectedServers[] = 'localhost2';
	$selectedServers[] = 's1';
	$selectedServers[] = 's2';
}elseif ($host == 'IPIPIP'){
	$selectedServers[] = 'test';
	$selectedServers[] = 's0';
	$selectedServers[] = 's999001';
}else {
	$maxServer=0;
	foreach ($servers as $server=>$serverInfo){
		if(substr($server, 0 ,1) != 's'){
		 continue;
		}
		if (substr($server,1)>900000){
			continue;
		}
		$maxServer=max($maxServer,substr($server,1));
	}
	$sttt = $_REQUEST['selectServer'];
	if (!empty($sttt)) {
		$sttt = str_replace('，', ',', $sttt);
		$sttt = str_replace(' ', '', $sttt);
		$tmp = explode(',', $sttt);
		foreach ($tmp as $tt) {
			$tt = trim($tt);
			if (!empty($tt)) {
				if(strstr($tt,'-')){
					$ttArray=explode('-', $tt);
					$min=min($ttArray[1],$maxServer);
					for ($i=$ttArray[0];$i<=$min;$i++){
						$selectedServers[$i] = 's'.$i;
						$all_sid[$i] = $i;
					}
				}else {
					if($tt<=$maxServer){
						$selectedServers[$tt] = 's'.$tt;
						$all_sid[$tt] = $tt;
					}
				}
			}
		}
	}else{
		$client = new Redis();
		$client->connect(GLOBAL_REDIS_SERVER_IP);
		$serverRatioConf = $client->get("RATIO_OF_CHOOSE_SERVER");
		if (!empty($serverRatioConf)) {
			$serverConfArr = explode(';',$serverRatioConf);
			foreach($serverConfArr as $serverItem) {
				$idRatioArr = explode(':', $serverItem);
				$keyList[] = $idRatioArr[0];
				$selectedServers[$idRatioArr[0]] = 's'.$idRatioArr[0];
			}
			$defaultselectServer = min($keyList).'-'.max($keyList);
		}
		$sttt = $defaultselectServer;
	}
	krsort($selectedServers, SORT_NUMERIC);
}
//批量修改的配置
$header= array(
	'kaifu'=>'开服时间',
	'yangfu'=>'养服时间',
	'daoliangStart'=>'导量开始时间',
	'activityTime'=>'积分开始时间',
	'alactTime'=>'联盟积分开始时间',
	'shuaguaiActStart'=>'刷怪开始时间',
	'activationTime'=>'兑换码结束时间',
	'110004' => '怪物攻城',
	'110001' => '王位争夺战',
	'payTotalTime'=>'累计充值开始时间',
//	'crossFightServerId'=>'跨服战开战服',
	'110007' => '跨服战开始时间',
	'roseCrownStart'=>'玫瑰花冠开始时间',
	'110008' => '祝福活动',
	'110009' => '奖励排行活动',
	'110010' => '英雄招募排行',
	'strongholdStart'=>'小王战',
	//110012被一元云购活动占用，参加Java代码内的OneCloudPayActivity类文件
);
///////

if ($_REQUEST['event']=='getWarServerIdArray'){
	$sql="select id from server_info where type=2;";
	$result=$page->globalExecute($sql, 3);
	$idsStr='';
	foreach ($result['ret']['data'] as $row){
		$idsStr.=$row['id'].'|';
	}
	$idsStr=trim($idsStr,'|');
	exit($idsStr);
}
//批量更新时间
if($_REQUEST['type'] == 'batchDo')
{
	$erversAndSidsArr=getSelectServersAndSids($_REQUEST['selectServer']);
	$selectServer1=$erversAndSidsArr['withS'];
	$columnName=$_REQUEST['columnName'];//选项
	$modifyActName = $columnName;
	if(!$_REQUEST['columnValue']){
		exit('时间为空!!');
	}
	$columnValue=$_REQUEST['columnValue'];//设置的时间
	$newDateTime = strtotime($columnValue)*1000;
	switch ($columnName) {
		case 110001:
			$modifySql = "insert into activity(id,name,type,openTime) values ($columnName,'wangweizhengduozhan',0,$newDateTime)
			ON DUPLICATE KEY update openTime = $newDateTime";
			break;
		case 110004:
			$modifySql = "insert into activity(id,name,type,openTime) values ($columnName,'MonsterSiege',4,$newDateTime)
			ON DUPLICATE KEY update openTime = $newDateTime";
			break;
		case 110007:
			$modifySql = "insert into activity(id,name,type,openTime) values ($columnName,'CrossKingdomFight',7,$newDateTime)
			ON DUPLICATE KEY update openTime = $newDateTime";
			break;
		case 110008:
			$modifySql = "insert into activity(id,name,type,openTime,startTime) values ($columnName,'BlessActivity',8,$newDateTime,$newDateTime)
			ON DUPLICATE KEY update openTime = $newDateTime,startTime = $newDateTime";
			break;
		case 110009:
			$modifySql = "insert into activity(id,name,type,openTime,startTime) values ($columnName,'rewardrank',9,$newDateTime,$newDateTime)
			ON DUPLICATE KEY update openTime = $newDateTime,startTime = $newDateTime";
			break;
		case 110010:
			$modifySql = "insert into activity(id,name,type,openTime,startTime) values ($columnName,'HeroEmploy',10,$newDateTime,$newDateTime)
			ON DUPLICATE KEY update openTime = $newDateTime,startTime = $newDateTime";
			break;
		default:
			$modifySql = "insert into server_info(uid,$modifyActName) values ('server',$newDateTime)
				ON DUPLICATE KEY update $modifyActName = $newDateTime";
			break;
	}
	$i=0;
	echo $modifySql.PHP_EOL;
	foreach ($selectServer1 as $server=>$servInfo){
		$result = $page->executeServer($server,$modifySql,2);
		if(!$result['error'] && $result['ret']['result']==1){
			$i+=1;
		}else{
			echo print_r($result.true).PHP_EOL;
		}
	}
	$selectServer2 = $erversAndSidsArr['onlyNum'];
	$selectServer2 = implode(',',$selectServer2);
	$detail = $modifySql.'____'.$selectServer2;
	adminLogSystem($adminid,$detail);

	exit('执行个数:'.$i);
}

//更新时间
if($_REQUEST['type'] == 'modify')
{
	$serverTemp = $_REQUEST['server'];
	$temp = explode('_', $serverTemp, 2);
	$modifyServer = $temp[0];
	$modifyActName = $temp[1];
	$newDateTime = strtotime($_REQUEST['newDate'])*1000;
	$tok = explode('_', $modifyActName);
	if (count($tok) == 3 && $tok[0]=='activity') {
		$actId = $tok[1];
		$modifyActName = $tok[2];
		if($actId==110001){
			$modifySql = "insert into activity(id,name,type,openTime) values ($actId,'wangweizhengduozhan',0,$newDateTime)
			ON DUPLICATE KEY update $modifyActName = $newDateTime";
		}
		if($actId==110004){
			$modifySql = "insert into activity(id,name,type,openTime) values ($actId,'MonsterSiege',4,$newDateTime)
			ON DUPLICATE KEY update $modifyActName = $newDateTime";
		}
		if($actId==110007){
			$modifySql = "insert into activity(id,name,type,openTime) values ($actId,'CrossKingdomFight',7,$newDateTime)
			ON DUPLICATE KEY update $modifyActName = $newDateTime";
		}
		if($actId==110008){
			$modifySql = "insert into activity(id,name,type,openTime,startTime) values ($actId,'BlessActivity',8,$newDateTime,$newDateTime)
			ON DUPLICATE KEY update $modifyActName = $newDateTime,openTime = $newDateTime";
		}
		if($actId==110009){
			$modifySql = "insert into activity(id,name,type,openTime,startTime) values ($actId,'rewardrank',9,$newDateTime,$newDateTime)
			ON DUPLICATE KEY update $modifyActName = $newDateTime,openTime = $newDateTime";
		}
		if($actId==110010){
			$modifySql = "insert into activity(id,name,type,openTime,startTime) values ($actId,'HeroEmploy',10,$newDateTime,$newDateTime)
			ON DUPLICATE KEY update $modifyActName = $newDateTime,openTime = $newDateTime";
		}

	}else{
		//cok老代码注释掉  added by qinbin
//		if($modifyActName=='yangfu'){
//			$activityTime=$newDateTime+86400000*3;
//			$shuaguaiActStart=$newDateTime+86400000*2;
//			$openTime=$newDateTime+86400000*55;
//			$kingSql="insert into activity(id,name,type,openTime) values ('110001','wangweizhengduozhan',0,$openTime)
//			ON DUPLICATE KEY update openTime = $openTime";
//			$kingResult = $page->executeServer($modifyServer,$kingSql,2, true);
//			$modifySql = "insert into server_info(uid,yangfu,daoliangStart,activityTime,shuaguaiActStart) values ('server',$newDateTime,$newDateTime,$activityTime,$shuaguaiActStart)
//			ON DUPLICATE KEY update yangfu = $newDateTime,daoliangStart=$newDateTime,activityTime=$activityTime,shuaguaiActStart=$shuaguaiActStart";
//		}else {
			if ($modifyActName=='crossFightServerId'){
				$modifySql = "insert into server_info(uid,$modifyActName) values ('server',".$_REQUEST['newDate'].") ON DUPLICATE KEY update $modifyActName = ".$_REQUEST['newDate'];
				if (substr($modifyServer,0,1)!='s'){
					return ;
				}
				$id=substr($modifyServer, 1);
				$globalSql = "insert into server_info(id,type,cross_fight_server_id) values($id,0,".$_REQUEST['newDate'].") ON DUPLICATE KEY update type =0,cross_fight_server_id = ".$_REQUEST['newDate'];
			}else {//其它都走这
				$modifySql = "insert into server_info(uid,$modifyActName) values ('server',$newDateTime)
				ON DUPLICATE KEY update $modifyActName = $newDateTime";
			}
//		}
	}
	if($modifySql){
		$result = $page->executeServer($modifyServer,$modifySql,2);
		$page->globalExecute($globalSql, 2);
	}
	$detail = $modifySql.'======'.$globalSql;
	adminLogSystem($adminid,$detail);
}

$allServerAct =array();

// id => type
$activity_config = array(
		110001 => 0,//王位争夺战
		110004 => 4,//怪物攻城
		110007 => 7,//跨服战
		110008 => 8,//祝福活动
		110009 => 9,//奖励排行
		110010 => 10,//英雄招募排行
);

$sql_server_info = "select * from server_info where uid='server'";
$sql_actvity = "select id, type, name, openTime, startTime, endTime from activity";

$now = time() * 1000;
foreach ($selectedServers as $server){
	if(substr($server, 0 ,1) != 's' && strpos($server, 'test')===false && strpos($server, 'localhost')===false){
		continue;
	}
	$result = $page->executeServer($server,$sql_server_info,3,true);//这里面每个服 只有一条数据
	if(empty($result)){
		continue;
	}

	$curRow = $result['ret']['data'][0];
	$allServerAct[$server]['activityTime'] = date('Y-m-d',$curRow['activityTime']/1000);
	$allServerAct[$server]['activationTime'] = date('Y-m-d',$curRow['activationTime']/1000);
	$allServerAct[$server]['kaifu'] = date('Y-m-d H:i:s',$curRow['kaifu']/1000);
	$allServerAct[$server]['yangfu'] = date('Y-m-d',$curRow['yangfu']/1000);
	$allServerAct[$server]['daoliangStart'] = date('Y-m-d',$curRow['daoliangStart']/1000);
	$allServerAct[$server]['daoliangEnd'] = date('Y-m-d',$curRow['daoliangEnd']/1000);
	$allServerAct[$server]['shuaguaiActStart'] = date('Y-m-d',$curRow['shuaguaiActStart']/1000);
	$allServerAct[$server]['payTotalTime'] = date('Y-m-d',$curRow['payTotalTime']/1000);
	$allServerAct[$server]['crossFightServerId'] = $curRow['crossFightServerId']?$curRow['crossFightServerId']:0;
	$allServerAct[$server]['roseCrownStart'] = date('Y-m-d',$curRow['roseCrownStart']/1000);
	$allServerAct[$server]['alactTime'] = date('Y-m-d',$curRow['alactTime']/1000);
	$allServerAct[$server]['strongholdStart'] = date('Y-m-d',$curRow['strongholdStart']/1000);

	$result_activity = $page->executeServer($server,$sql_actvity,3,true); //activity表
	foreach ($result_activity['ret']['data'] as $row) {
		foreach ($activity_config as $id => $type) {
			if ($row['id'] == $id && $row['type'] == $type) {
				$allServerAct[$server]["activity_{$id}_openTime"] = date('Y-m-d',$row['openTime']/1000);
				$allServerAct[$server]["activity_{$id}_startTime"] = date('Y-m-d H:i:s',$row['startTime']/1000);
				$allServerAct[$server]["activity_{$id}_endTime"] = date('Y-m-d H:i:s',$row['endTime']/1000);
				if($now >= $row['startTime'] && $now <= $row['endTime']){
					$allServerAct[$server]["activity_{$id}_status"] = "fighting";
				}else{
					$allServerAct[$server]["activity_{$id}_status"] = "protect";
				}
				break;
			}
		}
	}


	foreach ($activity_config as $id => $type) {
		if (!isset($allServerAct[$server]["activity_{$id}_openTime"])) {
			$allServerAct[$server]["activity_{$id}_openTime"] = date('Y-m-d', 0);
			$allServerAct[$server]["activity_{$id}_startTime"] = date('Y-m-d H:i:s',0);
			$allServerAct[$server]["activity_{$id}_endTime"] = date('Y-m-d H:i:s',0);
			$allServerAct[$server]["activity_{$id}_status"] = "preparation";
		}
	}
}
if($_REQUEST['type']=='modify'){
	if($modifyActName == 'log' ) {
		$id = substr($modifyServer, 1);
		$sql = "INSERT into server_info_log (sid,log) VALUES (" . $id . "," . "'" . $_REQUEST['newDate'] . "'" . ") ";
		$sql .= " ON DUPLICATE KEY UPDATE sid=" . $id . " ,log=" . "'" . $_REQUEST['newDate'] . "'";
		$page->globalExecute($sql, 2);
	}
}
$all_sid =  implode(",", $all_sid);
$log_sql = "select * from server_info_log where sid in ($all_sid);";
$log_ret = $page->globalExecute($log_sql,3);
foreach ($log_ret['ret']['data'] as $row) {
	$sid = "s".$row['sid'];
	$allServerAct[$sid]['log']=$row['log'];
}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>
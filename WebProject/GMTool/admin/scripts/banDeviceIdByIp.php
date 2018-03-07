<?php
define('IN_ADMIN',true);
define('APP_ROOT', realpath(dirname(__DIR__) . '/../gameengine/test'));
define('ADMIN_ROOT', realpath(dirname(__DIR__) . '/'));
ini_set('mbstring.internal_encoding','UTF-8');
include ADMIN_ROOT.'/config.inc.php';
include ADMIN_ROOT.'/admins.php';
include_once ADMIN_ROOT.'/servers.php';
$page = new BasePage();
date_default_timezone_set('UTC');
set_time_limit(0);

if(isset($_REQUEST['fileName'])){
	$fileName = $_REQUEST['fileName'];
}else {
	exit();
}

if(isset($_REQUEST['operator'])){
	$operator = $_REQUEST['operator'];
}else {
	exit();
}


global $servers;
$deviceIdArray=array();

$outPutFile="/tmp/{$operator}_ip_".date('Ymd').".txt";
$file=ADMIN_ROOT.'/scripts/'.$fileName;
file_put_contents($outPutFile,"uid,name,注册时间,付费金币,设备型号,大本等级\n",FILE_APPEND);

$handle = @fopen($file, "r");
if ($handle) {
	while (($buffer = fgets($handle)) !== false) {
	$buffer = trim($buffer);
	if (empty($buffer)) continue;
	$deviceId=$buffer;
	
	//$userArray=array();
	$result= cobar_getValidAccountList('device', $deviceId);
	//$result=cobar_getAccountInfoByGameuids($deviceId);
	
	if(count($result) > 0){
		foreach ($result as $curRow){
			$data = $curRow;
			$logItem=array();
			$logItem['server'] = 's'.$data['server'];
			$logItem['UID'] = $data['gameUid'];
			$logItem['deviceId'] = $data['deviceId'];
			$logItem['gameUserName'] = $data['gameUserName'];
			$logItem['gameUserLevel'] = $data['gameUserLevel'];
			
			$sql="select u.uid,u.name,date_format(from_unixtime(u.regTime/1000),'%Y%m%d') regTime,u.payTotal,u.phoneDevice,ub.level from userprofile u inner join user_building ub on u.uid=ub.uid where u.uid='".$data['gameUid']."' and ub.itemId=400000;";
			$ret=$page->executeServer('s'.$data['server'], $sql, 3);
			$row=$ret['ret']['data'][0];
			file_put_contents($outPutFile,$row['uid'].','.$row['name'].','.$row['regTime'].','.$row['payTotal'].','.$row['phoneDevice'].','.$row['level']."\n",FILE_APPEND);
			
			//$userArray[]=$logItem;
			/*if ($data['gameUserLevel']<=6){
				$opeDate=date('Y-m-d H:i:s');
				$time=time()*1000;
				$serverId=$data['server'];
				$server='s'.$data['server'];
				$uid=$data['gameUid'];
				$uuid=md5($serverId.$uid.$time);
				$sql="insert into ban_record(uuid,serverId,uid,time,operator,reason,opDate) values('$uuid',$serverId,'$uid',$time,'系统','根据ip获取的异常玩家','$opeDate')";
				$page->globalExecute($sql, 2);
	
				$reasonSql="insert into banTime_reason(serverId,uid,operator,reason,opeDate,status) values('$server','$uid','系统','充值作弊','$opeDate',1) ON DUPLICATE KEY UPDATE operator='系统',reason='根据ip获取的异常玩家',opeDate='$opeDate',status=1;";
				$page->globalExecute($reasonSql, 2);
	
				$taskSql="update user_task set id=CONCAT(id,'_ban') where uid='$uid' and state=0 limit 1;";
				$page->executeServer($server,$taskSql, 2);
	
				$sql = "select pointid from user_world where uid='$uid'";
				$result = $page->executeServer($server,$sql, 3,true);
				$pointid = $result['ret']['data'][0]['pointid'];
				$currserver = $server;
				$serverinfo = $servers[$currserver];
				$ip = $serverinfo['ip_inner'];
				$rediskey = 'world'.substr($currserver, 1);
				$redissfs = new Redis();
				$redissfs->connect($ip,6379);
				$redissfs->hDel($rediskey, $pointid);
	
				$sql="update worldpoint set pointType=8 where id=$pointid;";
				$re = $page->executeServer($server,$sql,2);
	
				$sql = "update userprofile set banTime=9223372036854775806 where uid ='$uid'";
				$re = $page->executeServer($server,$sql,2);
				cobar_query_global_db_cobar("update account_new set active = 1 where gameUid = '{$uid}'");
				file_put_contents('/data/log/banDeviceIdByIp.log', date('Y-m-d H:i:s')." $server $uid ".$data['deviceId']." ".$data['gameUserName']." ".$data['gameUserLevel']."\n", FILE_APPEND);
			}*/
		}
	}
	//file_put_contents('/tmp/log/uidByDeviceIdAndIp.log', print_r($userArray), FILE_APPEND);
	
	}
}

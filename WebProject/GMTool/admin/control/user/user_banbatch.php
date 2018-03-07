<?php
!defined('IN_ADMIN') && exit('Access Denied');
$type = $_REQUEST['action'];
if($_REQUEST['contents'])
	$contents = $_REQUEST['contents'];
if($contents){
	$contents = addslashes($contents);
	$arr = explode(';',$contents);
	if(count($arr) <1) {
		$headAlert='uid错误';
	}
	$arruids2 = array_chunk($arr,50);
	$uidServerArray = array();
	foreach($arruids2 as $key=>$value){
		$value1 = array_values($value);

		$result['ret']['data'] = cobar_getAccountInfoByGameuids($value1);
		foreach ($result['ret']['data'] as $curRow){
			$uidServerArray[$curRow['gameUid']]=$curRow['server'];
		}
	}

	foreach($uidServerArray as $uid=>$server) {
		if(!is_numeric($uid)){
			$headAlert='uid错误';
			break;
		}
		$server = 's'.$server;
		$opeDate = date('Y-m-d H:i:s');

		$type = 'unactive';//表示 封号

		if ($type == 'unactive') {

			$ret = $page->webRequest('kickuser', array('uid' => $uid), $server);

			$active = 1;
			$sql = "update userprofile set banTime=2208988800000 where uid ='$uid'";
			$ret = $page->executeServer($server,$sql, 2);
			$reason = $_REQUEST['reason'] ? $_REQUEST['reason'] : '没有填写原因';
			$reasonSql = "insert into banTime_reason(serverId,uid,operator,reason,opeDate,status) values('$server','$uid','$operator','$reason','$opeDate',1) ON DUPLICATE KEY UPDATE operator='$operator',reason='$reason',opeDate='$opeDate',status=1;";
			$page->globalExecute($reasonSql, 2);

			$time = time() * 1000;
			$serverId = substr($server,1);//
			$uuid = md5($serverId . $uid . $time);
			$sql = "insert into ban_record(uuid,serverId,uid,time,operator,reason,opDate) values('$uuid',$serverId,'$uid',$time,'$operator','$reason','$opeDate')";
			$page->globalExecute($sql, 2);

//			$taskSql = "update user_task set id=CONCAT(id,'_ban') where uid='$uid' and state=0 limit 1;";
//			$page->executeServer($server,$taskSql, 2);

			$sql = "select pointid from user_world where uid='$uid'";
			$result = $page->executeServer($server,$sql, 3);
			$pointid = $result['ret']['data'][0]['pointid'];

			$serverinfo = $servers[$server];
			$ip = $serverinfo['ip_inner'];
			$rediskey = 'world' . substr($server, 1);
			$redissfs = new Redis();
			$redissfs->connect($ip, 6379);
			$redissfs->hDel($rediskey, $pointid);

			$sql = "update worldpoint set pointType=8 where id=$pointid;";
			$re = $page->executeServer($server,$sql, 2);

		}
		if ($uid) {
// 			$sql = "update account_new set active = $active where gameUid = '{$uid}'";
// 			$result = $page->globalExecute($sql);
			cobar_query_global_db_cobar("update account_new set active = $active where gameUid = '{$uid}'");

			adminLogUser($adminid, $uid, $server, array('active' => $active,'action'=>'banbatch'));
		}

	}

}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>

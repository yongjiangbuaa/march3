<?php
define('IN_ADMIN',true);
define('APP_ROOT', realpath(dirname(__DIR__) . '/../gameengine/test'));
define('ADMIN_ROOT', realpath(dirname(__DIR__) . '/'));
ini_set('mbstring.internal_encoding','UTF-8');
include ADMIN_ROOT.'/config.inc.php';
include ADMIN_ROOT.'/admins.php';
include_once ADMIN_ROOT.'/servers.php';
include ADMIN_ROOT . '/language/exchangeLang.php';
$page = new BasePage();
date_default_timezone_set('UTC');
set_time_limit(0);
ini_set('memory_limit', '1024M');

global $servers;
$eventAll = array();

$gameuid = $_REQUEST['gameuid'];
$paymentid = $_REQUEST['paymentid'];
if (empty($gameuid)) {
	exit("gameuid is empty.");
}

$valid_ip = array('75.126.38.153','173.193.186.100','202.134.124.106');

$fromip = get_ip();
$redis = new Redis();
$redis->connect('10.81.103.90',6379);
// $uidlist = $redis->hGetAll('h_pay_cheat');
// $banneduidlist = $redis->hGetAll('h_pay_cheat_banned');

$dbIndex=array(
		'uid',
		'name',
		'orderId',
		'productId',
		'payPf',
		'type',
		'regTime',
		'payTime',
		'spend',
		'payLevel',
		'payDeviceId',
		'payIp',
		'regPf',
		'country',
		'regIp',
		'stat_regTime'
);

$uidlist = array($gameuid=>999999);
$nowTime=time()*1000;
foreach ($uidlist as $uid=>$cnt) {
	$orderSpend=0;
	$accall = cobar_getAccountInfoByGameuids($uid);
	$acc = $accall[0];
	if (empty($acc)) {
		echo "$uid NG. account not exists\n";
		continue;
	}
	
	$server = "s{$acc['server']}";
	file_put_contents('/data/log/pay_cheat_banned.log', date('Y-m-d H:i:s')." $server $uid viaIP:$fromip\n", FILE_APPEND);
	if (!in_array($fromip, $valid_ip)) {
		continue;
	}
	
	$link=mysqli_connect('STATISTICSIP','root','DBPWD','global');
	$sql="select sid,uid,times,date from fb_alert_record where sid=".$acc['server']." and uid='$uid';";
	$res = mysqli_query($link,$sql);
	$row = mysqli_fetch_assoc($res);
	if (isset($row['date']) && $row['date'] && $row['date']!=date('Ymd')){
		$times=$row['times']+1;
	}
	if (($times>1)){
		$opeDate=date('Y-m-d H:i:s');
		$time=time()*1000;
		$serverId=$acc['server'];
		$uuid=md5($serverId.$uid.$time);
		$sql="insert into ban_record(uuid,serverId,uid,time,operator,reason,opDate) values('$uuid',$serverId,'$uid',$time,'系统','充值作弊','$opeDate')";
		$page->globalExecute($sql, 2);
		
		$reasonSql="insert into banTime_reason(serverId,uid,operator,reason,opeDate,status) values('s$serverId','$uid','系统','充值作弊','$opeDate',1) ON DUPLICATE KEY UPDATE operator='系统',reason='充值作弊',opeDate='$opeDate',status=1;";
		$page->globalExecute($reasonSql, 2);
		
//		$taskSql="update user_task set id=CONCAT(id,'_ban') where uid='$uid' and state=0 limit 1;";
//		$page->executeServer($server,$taskSql, 2);
		
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
		
// 		$ret = $page->webRequest('kickuser',array('uid'=>$uid),$server);
// 		$ret = json_encode($ret);
		
		$redis->hSetNx('h_pay_cheat_banned', $uid, date('Y-m-d H:i:s'));
		
		$sql="update fb_alert_record set times=$times,date=".date('Ymd')." where sid=$serverId and uid='$uid';";
		$res = mysqli_query($link,$sql);
		
		echo "$server $uid $ret\n";
	}else {
		$sql="select lang from userprofile where uid='$uid';";
		$ret=$page->executeServer($server, $sql, 3);
		$lang='';
		if (!$ret['error'] && $ret['ret']['data']){
			$lang=$ret['ret']['data'][0]['lang'];
		}
		if (empty($lang) || (!isset($contentsArray[$lang]))){
			$lang='en';
		}
		
		$sendTime = microtime ( true ) * 1000;
		$title = addslashes ( $titleArray[$lang]['2'] );
		$contents = addslashes ( $contentsArray[$lang]['2'] );
		$mailUid = md5 ( $uid . $server . time () );
		$sql = "insert into mail (uid, toUser, fromUser, fromName, status, type, rewardStatus, saveFlag, title, contents, createTime, itemIdFlag, reply) values ('$mailUid', '$uid', '', 'system', 0, 13, 1, 0, '$title', '$contents', $sendTime, 0, 0)";
		$result2 = $page->executeServer ( $server, $sql, 2 );
		sendReward2 ( $mailUid, $server );
		
		$sql="insert into fb_alert_record(sid,uid,times,date) values(".substr($server, 1).",'$uid',1,".date('Ymd').");";
		$res = mysqli_query($link,$sql);
	}
	
	$sql="select sid,orderId from global_paylog where orderId='$paymentid';";
	$res = mysqli_query($link,$sql);
	$row = mysqli_fetch_assoc($res);
	if (isset($row['sid']) && !empty($row['sid'])){
		$serverTemp='s'.$row['sid'];
	}else {
		$serverTemp=$server;
	}
	
	
	$productArray = getPackageInfo ();
	$tempArray = array ();
	$props = '';
	$flag = false;
	
	$sql="select productId,time from paylog where uid='$uid' and orderId='$paymentid';";
	$ret=$page->executeServer($serverTemp, $sql, 3);
	if (!$ret['error'] && $ret['ret']['data']){
		$payTime=$ret['ret']['data'][0]['time'];
		$productId=$ret['ret']['data'][0]['productId'];
	}
	
	foreach ( $productArray as $valueArray ) {
		if ($valueArray ['id'] == $productId) {
			$productType = $valueArray ['type'];
			if ($productType==5){
				
				$day=ceil(($payTime-time()*1000)/86400000);
				$refundGold=$day*MONTH_CARD_GOLD;
				$props = 'gold,0,' . $refundGold . '|';
			}else {
				$props = 'gold,0,' . $valueArray ['gold_doller'] . '|';
			}
			if ($valueArray ['item']) {
				$tempArray = explode ( '|', $valueArray ['item'] );
				foreach ( $tempArray as $tempValue ) {
					$value = explode ( ';', $tempValue );
					$props .= 'goods,' . $value [0] . ',' . $value [1] . '|';
				}
			}
			$props = trim ( $props, '|' );
			$flag = true;
			break;
		}
	}
	if (! $flag) {
		adminLogUser ( '系统', $uid, $serverTemp, array (
		'refund_orderId' => $paymentid,
		'result' => "礼包ID为" . $paymentid . "的礼包已经下线，无法扣除道具!"
		) );
	} else {
		$ret = $page->webRequest ( "refund", array (
				'uid' => $uid,
				'orderId' => $paymentid,
				'refund' => $props,
				'type' => 0
		), $serverTemp );
		
		if ($productType == 3) {
			$sql = "DELETE FROM exchange WHERE uid = '$uid' AND id = '$paymentid'";
			$page->executeServer ( $serverTemp, $sql, 2 );
		}
		if ($productType == 5) {
			$sql = "DELETE FROM monthly_card WHERE uid = '$uid' AND itemId = '$paymentid'";
			$page->executeServer ( $serverTemp, $sql, 2 );
		}
		if ($ret != 'ok') {
			adminLogUser ( '系统', $uid, $serverTemp, array (
			'refund_orderId' => $paymentid,
			'result' => '"调用Java接口扣除道具失败!"'
			) );
		}else {
			adminLogUser ( '系统', $uid, $serverTemp, array (
			'refund_orderId' => $paymentid,
			'result' => '扣除道具成功'
			) );
		}
	}
	
	
	$sql="update paylog set status=4 where orderId='$paymentid';";
	$page->executeServer($serverTemp, $sql, 2);
	
	$sql = "update global_paylog set status=4 where orderId='$paymentid';";
	$res = mysqli_query($link,$sql);
	
	$sql="select p.uid uid,u.name name,p.orderId orderId,p.productId productId,p.pf payPf,r.type type,u.regTime regTime,p.time payTime,p.spend spend,p.payLevel payLevel,p.deviceId payDeviceId,p.ip payIp,r.pf regPf,r.country country,r.ip regIp,r.time stat_regTime from paylog p inner join userprofile u on p.uid=u.uid inner join stat_reg r on p.uid=r.uid where p.uid='$uid' and p.orderId='$paymentid' order by stat_regTime desc;";
	$ret=$page->executeServer($serverTemp, $sql, 3);
	$serverId=substr($serverTemp, 1);
	print_r($ret);
	if (!$ret['error'] && isset($ret['ret']['data']) && is_array($ret['ret']['data']) && $ret['ret']['data']){
		$col="sid,";
		$colVal="$serverId,";
		$dupVal="sid=$serverId,";
		foreach ($dbIndex as $index){
			$col.=$index.',';
			$colVal.=$ret['ret']['data'][0][$index]?("'{$ret['ret']['data'][0][$index]}',"):("'',");
			$dupVal.=$ret['ret']['data'][0][$index]?("$index='{$ret['ret']['data'][0][$index]}',"):("$index='',");
		}
		$col.='operateTime,';
		$colVal.=$nowTime.',';
		$dupVal.="operateTime=$nowTime,";
		
		$col.='fbFlag';
		$colVal.='1';
		$dupVal.="fbFlag=1";
		$sql="insert into refund_info($col) values($colVal) ON DUPLICATE KEY UPDATE $dupVal;";
		$res = mysqli_query($link,$sql);
	}
	mysqli_close($link);
}

function sendReward2($mailUid, $serv) {
	$page = new BasePage ();
	$page->webRequest ( 'sendmail', array (
			'uid' => $mailUid
	), $serv );
}

function get_ip() {
	if (_valid_ip($_SERVER["HTTP_CLIENT_IP"])) {
		return $_SERVER["HTTP_CLIENT_IP"];
	}
	foreach (explode(",",$_SERVER["HTTP_X_FORWARDED_FOR"]) as $ip) {
		if (_valid_ip(trim($ip))) {
			return $ip;
		}
	}
	if (_valid_ip($_SERVER["HTTP_X_FORWARDED"])) {
		return $_SERVER["HTTP_X_FORWARDED"];
	} elseif (_valid_ip($_SERVER["HTTP_FORWARDED_FOR"])) {
		return $_SERVER["HTTP_FORWARDED_FOR"];
	} elseif (_valid_ip($_SERVER["HTTP_FORWARDED"])) {
		return $_SERVER["HTTP_FORWARDED"];
	} elseif (_valid_ip($_SERVER["HTTP_X_FORWARDED"])) {
		return $_SERVER["HTTP_X_FORWARDED"];
	} else {
		return $_SERVER["REMOTE_ADDR"];
	}
}
function _valid_ip($ip) {
	if (!empty($ip) && ip2long($ip)!=-1) {
		$reserved_ips = array (
				array('0.0.0.0','2.255.255.255'),
				array('10.0.0.0','10.255.255.255'),
				array('127.0.0.0','127.255.255.255'),
				array('169.254.0.0','169.254.255.255'),
				array('172.16.0.0','172.31.255.255'),
				array('192.0.2.0','192.0.2.255'),
				array('192.168.0.0','192.168.255.255'),
				array('255.255.255.0','255.255.255.255')
		);
		foreach ($reserved_ips as $r) {
			$min = ip2long($r[0]);
			$max = ip2long($r[1]);
			if ((ip2long($ip) >= $min) && (ip2long($ip) <= $max)) return false;
		}
		return true;
	} else {
		return false;
	}
}

function getPackageInfo() {
		$a = require ADMIN_ROOT . '/language/refound/package.php';
		return $a;
}

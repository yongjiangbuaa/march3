<?php
define('IN_ADMIN',true);
define('ADMIN_ROOT', realpath(dirname(__FILE__) . '/../'));
include ADMIN_ROOT.'/config.inc.php';
include ADMIN_ROOT.'/servers.php';
ini_set('mbstring.internal_encoding','UTF-8');
includeModel("BasePage");
global $servers;
set_time_limit(0);
trackLog('INFO', "start. ".date('Y-m-d H:i:s'));

$GLOBAL_DB_SERVER_IP = array('10.1.4.122','10.1.4.122');//DEPLOYIP
$db_server_ip = $GLOBAL_DB_SERVER_IP;
if (is_array($db_server_ip)) {
	$idx = randItemWithWeight(array(100,100));
	$db_server_ip = $db_server_ip[$idx];
}
$cokdb_global_hostinfo1 = array(
		'host' => $db_server_ip,
		'user' => 'root',
		'password' => 'WNknSeKS1EI7o',
// 		'port' => '8066',
		'port' => '3306',
);
$cokdb_global_hostinfo2 = array(
		'host' => '10.1.4.122',
		'user' => 'root',
		'password' => 'WNknSeKS1EI7o',
		'port' => '3306',
);

$tryTimes = $argv[1]?$argv[1]:0;
$page = new BasePage();
$endTime = (time() - 86400)*1000;
// $sqlData = $page->executeServer('global',"select * from adreward where time > $endTime and state = 0 and tryTimes = $tryTimes order by time asc limit 1000",1,true);
$sqlData['ret']['data'] = query_global_db_direct("select * from adreward where time > $endTime and state = 0 and tryTimes = $tryTimes order by time asc limit 1000");
$deviceList = array();
$mailList = array();
$sendTime = floor(microtime(true)*1000);
foreach ($sqlData['ret']['data'] as $curRow){
	$gaid = $curRow['device'];
	$adTime = $curRow['time'];
	$gameAccount = null;
	
	if(!$deviceList[$gaid]){
// 		$accountSqlData = $page->executeServer('global',"select * from account_new where gaid = '$gaid' and active = 0 order by lastTime desc limit 1",1,true);
// 		$gameAccount = $accountSqlData['ret']['data'][0]; 
 		$validlist = getValidAccountList('gaid', $gaid);
 		$gameAccount = $validlist[0];
		if ($gameAccount)
			$deviceList[$gaid] = $gameAccount;
	}else{
		$gameAccount = $deviceList[$gaid];
	}
	if($gameAccount){
		$toUser = $gameAccount['gameUid'];
		$title = 114132;
		$contents = 114133;
		$reward = $curRow['reward'];
		$uid = getGUID();
		$mailList[] = array('server'=>$gameAccount['server'],'uid'=>$uid);
		$newMail = "insert into mail (uid, toUser, fromUser, fromName, status, type, rewardStatus, saveFlag, title, contents, rewardId, createTime, itemIdFlag, reply) values ('$uid', '$toUser', '', 'system', 0, 13, 0, 0, '$title', '$contents', '$reward', $sendTime, 1, 0)";
		$page->executeServer(getServer($gameAccount['server']),$newMail,1,true);
		$page->webRequest('sendmail',array('uid'=>$uid),getServer($gameAccount['server']));
// 		$page->executeServer('global',"update adreward set user = '$toUser', state=1, sendTime = $sendTime where device = '$gaid' and time = $adTime",1,true);
		query_global_db_direct("update adreward set user = '$toUser', state=1, sendTime = $sendTime where device = '$gaid' and time = $adTime");
	}else{
		if($tryTimes < 5){
// 			$page->executeServer('global',"update adreward set tryTimes = tryTimes + 1 where device = '$gaid' and time = $adTime",1,true);
			query_global_db_direct("update adreward set tryTimes = tryTimes + 1 where device = '$gaid' and time = $adTime");
		}
	}
}
foreach ($deviceList as $gameAccount){
	$page->webRequest('pushmsg',array('uid'=>$gameAccount['gameUid'],'msg'=>'push.adreward'),getServer($gameAccount['server']));
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


/**
 * 从cobar中读取。
 * @param unknown $type
 * @param unknown $value
 * @return multitype:
 */
function getValidAccountList($type, $value){
	$allAccountArr = getRecordFromMappingDB($type, $value);
	if (empty($allAccountArr)) {
		return array();
	}

	$target_list = array();
	foreach ($allAccountArr as $row) {
		if ($row['active'] == 1) {
			continue;
		}
		$target_list[$row['lastTime']] = $row;
	}
	krsort($target_list);
	$validAccountArr = array_values($target_list);

	return $validAccountArr;
}

/**
 * 从数据库表 usermapping/account_new 中读取account数据。
 * @param string $type
 * @param string $value
 * @return multitype:|Ambigous <multitype:multitype: , multitype:unknown >
 */
function getRecordFromMappingDB($type, $value){
	//获取mapping gameuid
	$allAccountArr = query_global_db_cobar("select * from usermapping where mappingType='$type' and mappingValue = '$value';");
	if (empty($allAccountArr)) {
		return array();
	}
	$target_gameuids = array();
	foreach ($allAccountArr as $row) {
		$target_gameuids[] = "'".$row['gameUid']."'";
	}
	$gameuids = implode(',', $target_gameuids);
	//获取所有Account数据
	$allAccountArr = query_global_db_cobar("select * from account_new where gameUid in ($gameuids);");
	if (empty($allAccountArr)) {
		return array();
	}
	return $allAccountArr;
}
function query_global_db_cobar($sql) {
	return doquery('cobar', $sql);
}
function query_global_db_direct($sql) {
	return doquery('direct', $sql);
}
function doquery($type,$sql){
	global $cokdb_global_hostinfo1, $cokdb_global_hostinfo2;
	if ($type == 'cobar') {
		$cokdb_global_hostinfo = $cokdb_global_hostinfo1;
	}else{
		$cokdb_global_hostinfo = $cokdb_global_hostinfo2;
	}
	$ret = array ();
	$mysqli = new mysqli($cokdb_global_hostinfo['host'], $cokdb_global_hostinfo['user'], $cokdb_global_hostinfo['password'], 'cokdb_global', $cokdb_global_hostinfo['port']);
	/* check connection */
	if ($mysqli->connect_errno) {
		trackLog('ERROR', "Connect failed: ".$mysqli->connect_error);
		exit();
	}
	if ($result = $mysqli->query($sql)) {
		if ($result === true) {
			trackLog('INFO', "sql. ".$sql);
		}
		else if ($result === false) {
			trackLog('ERROR', "sql. ".$sql);
		}
		else{
			/* fetch associative array */
			while ($row = $result->fetch_assoc()) {
				$ret [] = $row;
			}
			// 			trackLog('INFO', "sql. ".$sql."\nresult. ".print_r($ret, true));
			/* free result set */
			$result->free();
		}
	}
	
	/* close connection */
	$mysqli->close();
	return $ret;
}
function trackLog($type, $message){
	global $tryTimes;
	$file = '/tmp/adReward_'.$tryTimes.'_'.date('Ymd').'.log';
	file_put_contents($file, "$type, $message"."\n", FILE_APPEND);
}
function randItemWithWeight($weights){
	$weight_sum = array_sum($weights);
	$seed = 100000;
	$weight_total = $weight_sum * $seed;
	$rand = mt_rand(1, $weight_total);
	foreach ($weights as $k=>$w){
		$rand -= $w * $seed;
		if($rand <= 0){
			return $k;
		}
	}
	return null;
}

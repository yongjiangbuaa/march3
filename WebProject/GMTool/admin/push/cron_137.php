<?php
define('PUSH_ROOT', __DIR__);
date_default_timezone_set('UTC');
// require_once PUSH_ROOT.'/../include/mailchimp/Mandrill.php';
require_once PUSH_ROOT.'/Push.php';
set_time_limit(0);
ini_set('memory_limit', '512M');

foreach ( $argv as $arg ) {
	$kv = explode ( '=', $arg, 2 );
	$_REQUEST [$kv [0]] = $kv [1];
}

$from_sid = $_REQUEST['from_sid'];
$to_sid = $_REQUEST['to_sid'];
if (!$from_sid) {
	$from_sid = 1;
}
if (!$to_sid) {
	$to_sid = 900000;
}

$mailpattern = "/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z-_.]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i";

/*
// 注册当天不算，注册后的第一天开始算起（按照自然天、服务器时间）
// 北京时间15:00（服务器时间07:00）推送
*/

echo_message(__FILE__." START.", false);

$host = gethostbyname(gethostname());
if ($host == '184.173.110.102' || $host == '39-90') {
	require_once PUSH_ROOT.'/db.inc.php';
	$parse_app_id='T8Ssh6BzQXhBM34MImIFgfAbfVwcm2p1UO1Yi1tL';
	$parse_api_key='mBjbM0u3vwl2NIi61DJZT1gF1whJK5abiHqkjYrH';
// 	$cokdb_hostinfo = array(
// 			'ip_inner' => '10.41.163.16',
// 			'port' => '3306',
// 			'user' => 'root',
// 			'password' => 'DBPWD',
// 			'dbname' => 'cokdb2',
// 	);
// 	$db_list = array($cokdb_hostinfo);
	$db_list = get_db_list();
	foreach ($db_list as &$db) {
		$db['user'] = 'root';
		$db['password'] = 'DBPWD';
	}
	$globallink = mysqli_connect(GLOBAL_DB_SERVER_IP, 'root', GLOBAL_DB_SERVER_PWD, 'cokdb_global', '3306');
}
elseif ($host == 'IPIPIP') {
	//***TEST***
	$parse_app_id='T8Ssh6BzQXhBM34MImIFgfAbfVwcm2p1UO1Yi1tL';
	$parse_api_key='mBjbM0u3vwl2NIi61DJZT1gF1whJK5abiHqkjYrH';
	$cokdb_hostinfo = array(
			'ip_inner' => 'IPIPIP',
			'port' => '3306',
			'user' => 'root',
			'password' => 'admin123',
			'dbname' => 'cokdb1',
	);
	//***TEST***
	$db_list = array($cokdb_hostinfo);
	$globallink = mysqli_connect('IPIPIP', 'root', 'admin123', 'cokdb_global', '3306');
}
else{
	//***TEST***
	$parse_app_id='T8Ssh6BzQXhBM34MImIFgfAbfVwcm2p1UO1Yi1tL';
	$parse_api_key='mBjbM0u3vwl2NIi61DJZT1gF1whJK5abiHqkjYrH';
	$cokdb_hostinfo = array(
			'ip_inner' => 'localhost',
			'port' => '3306',
			'user' => 'cok',
			'password' => '1234567',
			'dbname' => 'cokdb1',
	);
	//***TEST***
	$db_list = array($cokdb_hostinfo);
	$globallink = mysqli_connect('localhost', 'cok', '1234567', 'cokdb_global', '3306');
}

// get msg define from db
$sql = "select id,time,title,contents,reward,notification from cokdb_global.push_137";
$rows = query_db($globallink, $sql);
// print_r($rows);
foreach ($rows as $row) {
	$row['title'] = json_decode($row['title'], true);
	$row['contents'] = json_decode($row['contents'], true);
	$row['notification'] = json_decode($row['notification'], true);
	$def_137[$row['id']] = $row;
}
// print_r($def_137);
// echo $parse_app_id, $parse_api_key, "\n";

// get daoliang from db
$sql = "select id,daoliangStart,daoliangEnd from cokdb_global.server_info";
$rows = query_db($globallink, $sql);
$daoliang = array();
// print_r($rows);
foreach ($rows as $row) {
	$daoliang[$row['id']] = array('start'=>$row['daoliangStart'], 'end'=>$row['daoliangEnd']);
}

$push = new Push ( $parse_app_id, $parse_api_key );

foreach ($db_list as $record) {
	$sid = $record['db_id'];
	if ($sid < $from_sid || $sid > $to_sid) {
		continue;
	}
	$cokdb_hostinfo = array();
	$cokdb_hostinfo['host'] = $record['ip_inner'];
	$cokdb_hostinfo['port'] = $record['port'];
	$cokdb_hostinfo['dbname'] = $record['dbname'];
	$cokdb_hostinfo['user'] = $record['user'];
	$cokdb_hostinfo['password'] = $record['password'];

	$GLOBALS['current_dbip'] = $cokdb_hostinfo['host'];
	$GLOBALS['current_dbname'] = $cokdb_hostinfo['dbname'];
	foreach ($def_137 as $key=>$def) {
		$push_tokens = array();
		$def_pushmsg = $def['notification'];
		$def_mail_title = $def['title'];
		$def_mail_content = $def['contents'];
		$reward = $def['reward'];
		
		$d_start = strtotime(date('Y-m-d',strtotime("-$key day")));
		if ($daoliang[$sid]['daoliangEnd'] > 0) {
			$daoend = $daoliang[$sid]['daoliangEnd'] + 11*86400*1000;
			if ($daoend > $d_start) {
				continue;
			}
		}
		$d_end = $d_start + 86400;
		$d_start *= 1000;
		$d_end *= 1000;
		
		$GLOBALS['current_day'] = $key;
		echo_message('Start >>>');
		$link = mysqli_connect($cokdb_hostinfo['host'], $cokdb_hostinfo['user'], $cokdb_hostinfo['password'], $cokdb_hostinfo['dbname'], $cokdb_hostinfo['port']);
		$sql = "select uid,parseRegisterId,gmail,lang,pf,appVersion from userprofile where regTime>$d_start and regTime<=$d_end;";
		$targetUidArray = query_db($link,$sql);
		echo_message($sql);
		echo_message('count='.count($targetUidArray));
		if (empty($targetUidArray)) {
			continue;
		}
		
// 		// mail_contents
// 		$mailContent = array();
// 		$contentsId = md5(uniqid(mt_rand(),1).microtime(true));
// 		$reward = $def_mail_rewards[$key];
// 		$ctime = time()*1000;
// 		$sql = "insert into mail_contents(id, title, contents, rewardId, createTime) values ('$contentsId', '$def_mail_title', '$def_mail_content', '$reward', $ctime);";
// 		$ret = mysqli_query($link, $sql);
// 		echo_message($sql);
// 		echo_message("ret=".intval($ret));
		
		// write mail to db & return push back 
		$push_tokens = processOneServer($targetUidArray,$def_pushmsg,$def_mail_title,$def_mail_content,$reward);
		//run_send_push($push_tokens, $def_pushmsg, "reg-$key");// 2015-9-3 stop temp. wangxianwei
		
		mysqli_close($link);
		echo_message('End <<<');
	}
}

echo_message(__FILE__." END.", false);

exit(0);

//
function processOneServer($targetUidArray,$def_pushmsg,$def_mail_title,$def_mail_content,$reward){
	$batch_count = 100;
	$counter = 0;
	$query_sqls = array();
	$push_tokens = array();
	$to = array();
	$recipient_metadata = array();
	$key = $GLOBALS['current_day'];
	global $mailpattern;
	$template_name = "cok-push137-".$key;
	$tags = array($template_name);
	
	foreach ($targetUidArray as $user) {
		if (empty($user['uid'])) {
			continue;
		}
		$counter++;
		
		$lang = $user['lang'];
		if ($lang == 'zh_CN') {
			$lang = 'zh_Hans';
		}
		if ($lang == 'zh_TW') {
			$lang = 'zh_Hant';
		}
		$mail_title = isset ( $def_mail_title [$lang] ) ? $def_mail_title [$lang] : $def_mail_title ['en'];
		$mail_content = isset ( $def_mail_content [$lang] ) ? $def_mail_content [$lang] : $def_mail_content ['en'];
		$mail_title = addslashes($mail_title);
		$mail_content = addslashes($mail_content);
		
		//mail
		$gameuid = $user['uid'];
		$uid = md5(uniqid(mt_rand(),1).microtime(true).$gameuid);
		$fromName = 'system';
		$mailType = 15;
		$rewardStatus = 0;//有奖
		$createTime = time()*1000;
		$sql = "
				insert into mail(uid, toUser, fromName, title, contents, type, rewardId, rewardStatus, createTime) values
				('$uid', '$gameuid', '$fromName', '$mail_title', '$mail_content', $mailType, '$reward', $rewardStatus, $createTime)
		";
		$query_sqls[] = $sql;

		$pf = $user['pf'];
		$deviceType = 'android';
		if ($pf == 'AppStore') {
			$deviceType = 'ios';
		}
		if (!empty($user['parseRegisterId'])) {
			$deviceToken = $user['parseRegisterId'];
			$pushlang = isset ( $def_pushmsg [$lang] ) ? $lang : 'en';
			if (!empty($def_pushmsg [$pushlang])) {
				$push_tokens[$deviceType][$pushlang][] = $deviceToken;
			}
		}

// 		$mailaddr = $user['gmail'];
// 		if (!empty($mailaddr) && preg_match($mailpattern, $mailaddr)) {
// 			$to[] = array('email' => $mailaddr);
// 			$recipient_metadata[] = array(
// 				'rcpt' => $mailaddr, 
// 				'values'=>array(
// 					'game'=>$game, 
// 				),
// 			);
// 		}
		
		if ($counter >= $batch_count) {
			run_write_db_batch($query_sqls);
// 			run_send_mail_template($to, $recipient_metadata, $template_name, $pushmsg, $tags);
			// reset
			$counter =0 ;
			$query_sqls = array();
			$to = array();
			$recipient_metadata = array();
		}
	}
	
	if ($counter > 0){
		run_write_db_batch($query_sqls);
// 		run_send_mail_template($to, $recipient_metadata, $template_name, $pushmsg, $tags);
	}
	return $push_tokens;
}

function run_write_db_batch($query_sqls){
	if (empty($query_sqls)) {
		return ;
	}
	global $link, $cokdb_hostinfo;
	if (!is_resource($link)) {
		$link = mysqli_connect($cokdb_hostinfo['host'], $cokdb_hostinfo['user'], $cokdb_hostinfo['password'], $cokdb_hostinfo['dbname'], $cokdb_hostinfo['port']);
	}
	
	// insert into table mail
	$query = implode(';', $query_sqls);
// 	echo $query,"\n";
	$ret = mysqli_multi_query($link, $query);
	echo_message("mysqli_multi_query cnt=".count($query_sqls)." ret=".intval($ret));
	if (!$ret) {
		echo_message("ERROR. mysqli_multi_query_error.");
		exit(1);
	}
}
function run_send_push($push_tokens, $def_pushmsg, $type){
// 	print_r($push_tokens);
	if (empty($push_tokens)) {
		return ;
	}
	foreach ($push_tokens as $deviceType => $tm) {
		foreach ($tm as $lang => $tokens) {
			if (empty($def_pushmsg[$lang]) || empty($tokens)) {
				continue;
			}
			$chunks = array_chunk($tokens, 100);
			foreach ($chunks as $dotokens) {
				do_send_push($deviceType, $dotokens, $def_pushmsg[$lang], $lang, $type);
			}
		}
	}
}
function do_send_push($deviceType, $push_tokens, $message, $lang, $type){
	global $push, $parse_app_id;
	$push->device = $deviceType;
	
	//PUSH
	echo_message("call parse api: $parse_app_id $deviceType tokencount=".count($push_tokens). " lang=$lang msg=$message");
// 	echo implode(',', $push_tokens), "\n";
	$result = $push->pushToMultiUser($push_tokens, $message, $type);
	if (isset($result['errmsg'])) {
		echo_message("ERROR. parse_api errno={$result['errno']} errmsg={$result['errmsg']} http_code={$result['http_code']}");
	}else {
		echo_message("parse ret: http_code={$result['http_code']} time={$result['time']}");
	}
}
function run_send_mail_template($to, $recipient_metadata, $template_name, $subject, $tags){
	if (empty($to)) {
		return ;
	}
	$mandrill = new Mandrill('lvzCJNuk9AnZ1n1WjRuO-g');
	$template_content = array();
	$message = array(
			'subject' => $subject,
			'from_email' => 'HCGCOK@gmail.com',
			'from_name' => 'COQ Team',
					'to' => $to,
			'headers' => array('Reply-To' => 'HCGCOK@gmail.com'),
			'important' => false,
			'track_opens' => true,
			'track_clicks' => true,
					'tags' => $tags,
			'recipient_metadata' => $recipient_metadata,
			'preserve_recipients' => false,
			);
	$async = true;
	$ip_pool = 'Main Pool';
	$send_at = null;
	$result = $mandrill->messages->sendTemplate($template_name, $template_content, $message, $async, $ip_pool, $send_at);
// 	print_r($result);
}

function echo_message($message='',$withprefix=true){
	if ($withprefix) {
		$prefix = date('[Y-m-d.H:i:s]')." [{$GLOBALS['current_dbip']}] [{$GLOBALS['current_dbname']}] [{$GLOBALS['current_day']}] ";
		$log = "$prefix $message\n";
	}else{
		$log = date('[Y-m-d.H:i:s]')." $message\n";
	}
	echo $log;
	file_put_contents('/tmp/run_cron_137.log', $log, FILE_APPEND);
}
function query_db($link,$sql) {
	$result = mysqli_query($link, $sql);
	if (empty($result)) {
		return array();
	}
	$ret = array ();
	while ( $row = mysqli_fetch_assoc($result) ) {
		$ret [] = $row;
	}
	return $ret;
}

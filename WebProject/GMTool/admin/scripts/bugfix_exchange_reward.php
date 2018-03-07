<?php
define('PUSH_ROOT', __DIR__);
date_default_timezone_set('UTC');
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
	$to_sid = 999999;
}

$mailpattern = "/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z-_.]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i";

echo_message(__FILE__." START.", false);


// pay info .  server, uid, productid
$payinfo = array();
$paylog = file('/tmp/9715-9719_uid.log');
foreach ($paylog as $line) {
	$line = trim($line);
	if (empty($line)) {
		continue;
	}
	$arr = explode("\t", $line);
	$payinfo[$arr[0]][$arr[1]][] = $arr[2];
}

//exchange.xml
$exchange = array(
		9715=>11500,
		9716=>11501,
		9717=>11502,
		9718=>11503,
		9719=>11504,
);

//mail.xml
$mail = array(
		11500=> array(
				sender=>"3000002",
				title=>"105726",
				message=>"105731",
				reward=>"230195",
				type=>"2"
		),
		11501=> array(
				sender=>"3000002",
				title=>"105727",
				message=>"105736",
				reward=>"230196",
				type=>"2"
		),
		11502=> array(
				sender=>"3000002",
				title=>"105728",
				message=>"105737",
				reward=>"230197",
				type=>"2"
		),
		11503=> array(
				sender=>"3000002",
				title=>"105729",
				message=>"105738",
				reward=>"230198",
				type=>"2"
		),
		11504=> array(
				sender=>"3000002",
				title=>"105730",
				message=>"105739",
				reward=>"230199",
				type=>"2"
		),
);

require_once PUSH_ROOT.'/db.inc.php';

$db_list = get_db_list();
foreach ($db_list as &$db) {
	$db['user'] = 'root';
	$db['password'] = 'DBPWD';
}

foreach ($db_list as $record) {
	$sid = $record['db_id'];
	if ($sid < $from_sid || $sid > $to_sid) {
		continue;
	}
	$def_pay = $payinfo[$sid];
	if (empty($def_pay)) {
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
	
	$mysqli = new mysqli($cokdb_hostinfo['host'], $cokdb_hostinfo['user'], $cokdb_hostinfo['password'], $cokdb_hostinfo['dbname'], $cokdb_hostinfo['port']);
	$sqlfield_mail = "insert into mail(uid, toUser, fromName, title, contents, type, rewardId, itemIdFlag, rewardStatus, status, createTime) values ";
	$sqlfield_mailgroup = "insert into mail_group(uid,groupType,groupIndex,updateTime) values ";
	
	foreach ($def_pay as $patloggameuid=>$productidlist) {
		echo_message("$sid $patloggameuid".' start.... <<<');
		$query_sqls_mail = array();
		$query_sqls_group = array();
		
		$sql_alluids = "select u.allianceId, u.uid payuid, u2.uid uid from userprofile u inner join userprofile u2 on u.allianceId=u2.allianceId where u.uid='$patloggameuid' and u.allianceId != '' and u.allianceId is not null;";
		$result = $mysqli->query($sql_alluids);
		while ($row = $result->fetch_assoc()) {
			file_put_contents('/tmp/run_exchange.log', json_encode($row)."\n", FILE_APPEND);
			foreach ($productidlist as $productid) {
				$maildef = $mail[$exchange[$productid]];
				$mail_title = $maildef['title'];
				$mail_content = $maildef['message'];
				$gameuid = $row['uid'];
				$uid = md5(uniqid(mt_rand(),1).microtime(true).$gameuid);
				$fromName = $maildef['sender'];
				$mailType = $maildef['type'];
				$rewardStatus = 0;
				$status = 0;
				$createTime = time()*1000;
				$itemIdFlag = 1;
				$reward = genRewards($maildef['reward']);
				
				$one = "('$uid', '$gameuid', '$fromName', '$mail_title', '$mail_content', $mailType, '$reward', $itemIdFlag, $rewardStatus, $status, $createTime)";
				$query_sqls_mail[] = $one;
				file_put_contents('/tmp/run_exchange_sql.log', $one."\n", FILE_APPEND);
				
				$one2 = "('$gameuid', $mailType, '$uid', $createTime)";
				$query_sqls_group[] = $one2;
				file_put_contents('/tmp/run_exchange_sql_group.log', $one2."\n", FILE_APPEND);
			}
		}
		$sql1 = $sqlfield_mail . implode(',', $query_sqls_mail);
		$result = $mysqli->query($sql1);
		$rf = intval($result);
		file_put_contents('/tmp/run_exchange_result_mail.log', "$patloggameuid mail $rf\n", FILE_APPEND);
		if ($result) {
			$sql2 = $sqlfield_mailgroup . implode(',', $query_sqls_group);
			$result = $mysqli->query($sql2);
			$rf = intval($result);
			file_put_contents('/tmp/run_exchange_result_group.log', "$patloggameuid mailgroup $rf\n", FILE_APPEND);
			if (!$result) {
				file_put_contents('/tmp/run_exchange_result_err_group.log', "$patloggameuid mailgroup $rf $sql2\n", FILE_APPEND);
			}
		}else{
			file_put_contents('/tmp/run_exchange_result_err_mail.log', "$patloggameuid mail $rf $sql1\n", FILE_APPEND);
		}
		
		echo_message("$sid $patloggameuid".' end.... <<< '.$rf);
	}
	mysql_close($link);
}

echo_message(__FILE__." END.", false);


function genRewards($rewardid){
	//reward.xml
	$rewarddef = array(
			"230195"=>array(
					'honor'=>"1000",
					'alliance_point'=>"1000",
					'rate'=>"240;240;180;240;240;300;240;240;10;10;10;10;10;10;10;10;10;10;10;10",
					'item'=>"200301;200331;200200;200220;200342;200362;200390;200031;201010;201020;201030;201040;201050;201060;201070;201080;201090;201100;201110;201120",
					'num'=>"2;2;5;1;2;5;2;5;2;2;2;2;2;2;2;2;2;2;2;2",),
			"230196"=>array(
					'honor'=>"3000",
					'alliance_point'=>"3000",
					'rate'=>"240;240;240;240;240;360;120;10;10;10;10;10;10;10;10;10;10;10;10",
					'item'=>"200301;200331;200200;200220;200342;200363;200390;201010;201020;201030;201040;201050;201060;201070;201080;201090;201100;201110;201120",
					'num'=>"4;4;10;2;4;3;5;4;4;4;4;4;4;4;4;4;4;4;4",),
			"230197"=>array(
					'honor'=>"5000",
					'alliance_point'=>"5000",
					'rate'=>"240;240;240;240;240;120;360;120;10;10;10;10;10;10;10;10;10;10;10;10",
					'item'=>"200302;200332;200200;200220;200342;200420;200363;200391;201011;201021;201031;201041;201051;201061;201071;201081;201091;201101;201111;201121",
					'num'=>"2;2;15;3;6;1;5;2;2;2;2;2;2;2;2;2;2;2;2;2",),
			"230198"=>array(
					'honor'=>"7000",
					'alliance_point'=>"7000",
					'rate'=>"60;60;120;120;60;60;180;10;10;10;10;10;10;10;10;10;10;10;10",
					'item'=>"200301;200302;200200;200221;200450;200391;200364;201011;201021;201031;201041;201051;201061;201071;201081;201091;201101;201111;201121",
					'num'=>"8;8;20;2;1;3;3;3;3;3;3;3;3;3;3;3;3;3;3",),
			"230199"=>array(
					'honor'=>"10000",
					'alliance_point'=>"10000",
					'rate'=>"120;120;120;120;360;120;10;10;10;10;10;10;10;10;10;10;10;10",
					'item'=>"200303;200333;200200;200221;200364;200392;201012;201022;201032;201042;201052;201062;201072;201082;201092;201102;201112;201122",
					'num'=>"1;1;30;5;5;1;1;1;1;1;1;1;1;1;1;1;1;1",),
	);
	
	$sel = $rewarddef[$rewardid];
	//honor,0,1000|alliance_point,0,1000|goods,200362,5  
	
	$weights = explode(';', $sel['rate']);
	$items = explode(';', $sel['item']);
	$nums = explode(';', $sel['num']);
	$selitemidx = randItemWithWeight($weights);
	
	$r[] = "honor,0,".$sel['honor'];
	$r[] = "alliance_point,0,".$sel['alliance_point'];
	$r[] = "goods,".$items[$selitemidx].','.$nums[$selitemidx];
	return implode('|', $r);
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
function run_send_push($push_tokens, $def_pushmsg){
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
				do_send_push($deviceType, $dotokens, $def_pushmsg[$lang], $lang);
			}
		}
	}
}
function do_send_push($deviceType, $push_tokens, $message, $lang){
	global $push, $parse_app_id;
	$push->device = $deviceType;
	
	//PUSH
	echo_message("call parse api: $parse_app_id $deviceType tokencount=".count($push_tokens). " lang=$lang msg=$message");
// 	echo implode(',', $push_tokens), "\n";
	$result = $push->pushToMultiUser($push_tokens, $message);
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
			'from_name' => 'COK Team',
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

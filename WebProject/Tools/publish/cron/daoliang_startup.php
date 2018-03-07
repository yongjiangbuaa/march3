<?php
/*
『导量自动化』之
	·开启新导量服

*/

define('ROOT', dirname(__DIR__));
date_default_timezone_set('UTC');
set_time_limit(0);
ini_set('memory_limit', '512M');
error_reporting(0);

require_once ROOT.'/mailer/mailer.inc.php';

define('REDIS_SERVER_IP_DAOLIANG_CONFIG', 'IP');
$rateConfigKey = "RATIO_OF_CHOOSE_SERVER";
$countryConfigKey = "COUNTRY_OF_CHOOSE_SERVER";

$global_mail_lines = array();

echo_msg("开服准备 ...");

require_once ROOT.'/db/db.inc.php';
$server_list = get_server_list();
$db_list = get_db_list();
$dbLink = array();
foreach ($db_list as $dbinfo) {
	$tempsid = $dbinfo['db_id'];
	$masterdef = array(
			'host'=>$dbinfo['ip_inner'],
			'port'=>$dbinfo['port'],
			'user'=>'root',
			'password'=>'t9qUzJh1uICZkA',
			'dbname'=>$dbinfo['dbname'],
	);
	$dbLink[$tempsid] = $masterdef;
}

$client_redis = new Redis();
$client_redis->connect(REDIS_SERVER_IP_DAOLIANG_CONFIG);

// 获取现有配置
$curr_ratio_str = $client_redis->get($rateConfigKey);
$curr_country_arr = $client_redis->hGetAll($countryConfigKey);

$serverConfArr = explode(';',$curr_ratio_str);
foreach($serverConfArr as $serverItem) {
	$idRatioArr = explode(':', $serverItem);
	$curr_ratio_arr[$idRatioArr[0]] = $idRatioArr[1];
}
foreach ($curr_country_arr as $cid => $ccountry) {
	if (!isset($curr_ratio_arr[$cid])) {
		$curr_ratio_arr[$cid] = 0;
	}
}

echo_msg("当前导量配置：".concat_numbers(array_keys($curr_ratio_arr))). " (共".count($curr_ratio_arr)."台)";

$select_maxsid_sql = "select max(svr_id) max_sid from cokdb_admin_deploy.tbl_webserver where is_test=0";
$result = do_query_global_db($select_maxsid_sql);
$max_sid_db = $result[0]['max_sid'];

$daoliang_sid_list = array_keys($curr_ratio_arr);
$max_sid = max($daoliang_sid_list);

// maybe deploy is in processing.
if ($max_sid < $max_sid_db) {
	echo_msg("daoliangmax=$max_sid < dbmax=$max_sid_db. deploy is in processing.");
	send_notify_mail('FAILED', '');
	exit();
}

$pre_sid = $max_sid - 1;
$next_sid = $max_sid + 1;
echo_msg("当前最大服=".$max_sid." 当前次大服=".$pre_sid);
echo_msg("此次开服目标服=".$next_sid);

if ($next_sid > 900000) {
	echo_msg("reached the MAX server count. $next_sid.");
	send_notify_mail('FAILED', '');
	exit();
}

// check domain && pub ip
$onl_status = is_online($next_sid);
if (!$onl_status) {
	echo_msg("检查域名绑定......\t\t FAILED");
	send_notify_mail('FAILED', '');
	exit();
}else{
	echo_msg("检查域名绑定......\t\t OK");
}

// check server status
$svr_status = check_server_status($next_sid);
if (!$svr_status) {
	echo_msg("检查游戏服务器......\t\t FAILED");
	send_notify_mail('FAILED', '');
	exit();
}else{
	echo_msg("检查游戏服务器......\t\t OK");
}

// check db status
$db_status = check_db_status($next_sid);
if (!$db_status) {
	echo_msg("检查DB服务器......\t\t FAILED");
	send_notify_mail('FAILED', '');
	exit();
}else{
	echo_msg("检查DB服务器......\t\t OK");
}

// update dbstatus
//$update_sql = "update cokdb_admin_deploy.tbl_webserver set is_hot=0 where svr_id = $pre_sid;";
//$ret = do_query_global_db($update_sql);
//echo_msg("去掉老服HOT标.s".$pre_sid."......\t\t OK");

$update_sql = "update cokdb_admin_deploy.tbl_webserver set is_hot=1 where svr_id = $next_sid;";
$ret = do_query_global_db($update_sql);
echo_msg("设置新服HOT标......\t\t OK");

$update_sql = "update cokdb_admin_deploy.tbl_webserver set is_test=0 where svr_id = $next_sid;";
$ret = do_query_global_db($update_sql);
echo_msg("设置新服上线状态......\t\t OK");

// set new rate
if (in_array($next_sid, array(618,619,620,669,670,671))) {
	$curr_ratio_arr[$next_sid] = 0;
}else{
	$curr_ratio_arr[$next_sid] = 10;
}
$curr_country_arr[$next_sid] = 'CN';
$client_redis->hSet($countryConfigKey, $next_sid, 'CN');

$modifyRate = array();
foreach ($curr_ratio_arr as $serverId=>$serverRate){
	$modifyRate[] = implode(":", array($serverId,$serverRate));
}
$new_ratio = implode(";", $modifyRate);
$ret = $client_redis->set($rateConfigKey, $new_ratio);
echo_msg("设置新导量配置：".concat_numbers(array_keys($curr_ratio_arr)). " (共".count($curr_ratio_arr)."台)"."......\t\t OK");

// update server_info.
$newDateTime = time()*1000;
$activityTime=$newDateTime+86400000*3;
$shuaguaiActStart=$newDateTime+86400000*2;
$openTime=$newDateTime+86400000*55;
$kingSql="insert into activity(id,name,type,openTime) values ('110001','wangweizhengduozhan',0,$openTime) ON DUPLICATE KEY update openTime = $openTime";
update_sql($next_sid,$kingSql);
$monsterSql="insert into activity(id,name,type,openTime) values ('110004','MonsterSiege',4,$openTime) ON DUPLICATE KEY update openTime = $openTime";
update_sql($next_sid,$monsterSql);
$modifySql = "insert into server_info(uid,yangfu,daoliangStart,activityTime,shuaguaiActStart) values ('server',$newDateTime,$newDateTime,$activityTime,$shuaguaiActStart)
ON DUPLICATE KEY update yangfu=$newDateTime,daoliangStart=$newDateTime,activityTime=$activityTime,shuaguaiActStart=$shuaguaiActStart";
update_sql($next_sid,$modifySql);
echo_msg("更新数据库：开服时间、活动时间....... \t\t OK");

$modifySql = "update cokdb_global.server_info set daoliangStart=$newDateTime where id=$next_sid";
do_query_global_db($modifySql);
echo_msg("更新全局数据库：新服的开服时间....... \t\t OK");

// set current max server id to all server. 
$clientsfs = new Redis();
$sipid_mapping = array();
foreach ($server_list as $sinfo) {
	if ($sinfo['svr_id'] == 0) {
		continue;
	}
	if ($sinfo['svr_id'] >= 999001) {
		continue;
	}
	$sfsip = $sinfo['ip_inner'];
	$sipid_mapping["root@$sfsip"] = "s{$sinfo['svr_id']}";
	try {
		$clientsfs->connect($sfsip);
		$clientsfs->set('latest_server', $next_sid);
	} catch (Exception $e) {
		echo "s{$sinfo['svr_id']} $sfsip set latest_server error".$e->getMessage()."\n";
	}
}
echo_msg("更新所有服Redis：最新服为 ".$next_sid."....... \t\t OK");

// upload new servers.xml to all server.
echo_msg("生成并发送 servers.xml 到所有服，使所有服在世界中能够看到 新开服......");
$cmd = '/home/elex/php/bin/php /publish/scripts/generate_servers.xml.php output_config_dir=/publish/update/config';
$ret = exec ( $cmd, $out, $status );
if ($status !== 0 || $ret != 'done. /publish/update/config/servers.xml') {
	echo_msg("...生成 NG");
}else{
	echo_msg("...servers.xml 生成 OK");
	$fmt = check_xml_format('/publish/update/config/servers.xml');
	if ($fmt) {
		echo_msg("...servers.xml 格式检查 OK");
		$cmd = 'cd /usr/local/cok/SFS2X && /usr/bin/fab -P uploadServersXmlOnly > /tmp/fab.txt 2>&1';
		exec ( $cmd, $out, $status );
		if ($status !== 0) {
// 			echo_msg("...发送至全服 部分失败. 【重试】......");
			$retryresult = 'OK';
			$cmd = "cat /tmp/fab.txt | grep 'Parallel execution exception'";
			unset($out);
			exec ( $cmd, $out, $status );
			$pattern = '/root@\d+.\d+.\d+.\d+/';
			foreach ($out as $errline) {
				$matches = array();
				$ret = preg_match($pattern, $errline, $matches);
				if ($ret && count($matches)) {
					$roothost = $matches[0];
					$cmd = "cd /usr/local/cok/SFS2X && /usr/bin/fab uploadServersXmlOnly:host=$roothost";
					$shr = shell_exec($cmd);
					if ($shr === null) {
						echo_msg("......{$sipid_mapping[$roothost]} => ERROR.");
						$retryresult = 'NG';
					}else{
						echo "......{$sipid_mapping[$roothost]} => OK.\n";
					}
				}
			}
			if ($retryresult == 'NG') {
				echo_msg("...servers.xml 发送至全服 【部分服 失败】");
			}else {
				echo_msg("...servers.xml 发送至全服 OK.");
			}
		}else{
			echo_msg("...servers.xml 发送至全服 OK.");
		}
	}else{
		echo_msg("...servers.xml 格式检查 ERROR. 【格式错误，停止发送servers.xml到全服。失败。】");
	}
}

echo_msg("开服完成.");
send_notify_mail('OK', '');
exit();

// funcs.
function do_query_global_db($sql){
	$re = query_global_deploy_db($sql);
	$mark = $re?'OK':'NG';
	echo $sql." ".$mark."\n";
	return $re;
}
function check_server_status($sid) {
	$svrinfo = get_server_info($sid);
	if (empty($svrinfo)) {
		return false;
	}
	$ip = $svrinfo['ip_inner'];
	$rc = new Redis();
	$ret = $rc->connect($ip, 6379, 3);
	if ($ret === false) {
		return false;
	}
	return true;
}
function check_db_status($sid) {
	global $dbLink;
	$dbinfo = $dbLink[$sid];
	$dbname = $dbinfo['dbname'];
	$sql = "select count(*) rowcnt from $dbname.server_info";
	$recs = query_game_db($dbinfo,$sql);
	if ($recs[0]['rowcnt'] >= 1) {
		return true;
	}
	return false;
}
function get_online($sid) {
	return 'N/A';
}
function get_reg($sid) {
	global $dbLink;
	$dbinfo = $dbLink[$sid];
	$dbname = $dbinfo['dbname'];
	$sql = "select count(*) regtotal from $dbname.stat_reg";
	$recs = query_game_db($dbinfo,$sql);
	return $recs[0]['regtotal'];
}
function select_valid_server($curr_ratio_arr){
	global $except_servers;
	$curr_servers = array_keys($curr_ratio_arr);
	$max_sid = max($curr_servers);
	$target_sid = $max_sid + 1;
	while (in_array($target_sid, $except_servers)) {
		$target_sid++;
	}
	return $target_sid;
}
function is_online($sid) {
	$dns = "s$sid.cok.elexapp.com";
	$retval = 0;
	system("ping -c1 -q -w1 $dns > /dev/null 2>&1", $retval);
	if($retval !== 0){
		return false;
	}
	$host_ip = gethostbyname($dns);
	$svrinfo = get_server_info($sid);
	if (empty($svrinfo)) {
		return false;
	}
	$ip = $svrinfo['ip_pub'];
	echo "gethostbyname=$host_ip def=$ip \n";
	return $host_ip == $ip;
}
function update_sql($sid,$sql) {
	global $dbLink;
	$dbinfo = $dbLink[$sid];
	$dbname = $dbinfo['dbname'];
	$recs = query_game_db($dbinfo,$sql,$dbname);
	echo $sql." ret=".$recs."\n";
	return $recs;
}

function send_notify_mail($subject, $txt){
	global $next_sid, $global_mail_lines;
	$date = date('Y-m-d H:i');

	$subject = "[COK开服][$date] s$next_sid $subject";
	$content = implode('<br>', $global_mail_lines);
	$ret = cokcore_mailer_send_mail('daoliang', $subject, $content);
	echo_msg("send mail -> $ret");
}
function check_xml_format($xmlfile) {
	if (!file_exists($xmlfile)) {
		return false;
	}
	$doc = @simplexml_load_file($xmlfile);
	if ($doc) {
		return true; //this is valid
	} else {
		return false; //this is not valid
	}
}

function echo_msg($msg=NULL,$arr=NULL){
	global $global_mail_lines;
	if ($msg !== null) {
		$pmsg = date('[Y-m-d H:i:s]')." $msg";
		echo $pmsg.PHP_EOL;
		$global_mail_lines[] = $pmsg;
	}
	if ($arr !== null) {
		echo date('[Y-m-d H:i:s]')." ARRAY->".PHP_EOL;
		print_r($arr);
	}
}

function concat_numbers($data, $delimiter=',') {
	if (!is_array($data)) {
		$data = explode($delimiter, $data);
	}
	sort($data);
	$i=$j=$k=0;
	$max = count($data) - 1;
	$seg = array();
	while ($i<$max && $j<=$max) {
		while ($j<=$max && $data[$j] == $data[$i] + $k) {
			$k++;
			$j++;
		}
		if ($k==1) {
			$seg[] = $data[$i];
		}else{
			$seg[] = "{$data[$i]}-{$data[$j-1]}";
		}
		$k=0;
		$i=$j;
	}
	return implode($delimiter, $seg);
}

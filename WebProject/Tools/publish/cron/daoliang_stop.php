<?php
/*
『导量自动化』之
	·停止导量

停止导量策略：（GM可配）
	· 阈值：注册用户>N(90000)，同时在线>N(2500)
*/

define('ROOT', dirname(__DIR__));
date_default_timezone_set('UTC');
set_time_limit(0);
ini_set('memory_limit', '512M');
error_reporting(0);

define('REDIS_SERVER_IP_DAOLIANG_CONFIG', 'IP');
$rateConfigKey = "RATIO_OF_CHOOSE_SERVER";
$countryConfigKey = "COUNTRY_OF_CHOOSE_SERVER";

$except_servers = array();// 152,188,191,194,197,199,229,236,289,305 Only for web game FB

$open = true;
$threshold_reg_default = 80000;
$threshold_reg_special = array(
		500 => 70000,
		419 => 140000,
		430 => 140000,
		508 => 110000,//jp
		509 => 110000,//jp
		521 => 110000,//jp
		522 => 100000,//jp
		580 => 90000,
		581 => 90000,
		582 => 90000,
		636 => 110000,//JP
		637 => 110000,//JP
		641 => 110000,//JP
		632 => 80000,
		633 => 80000,
		634 => 80000,
		635 => 80000,
);
$threshold_online = 3000;
$parallel_cnt = 11;
$rate = 'AVG';
$country = array('JP'=>3);
$hot = array(1,1,0,0,0,0,0,0,0,0,0);

require_once ROOT.'/db/db.inc.php';
$db_list = get_db_list();
$dbLink = array();
foreach ($db_list as $dbinfo) {
	$sid = $dbinfo['db_id'];
	$masterdef = array(
			'host'=>$dbinfo['ip_inner'],
			'port'=>$dbinfo['port'],
			'user'=>'root',
			'password'=>'t9qUzJh1uICZkA',
			'dbname'=>$dbinfo['dbname'],
	);
	$dbLink[$sid] = $masterdef;
}

$client_redis = new Redis();
$client_redis->connect(REDIS_SERVER_IP_DAOLIANG_CONFIG);

// 获取现有配置
$curr_ratio_str = $client_redis->get($rateConfigKey);
$curr_country_arr = $client_redis->hGetAll($countryConfigKey);

echo date('Y-m-d H:i:s ')."$rateConfigKey -> $curr_ratio_str\n";

$serverConfArr = explode(';',$curr_ratio_str);
foreach($serverConfArr as $serverItem) {
	$idRatioArr = explode(':', $serverItem);
	$curr_ratio_arr[$idRatioArr[0]] = $idRatioArr[1];
}

// 若只有两个导量服，则不处理。以防意外。
if (count($curr_ratio_arr) <= 10) {
	echo date('Y-m-d H:i:s ')."WARNING! daoliang server is TOO-LESS!\n";
	exit();
}

// 超过阈值的服列表
$status_curr_overload = array();
$mail_details = array();
foreach ($curr_ratio_arr as $sid => $ratio) {
	$overload = 0;
	$online = get_online($sid);
	$reg = get_reg($sid);
	
	if ($online >= $threshold_online) {
		$overload = true;
	}
	
	$reg_max = $threshold_reg_default;
	if (isset($threshold_reg_special[$sid])) {
		$reg_max = $threshold_reg_special[$sid];
	}
	if ($reg >= $reg_max) {
		$overload = true;
	}
	
	if ($overload) {
		if(in_array($sid, $except_servers)){
			echo date('Y-m-d H:i:s ')."WARNING! $sid reg=$reg online=$online -> overload=$overload\n";
		}else{
			$status_curr_overload[] = $sid;
		}
	}
	
	echo date('Y-m-d H:i:s ')."$sid reg=$reg online=$online -> overload=$overload\n";
	$mail_details[] = "$sid reg=$reg online=$online -> overload=$overload";
}

if (count($status_curr_overload) > 0) {
	foreach ($status_curr_overload as $curr_sid) {
		// save the config to redis.
		unset($curr_ratio_arr[$curr_sid]);
		if (isset($curr_country_arr[$curr_sid])) {
			$sidcountry = $curr_country_arr[$curr_sid];
			if ($sidcountry == 'CN') {
				$client_redis->hDel($countryConfigKey, $curr_sid);
			}else{
				if (strpos($sidcountry, 'CN') !== false) {
					$temp = str_replace('CN', '', $sidcountry);
					$temp = str_replace(',,', ',', $temp);
					$newsidcountry = trim($temp, ',');
					if (empty($newsidcountry)) {
						$client_redis->hDel($countryConfigKey, $curr_sid);
					}else{
						$client_redis->hSet($countryConfigKey, $curr_sid, $newsidcountry);
					}
				}
			}
		}
		
		$daoliangEnd = time()*1000;
		$modifySql = "update server_info set daoliangEnd=$daoliangEnd where uid='server'";
		update_sql($curr_sid,$modifySql);
		
		$modifySql = "update cokdb_global.server_info set daoliangEnd=$daoliangEnd where id=$curr_sid";
		do_query_global_db($modifySql);
	}
	$modifyRate = array();
	foreach ($curr_ratio_arr as $serverId=>$serverRate){
		$modifyRate[] = implode(":", array($serverId,$serverRate));
	}
	$new_ratio = implode(";", $modifyRate);
	
	echo date('Y-m-d H:i:s ')."$rateConfigKey -> $new_ratio\n";
	$ret = $client_redis->set($rateConfigKey, $new_ratio);
}

exit();

// funcs.
function do_query_global_db($sql){
	$re = query_global_deploy_db($sql);
	$mark = $re?'OK':'NG';
	echo $sql." ".$mark."\n";
	return $re;
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

function update_sql($sid,$sql) {
	global $dbLink;
	$dbinfo = $dbLink[$sid];
	$dbname = $dbinfo['dbname'];
	$recs = query_game_db($dbinfo,$sql,$dbname);
	echo $sql." ret=".$recs."\n";
	return $recs;
}
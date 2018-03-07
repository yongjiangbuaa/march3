<?php
defined('STATS_ROOT') || define('STATS_ROOT', dirname(__DIR__));
require_once STATS_ROOT.'/stats.inc.php';

$sid = intval($_REQUEST['sid']);
if ($sid <= 0) {
	$sid = 1;
}

if (defined('SERVER_ID')) {
	throw new Exception('Constant SERVER_ID definition invalid.');
}
define('SERVER_ID', $sid);
define('SERVER_MARK', 's'.$sid);
define('IB_DB_NAME_SNAPSHOT', 'snapshot_'.SERVER_MARK);
define('IB_DB_NAME_STAT', 'stat_'.SERVER_MARK);
define('IB_DB_NAME_STAT_ALLSERVER', 'stat_allserver');
define('LOG_CONDITION', "");

$GLOBALS['slave_db'] = changedbinfoformat($dbLink[SERVER_MARK]['slave']);
$GLOBALS['slave_db_global'] = changedbinfoformat($dbLink['global']['slave']);
$GLOBALS['slave_db_global_cobar'] = changedbinfoformat($dbLink['global_cobar_account']['slave']);
$GLOBALS['master_db'] = changedbinfoformat($dbLink[SERVER_MARK]['main']);

define('COK_DB_NAME', $dbLink[SERVER_MARK]['main'][4]);


if (!defined('SERVER_ID') || !defined('COK_DB_NAME')) {
	throw new Exception("Constant DB_NAME or SERVER_ID not defined.");
}else{
// 	echo SERVER_ID. ' ' . COK_DB_NAME."\n";
}

if (strtoupper(substr(PHP_OS,0,3))=='WIN') {
	define('MYSQL_CMD_TPL', 'mysql -h%s -u%s -p"%s" %s --skip-column-names -e "%s"%s');
} else {
	define('MYSQL_CMD_TPL', '/home/elex/mysql/bin/mysql -h%s -u%s -p"%s" %s --skip-column-names -e "%s"%s');
}

define('STATS_DB', 'stats_db');
define('SLAVE_DB', 'slave_db');
define('MASTER_DB', 'master_db');

function changedbinfoformat($dbinfo){
	return array(
			'host'=>$dbinfo[0],
			'user'=>$dbinfo[2],
			'password'=>$dbinfo[3],
			'port'=>$dbinfo[1],
			'dbname'=>$dbinfo[4],
	);
}
function getMaxserver(){
	global $dbLink;
	$serverArr = array();
	foreach($dbLink as $server=>$value){
		$serverArr[] = intval(substr($server,1));
	}
	$maxserver = max($serverArr);
	return $maxserver;
}
function build_mysql_cmd($config, $cmd, $file = null, $append = true, $dsn=null)
{
	if (empty($dsn)) {
		$dsn = $GLOBALS[$config];
	}
	$pipe_cmd = '';
	if (!empty($file)) {
		if ($append) {
			$pipe_cmd .= ' >> ';
		} else {
			$pipe_cmd .= ' > ';
		}
		$pipe_cmd .= $file;
	}
	$host = $dsn['host'];
	$port = $dsn['port'];
	if (isset($dsn['slave'])) {
		$t = explode(':', $dsn['slave']);
		$host = $t[0];
		$port = 0;
		if (isset($t[1])) {
			$port = intval($t[1]);
		}
	}
	return vsprintf(MYSQL_CMD_TPL, array(
		$host,
		$dsn['user'],
		$dsn['password'],
		$port > 0 ? "-P{$port}" : "",
		$cmd,
		$pipe_cmd
	));
}
function get_process_maxtime($name, $db = null, $field_name='time') {
	$id = md5($name);
	$name = elex_addslashes($name);
	if (is_null($db)) {
		$db = DB_NAME;
	}
	$data = query_infobright("SELECT max($field_name) offset FROM `$db`.`$name`", true);
	$ret = 0;
	if (!empty($data)) {
		$ret = intval($data[0]['offset']);
	}
//	if(empty($ret)){
//		$ret = time()*1000;
//	}
	return $ret+1;
}
function get_process_offset($name, $db = null) {
	$id = md5($name);
	$name = elex_addslashes($name);
	if (is_null($db)) {
		$db = DB_NAME;
	}
	$data = query_infobright("SELECT * FROM `$db`.`process_offset` WHERE process_id='$id'", true);
	$ret = 0;
	if (!empty($data)) {
		$ret = intval($data[0]['offset']);
	}
	return $ret;
}
function update_process_offset($name, $value, $db = null) {
	$id = md5($name);
	$name = elex_addslashes($name);
	if (is_null($db)) {
		$db = DB_NAME;
	}
	query_infobright("INSERT INTO `$db`.`process_offset` VALUES ('$id', '$name', $value) ON DUPLICATE KEY UPDATE `offset`=VALUES(`offset`),`name`=VALUES(`name`)");
}
function get_process_filemtime($name, $db = null) {
	$id = md5($name);
	$name = elex_addslashes($name);
	if (is_null($db)) {
		$db = DB_NAME;
	}
	$data = query_infobright("SELECT * FROM `$db`.`process_filemtime` WHERE process_id='$id'", true);
	$ret = 0;
	if (!empty($data)) {
		$ret = intval($data[0]['offset']);
	}
	return $ret;
}
function update_process_filemtime($name, $value, $db = null) {
	$id = md5($name);
	$name = elex_addslashes($name);
	if (is_null($db)) {
		$db = DB_NAME;
	}
	query_infobright("INSERT INTO `$db`.`process_filemtime` VALUES ('$id', '$name', $value) ON DUPLICATE KEY UPDATE `offset`=VALUES(`offset`),`name`=VALUES(`name`)");
}

function query_infobright($sql){
	global $stats_db;
	$client = new mysqli($stats_db['host'],$stats_db['user'],$stats_db['password'],'',$stats_db['port']);
	if ($client->connect_errno) {
		$sql = str_replace("\n", '\n', $sql);
		$errno = $client->connect_errno;
		$errmsg = $client->connect_error;
		writeRunLog("[IB_ERR] $errno $errmsg $sql");
		return false;
	}
	$result = mysqli_query($client,$sql);
	if($result === false && 0 != mysqli_errno($client)){
		$errno = mysqli_errno($client);
		$errmsg = mysqli_error($client);
		$sql = str_replace("\n", '\n', $sql);
		writeRunLog("[IB_ERR] $errno $errmsg $sql");
		return false;
	}
	if (is_bool($result)) {
		return $result;
	}
	$ret = array();
	while ($row = mysqli_fetch_assoc($result)) {
		$ret[] = $row;
	}
	return $ret;
}
function query_infobright_new($client,$sql){

	$result = mysqli_query($client,$sql);
	if($result === false && 0 != mysqli_errno($client)){
		$errno = mysqli_errno($client);
		$errmsg = mysqli_error($client);
		$sql = str_replace("\n", '\n', $sql);
		writeRunLog("[IB_ERR] $errno $errmsg $sql");
		return false;
	}
	if (is_bool($result)) {
		return $result;
	}
	$ret = array();
	while ($row = mysqli_fetch_assoc($result)) {
		$ret[] = $row;
	}
	return $ret;
}
function getInfobrightConnect($message=null){
	global $stats_db;
	$client = new mysqli($stats_db['host'],$stats_db['user'],$stats_db['password'],'',$stats_db['port']);
	if ($client->connect_errno) {
		if($message){
			$message = str_replace("\n", '\n', $message);
		}else{
			$message ='';
		}
		$errno = $client->connect_errno;
		$errmsg = $client->connect_error;
		writeRunLog("[IB_ERR] $errno $errmsg $message");
		return false;
	}else{
		return $client;
	}
}
function check_stat_table_exist($dbname,$table_name){
	global $stats_db;
	$dbInfo = $stats_db;
	$dbInfo['dbname'] = $dbname;
	$mysqli = new mysqli($dbInfo['host'], $dbInfo['user'], $dbInfo['password'], $dbInfo['dbname'], $dbInfo['port']);
	if ($mysqli->connect_errno) {
		writeRunLog("Connect failed:" . $mysqli->connect_error);
		return null;
	}
	$sql = "show tables like '$table_name'";
	$result = $mysqli->query($sql);
	$data = array();
	if ($result && is_object($result)) {
		while ($row = $result->fetch_assoc()) {
			$data [] = $row;
		}
		$result->free();
	}
	$mysqli->close();
	return !empty($data);
}

function query_game_db($dbInfo,$sql) {
	$mysqli = new mysqli($dbInfo['host'], $dbInfo['user'], $dbInfo['password'], $dbInfo['dbname'], $dbInfo['port']);
	$result = $mysqli->query($sql);
	if(is_bool($result)){
		return $result;
	}
	$data = array();
	if ($result && is_object($result)) {
		/* fetch associative array */
		while ($row = $result->fetch_assoc()) {
			$data [] = $row;
		}
		/* free result set */
		$result->free();
	}
	$mysqli->close();
	return $data;

}

function get_game_db($sid, $mainDB=false){
	global $dbLink;
	$db = $dbLink['s'.$sid];
	if(!$mainDB){
		$dbInfo = $db['slave'];
	}else{
		$dbInfo = $db['main'];
	}
	$ret = null;
	if(!empty($dbInfo)){
		$ret['host'] = $dbInfo[0];
		$ret['port'] = $dbInfo[1];
		$ret['user'] = $dbInfo[2];
		$ret['password'] = $dbInfo[3];
		$ret['dbname'] = 'cokdb'.$sid;
	}
	return $ret;
}

function writeRunLog($msg){
	$logdir = '/home/data/log/scripts/runlog';
	if (!file_exists($logdir)) {
		mkdir($logdir,0775,TRUE);
	}
	file_put_contents($logdir.'/'.SERVER_MARK.'.log', date("[Y-m-d H:i:s]")." $msg\n", FILE_APPEND);
}

function write_pid_file($name, $server_id = null)
{
	if ($server_id === null) {
		$server_id = SERVER_ID;
	}
	$pidFile = '/tmp/' . $name . '_' . $server_id . '.pid';
	if (file_exists($pidFile)) {
		writeRunLog("================pid file exists '" . $pidFile . "' ===============");
		return false;
	}
	writeRunLog("================ create pid file '" . $pidFile . "' ===============");
	$s = file_put_contents($pidFile, $server_id);
	return $s > 0;
}

function remove_pid_file($name,$server_id = null)
{
	if ($server_id === null) {
		$server_id = SERVER_ID;
	}
	$pidFile = '/tmp/' . $name . '_' . $server_id . '.pid';
	if (file_exists($pidFile)) {
		unlink($pidFile);
		writeRunLog("================ unlink pid file '" . $pidFile . "' ===============");
	}
}

function list_files_absolute_recursive($path, &$list = array()) {
	$path = dir_path ( $path );
	$files = glob ( $path . '*' );
	foreach ( $files as $v ) {
		if (is_dir ( $v )) {
			$list = list_files_absolute_recursive ( $v, $list );
		}else{
			if(!is_link($v)){
				$list [] = $v;
			}
		}
	}
	return $list;
} 
function dir_path($path) {
	$path = str_replace ( '\\', '/', $path );
	if (substr ( $path, - 1 ) != '/')
		$path = $path . '/';
	return $path;
}

function get_file_line_count($file){
	$filelinescnt = `wc -l $file`;
	$filelinescnt = trim($filelinescnt);
	$temp = explode(' ', $filelinescnt);
	$offset = $temp[0];
	return $offset;
}
function elex_addslashes($params){
	if(get_magic_quotes_gpc()){
		return $params;
	}
	if(is_array($params)){
		return array_map('elex_addslashes',$params);
	}else{
		return addslashes($params);
	}
}

function dau_referrer($value) {
	if($value['pf']==null  || $value['pf']==''){
		$pf='roll';
		$country='roll';
		$referrer='roll';
		$appVersion='roll';
	}else{
		$pf=$value['pf'];
		if($value['country']==null || $value['country']==''){
			$country = "Unknown";
		}else{
			$country = $value['country'];
		}

		if($value['referrer']==null || $value['referrer']=='' || $value['referrer']=='Organic' || empty($value['referrer'])){
			$referrer = "nature";
		}elseif($value['referrer']=='facebook'||preg_match("/\\{\"app\":\d{1,},\"t\":\d{1,}}/", $value['referrer'])
			||preg_match("/\\{app:\d{1,},t:\d{1,}}/", $value['referrer'])){
			$referrer =  "facebook";
		}else{
			$referrer = $value['referrer'];
		}

		if($value['appVersion']==null || $value['appVersion']==''){
			$appVersion = "Unknown";
		}else{
			$appVersion = $value['appVersion'];
		}
	}
	return array($pf ,$country ,$referrer,$appVersion);
}
function monthList($start,$end){
	if(!is_numeric($start)||!is_numeric($end)||($end<$start)) return '';
	$start=date('Ym',$start);
	$end=date('Ym',$end);
	//转为时间戳
	$start=strtotime($start.'01');
	$end=strtotime($end.'01');
	$i=0;
	$d=array();
	while($start<=$end){
		//这里累加每个月的的总秒数 计算公式：上一月1号的时间戳秒数减去当前月的时间戳秒数
		$d[$i]=trim(date('Ym',$start),' ');
		$start+=strtotime('+1 month',$start)-$start;
		$i++;
	}
	return $d;
}
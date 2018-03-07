<?php
error_reporting(E_ALL ^ E_NOTICE);

define('GLOBAL_TEMPLATE_DBNAME', 'cokdb_template');

define('GLOBAL_DB_SERVER_IP', '10.82.60.173');
define('GLOBAL_DB_SERVER_USER', 'gow');
define('GLOBAL_DB_SERVER_PWD', 'ZPV48MZH6q9V8oVNtu');

//define('GLOBAL_DB_SERVER_IP', '10.1.16.211');
//define('GLOBAL_DB_SERVER_USER', 'cok');
//define('GLOBAL_DB_SERVER_PWD', '1234567');


$cokdb_admin_deploy_hostinfo = array(
		'host' => GLOBAL_DB_SERVER_IP,
		'user' => GLOBAL_DB_SERVER_USER,
		'password' => GLOBAL_DB_SERVER_PWD
);
if (PHP_OS == "WINNT" || PHP_OS == 'Darwin') {
	$cokdb_admin_deploy_hostinfo = array(
			'host' => GLOBAL_DB_SERVER_IP,
			'user' => GLOBAL_DB_SERVER_USER,
			'password' => GLOBAL_DB_SERVER_PWD
	);
}

if (!file_exists('/publish/db/cokdbsql/')) {
	mkdir('/publish/db/cokdbsql/', 0777, true);
}

function add_webserver($data) {
	$db_tbl = 'cokdb_admin_deploy.tbl_webserver';
	if (!isset($data['svr_id'])) {
		$id = get_next_id ( 'svr_id', $db_tbl );
	}else{
		$id = $data['svr_id'];
	}

	$ip_inner = $data ['ip_inner'];
	$port = isset ( $data ['port'] ) ? $data ['port'] : 9933;
	$svr_name = isset ( $data ['svr_name'] ) ? $data ['svr_name'] : "S$id";
	$db_info = array (
			'svr_id' => $id,
			'status' => 0,
			'svr_name' => $svr_name,
			'zone' => 'COK' . $id,
			'ip_inner' => $ip_inner,
			'ip_pub' => $data ['ip_pub'],
			'port' => $port,
			'ver_client' => $data ['ver_client'],
			'ver_server' => $data ['ver_server'],
			'db_ref' => $data ['db_ref'],
			'open_time' => isset($data ['open_time']) ? $data ['open_time'] : time (),
			'is_recommend' => $id==1?1:0,
			'is_new' => 1,
			'is_test' => $data ['is_test'],
			'create_time' => time (),
			'update_time' => time ()
	);
	$sql = build_insert_sql ( $db_tbl, $db_info );
	query_db ( $sql );
}

function add_dbserver($data) {
	$db_tbl = 'cokdb_admin_deploy.tbl_db';
	if (!isset ( $data ['db_id'] )) {
		$id = get_next_id ( 'db_id', $db_tbl );
	}else{
		$id = $data ['db_id'];
	}

	$ip_inner = $data ['ip_inner'];
	$port = isset ( $data ['port'] ) ? $data ['port'] : 3306;
	$dbname = isset ( $data ['dbname'] ) ? $data ['dbname'] : "cokdb$id";
	$db_info = array (
			'db_id' => $id,
			'status' => 0,
			'ip_inner' => $ip_inner,
			'port' => $port,
			'dbname' => $dbname,
			'db_ref' => "$ip_inner:$port/$dbname",
			'ip_pub' => $data ['ip_pub'],
			'create_time' => time (),
			'update_time' => time (),
			'slave_ip_inner' => $data['slave_ip_inner'],
			'slave_ip_pub' => $data['slave_ip_pub'],
	);
	$sql = build_insert_sql ( $db_tbl,$db_info );
	query_db ( $sql );
}

function get_db_info($db_id){
	$db_tbl = 'cokdb_admin_deploy.tbl_db';
	$sql = "select * from $db_tbl where db_id=$db_id";
	$ret = query_db($sql);
	return $ret[0];
}
function get_db_list(){
	$db_tbl = 'cokdb_admin_deploy.tbl_db';
	$sql = "select * from $db_tbl";
	$ret = query_db($sql);
	return $ret;
}

function get_server_list(){
	$db_tbl = 'cokdb_admin_deploy.tbl_webserver';
	$sql = "select * from $db_tbl";
	$ret = query_db($sql);
	return $ret;
}

function get_server_info($svr_id){
	$db_tbl = 'cokdb_admin_deploy.tbl_webserver';
	$sql = "select * from $db_tbl where svr_id=$svr_id";
	$ret = query_db($sql);
	return $ret[0];
}
function get_server_ip_inner($svr_id){
	$db_tbl = 'cokdb_admin_deploy.tbl_webserver';
	$sql = "select * from $db_tbl where svr_id=$svr_id";
	$ret = query_db($sql);
	return $ret[0]['ip_inner'];
}
function get_next_id($key, $db_tbl) {
	$sql = "select max($key) max_id from $db_tbl;";
	$data = query_db ( $sql );
	return $data [0] ['max_id'] + 1;
}
function get_curr_max_id($key, $db_tbl) {
	$sql = "select max($key) max_id from $db_tbl;";
	$data = query_db ( $sql );
	return intval($data [0] ['max_id']);
}
function build_insert_sql($dbtbl, $info) {
	$keys = array_keys ( $info );
	$vals = array_values ( $info );
	foreach ($vals as &$v) {
		$v = addslashes($v);
	}

	$fields = implode ( ',', $keys );
	$values = "'" . implode ( "','", $vals ) . "'";
	$sql = "REPLACE into $dbtbl ($fields) values($values) ";
	return $sql;
}
function build_insert_sql_notreplace($dbtbl, $info) {
	$keys = array_keys ( $info );
	$vals = array_values ( $info );
	foreach ($vals as &$v) {
		$v = addslashes($v);
	}

	$fields = implode ( ',', $keys );
	$values = "'" . implode ( "','", $vals ) . "'";
	$sql = "INSERT into $dbtbl ($fields) values($values) ";
	return $sql;
}
function build_insert_sql_update($dbtbl, $info) {
	$keys = array_keys ( $info );
	$vals = array_values ( $info );
	foreach ($vals as &$v) {
		$v = addslashes($v);
	}
	$updKv = buildUpdateSql($info);
	
	$fields = implode ( ',', $keys );
	$values = "'" . implode ( "','", $vals ) . "'";
	$sql = "INSERT into $dbtbl ($fields) values($values) ON DUPLICATE KEY update $updKv";
	return $sql;
}
function buildUpdateSql($kv){
	$all = array();
	foreach ($kv as $key => $value) {
		$v = addslashes($value);
		$all[] = "$key='$v'";
	}
	return implode(',', $all);
}


function query_db($sql) {
	global $cokdb_admin_deploy_hostinfo;
	$client = mysql_connect ( $cokdb_admin_deploy_hostinfo['host'], $cokdb_admin_deploy_hostinfo['user'], $cokdb_admin_deploy_hostinfo['password'] );
	$result = mysql_query ( $sql, $client );
	$ret = array ();
	while ( $row = mysql_fetch_assoc ( $result ) ) {
		$ret [] = $row;
	}
	mysql_free_result($result);
	return $ret;
}
function query_global_deploy_db($sql){
	global $cokdb_admin_deploy_hostinfo;
	$client = mysql_connect ( $cokdb_admin_deploy_hostinfo['host'], $cokdb_admin_deploy_hostinfo['user'], $cokdb_admin_deploy_hostinfo['password'] );
	$result = mysql_query ( $sql, $client );
	if (!is_resource($result)) {
		return $result;
	}
	$ret = array ();
	while ( $row = mysql_fetch_assoc ( $result ) ) {
		$ret [] = $row;
	}
	mysql_free_result($result);
	return $ret;
}
function query_game_db($dbinfo,$sql,$dbname=null) {
	$client = mysql_connect ( $dbinfo['host'], $dbinfo['user'], $dbinfo['password'] );
	if (!empty($dbname)) {
		mysql_select_db( $dbname, $client );
	}
	$result = mysql_query ( $sql, $client );
	if (!is_resource($result)) {
		return $result;
	}
	$ret = array ();
	while ( $row = mysql_fetch_assoc ( $result ) ) {
		$ret [] = $row;
	}
	mysql_free_result($result);
	return $ret;
}
function multiquery_game_db($dbinfo,$sql,$dbname=null) {
	$mysqli = new mysqli($dbinfo['host'], $dbinfo['user'], $dbinfo['password'], $dbname, 3306);
	$mysqli->multi_query($sql);
}
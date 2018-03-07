<?php
define("DB_DIR",dirname(dirname(dirname(__DIR__))));
include DB_DIR.'/db/db.config.php';

$cokdb_admin_deploy_hostinfo = array(
		'host' => GLOBAL_DB_SERVER_IP,
		'user' => GLOBAL_DB_SERVER_USER,
		'password' => GLOBAL_DB_SERVER_PWD
);

function get_db_info($db_id){
	$db_tbl = 'tbl_db';
	$sql = "select * from $db_tbl where db_id=$db_id";
	$ret = query_db_global($sql);
	return $ret[0];
}
function get_db_list(){
	$db_tbl = 'tbl_db';
	$sql = "select * from $db_tbl";
	$ret = query_db_global($sql);
	return $ret;
}

function get_server_list(){
	$db_tbl = 'tbl_webserver';
	$sql = "select * from $db_tbl";
	$ret = query_db_global($sql);
	return $ret;
}

function get_server_info($svr_id){
	$db_tbl = 'tbl_webserver';
	$sql = "select * from $db_tbl where svr_id=$svr_id";
	$ret = query_db_global($sql);
	return $ret[0];
}
function get_server_ip_inner($svr_id){
	$db_tbl = 'tbl_webserver';
	$sql = "select * from $db_tbl where svr_id=$svr_id";
	$ret = query_db_global($sql);
	return $ret[0]['ip_inner'];
}
function build_insert_sql($dbtbl, $info) {
	$keys = array_keys ( $info );
	$vals = array_values ( $info );

	$fields = implode ( ',', $keys );
	$values = "'" . implode ( "','", $vals ) . "'";
	$sql = "insert into $dbtbl ($fields) values($values)";
	return $sql;
}
function query_db_global($sql) {
	global $cokdb_admin_deploy_hostinfo;
	$client = new mysqli( $cokdb_admin_deploy_hostinfo['host'], $cokdb_admin_deploy_hostinfo['user'], $cokdb_admin_deploy_hostinfo['password'], GLOBAL_DEPLOY_DB_NAME );
	if($client->connect_error){
		return false;
	}
	$result = $client->query($sql);
	if(is_bool($result)){
		$client->close();
		return $result;
	}
	$ret = array ();
	while ( $row = $result->fetch_assoc() ) {
		$ret [] = $row;
	}
	$result->free();
	$client->close();
	return $ret;
}

<?php
error_reporting(E_ERROR);

define('GLOBAL_DB_SERVER_IP', 'DEPLOYIP');
define('GLOBAL_DB_SERVER_PWD', 'DBPWD');
define('GLOBAL_TEMPLATE_DBNAME', 'cokdb_template');

$cokdb_admin_deploy_hostinfo = array(
		'host' => GLOBAL_DB_SERVER_IP.':3306',
		'user' => 'root',
		'password' => 'DBPWD'
);

function get_db_info($db_id){
	$db_tbl = 'cokdb_admin_deploy.tbl_db';
	$sql = "select * from $db_tbl where db_id=$db_id";
	$ret = query_db_global($sql);
	return $ret[0];
}
function get_db_list(){
	$db_tbl = 'cokdb_admin_deploy.tbl_db';
	$sql = "select * from $db_tbl";
	$ret = query_db_global($sql);
	return $ret;
}

function get_server_list(){
	$db_tbl = 'cokdb_admin_deploy.tbl_webserver';
	$sql = "select * from $db_tbl";
	$ret = query_db_global($sql);
	return $ret;
}

function get_server_info($svr_id){
	$db_tbl = 'cokdb_admin_deploy.tbl_webserver';
	$sql = "select * from $db_tbl where svr_id=$svr_id";
	$ret = query_db_global($sql);
	return $ret[0];
}
function get_server_ip_inner($svr_id){
	$db_tbl = 'cokdb_admin_deploy.tbl_webserver';
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
	$client = mysql_connect ( $cokdb_admin_deploy_hostinfo['host'], $cokdb_admin_deploy_hostinfo['user'], $cokdb_admin_deploy_hostinfo['password'] );
	$result = mysql_query ( $sql, $client );
	$ret = array ();
	while ( $row = mysql_fetch_assoc ( $result ) ) {
		$ret [] = $row;
	}
	return $ret;
}

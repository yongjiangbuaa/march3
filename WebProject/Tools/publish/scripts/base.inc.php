<?php
set_time_limit ( 0 );
date_default_timezone_set('UTC');

ob_implicit_flush ( true );

//TODO
defined('GLOBAL_REDIS_SERVER_IP') || define('GLOBAL_REDIS_SERVER_IP', '');


defined ( 'PUBLISH_DIR' ) || define ( 'PUBLISH_DIR', dirname ( __DIR__ ) );
define ( 'TEMPLATE_DIR', PUBLISH_DIR . '/template' );
define ( 'TEMPLATE_CONFIG_DIR', PUBLISH_DIR . '/template/config' );

define ( 'PACKAGE_DIR', PUBLISH_DIR . '/packages' );
define ( 'PACKAGE_TGZ_DIR', PUBLISH_DIR . '/packages/tgz' );
define ( 'PACKAGE_SFS2X_DIR', PACKAGE_DIR . '/cok/SFS2X' );
define ( 'PACKAGE_SERVERCONFIG_DIR', PACKAGE_SFS2X_DIR . '/config' );
define ( 'PACKAGE_GAMECONFIG_DIR', PACKAGE_SFS2X_DIR . '/gameconfig' );
define ( 'PACKAGE_ZONES_DIR', PACKAGE_SFS2X_DIR . '/zones' );
define ( 'PACKAGE_RESOURCE_DIR', PACKAGE_SFS2X_DIR . '/resource' );

define ( 'SERVER_REMOTE_DIR', '/usr/local' );
define ( 'SERVER_REMOTE_SFS2X_DIR', SERVER_REMOTE_DIR . '/cok/SFS2X' );
define ( 'PREFIX_ZONE', 'COK' );
define ( 'PREFIX_DBNAME', 'cokdb' );


foreach ( $argv as $arg ) {
	$kv = explode ( '=', $arg, 2 );
	$_REQUEST [$kv [0]] = $kv [1];
}

// ****** common functions ******
function echo_realtime($message) {
	echo $message, PHP_EOL;
	ob_flush ();
}
function run_local_exec($cmd, &$out, &$status) {
	echo_realtime ( '[run_local_exec] ' . $cmd );
	echo_realtime ( '...' );
	unset ( $out );
	$ret = exec ( $cmd, $out, $status );
	if (0 == $status || (empty ( $ret ) && empty ( $out ))) {
		$std = $out;
		echo_realtime ( '[run_local_exec succ] ' );
	} else {
		$err = $out;
		echo_realtime ( '[run_local_exec err] status=' . $status );
	}
	return array (
			'std' => $std,
			'err' => $err 
	);
}
function run_remote_exec($cmd, $p_remote_ip=null) {
	if (!empty($p_remote_ip)) {
		$remote_ip = $p_remote_ip;
	}
	elseif (isset($_REQUEST['ref_server_ip_inner'])) {
		$remote_ip = $_REQUEST['ref_server_ip_inner'];
	}
	else{
		$remote_ip = $_REQUEST['server_ip_inner'];
	}
	$ssh_cmd = "ssh -t root@{$remote_ip} " . '"' . $cmd . '"';
	echo_realtime ( '[run] ' . $ssh_cmd );
	echo_realtime ( '...' );
	unset ( $out );
	$ret = exec ( $ssh_cmd, $out, $status );
	// var_dump($status);
	// var_dump($out);
	if (0 == $status || (empty ( $ret ) && empty ( $out ))) {
		$std = $out;
		echo_realtime ( '[ssh run succ] ' );
	} else {
		$err = $out;
		echo_realtime ( '[ssh run err] status=' . $status );
	}
	return array (
			'std' => $std,
			'err' => $err 
	);
}

function validate_params($need_params){
	foreach ( $need_params as $p ) {
		if (! isset ( $_REQUEST [$p] ) || empty ( $_REQUEST [$p] )) {
			die ( "param $p is need. --> " . implode ( ' ', $need_params ) . PHP_EOL );
		}
	}
}

// $ssh_connection = ssh2_connect ( $_REQUEST ['server_ip_inner'], 22 );
// ssh2_auth_password ( $ssh_connection, 'root', '8YDpCb4VppMOaqyk' );

// function run_ssh2_exec($cmd) {
// 	global $ssh_connection;
// 	$stdout_stream = ssh2_exec ( $ssh_connection, $cmd );
// 	$stderr_stream = ssh2_fetch_stream ( $stdout_stream, SSH2_STREAM_STDERR );
// 	// while($line = fgets($stderr_stream)) { flush(); echo $line."\n"; }
// 	// while($line = fgets($stdout_stream)) { flush(); echo $line."\n";}
// 	$std = stream_get_contents ( $stdout_stream );
// 	$err = stream_get_contents ( $stderr_stream );
// 	return array (
// 			'std' => $std,
// 			'err' => $err 
// 	);
// }
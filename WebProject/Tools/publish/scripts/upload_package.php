<?php
require_once 'base.inc.php';

$need_params = array (
		'server_ip_inner',
		'package_tar_name' 
);
validate_params($need_params);

$tar_name = $_REQUEST ['package_tar_name'];
$file_name = $tar_name;
if (! file_exists ( DEPLOY_PACKAGE_DIR . "/$file_name" )) {
	die ( "not exists. " . DEPLOY_PACKAGE_DIR . "/$file_name" . PHP_EOL );
}

$s_t = microtime ( true );

chdir ( DEPLOY_PACKAGE_DIR );
$remote_dir_file = SERVER_REMOTE_DIR . "/$file_name";
$cmd = "scp $file_name root@{$_REQUEST['server_ip_inner']}:{$remote_dir_file}";
run_local_exec($cmd, $out, $status);
if (0 == $status) {
	echo_realtime ( '[succ] ' . $remote_dir_file );
	
	$cmd = "tar -xzf " . SERVER_REMOTE_DIR . "/$file_name -C " . SERVER_REMOTE_DIR;
	run_remote_exec($cmd, $_REQUEST['server_ip_inner']);
} else {
	echo_realtime ( '[err1]' );
}
chdir ( PUBLISH_DIR );

echo_realtime ( "COMPLETE. use time (s) = " . (microtime ( true ) - $s_t) );

//<END>


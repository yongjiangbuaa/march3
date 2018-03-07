<?php
require_once 'base.inc.php';

$need_params = array (
		'ref_server_ip_inner' 
);
validate_params($need_params);

$s_t = microtime ( true );

$tar_name = $_REQUEST ['package_tar_name'];

// ssh -t root@10.41.163.18 "cd /usr/local; tar -czf /tmp/smartfoxserver-1409241902-10.41.163.18.tgz --exclude=cok/SFS2X/logs/*log* --exclude=cok/SFS2X/logs/*/*log* cok/"
$remote_file = '/tmp/' . $tar_name;
$cmd = "cd /usr/local; tar -czf $remote_file --exclude=cok/SFS2X/logs/*log* --exclude=cok/SFS2X/logs/*/*log*  --exclude=cok/SFS2X/deploy/*  --exclude=cok/backup --exclude=cok/ccsa-provider-1.0/logs  cok/";
run_remote_exec ( $cmd , $_REQUEST['ref_server_ip_inner']);

// scp root@10.41.163.18:/tmp/smartfoxserver-1409241902-10.41.163.18.tgz /publish/packages/tgz/
$cmd = "scp root@{$_REQUEST['ref_server_ip_inner']}:{$remote_file} " . PACKAGE_TGZ_DIR.'/';
run_local_exec ( $cmd, $out, $status );

if (file_exists(PACKAGE_DIR."/cok")) {
	$cmd = "rm -rf ".PACKAGE_DIR."/cok";
	run_local_exec ( $cmd, $out, $status );
}

// tar -xzf /publish/packages/tgz/smartfoxserver-1409241902-10.41.163.18.tgz -C /publish/packages
$cmd = "tar -xzf ".PACKAGE_TGZ_DIR."/{$tar_name} -C " . PACKAGE_DIR;
run_local_exec ( $cmd, $out, $status );

echo_realtime ( "COMPLETE. use time (s) = " . (microtime ( true ) - $s_t) );

//<END>




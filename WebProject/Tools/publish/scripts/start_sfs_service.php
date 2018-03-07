<?php
require_once 'base.inc.php';

$need_params = array (
		'server_ip_inner',
);
validate_params($need_params);

$s_t = microtime ( true );

// $exp = "export SFS2X=". SERVER_REMOTE_SFS2X_DIR;
// $cmd = "echo $exp >> /root/.bashrc";
// run_remote_exec($cmd);

// $exp = 'export PATH=$PATH:/usr/local/cok/jre/bin';
// $cmd = "echo $exp >> /root/.bashrc";
// run_remote_exec($cmd);

$cmd = SERVER_REMOTE_SFS2X_DIR. '/sfs2x-service start && sleep 10';
run_remote_exec($cmd);

echo_realtime ( "COMPLETE. use time (s) = " . (microtime ( true ) - $s_t) );

//<END>

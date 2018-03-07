<?php
require_once 'base.inc.php';

$need_params = array (
		'server_ip_inner' 
);
validate_params($need_params);

$s_t = microtime ( true );

if (empty($_REQUEST ['package_tar_name'])) {
	$curr_date = date ( 'ymdHi' );
	$tar_name = "smartfoxserver-{$curr_date}_{$_REQUEST['server_ip_inner']}.tgz";
	$_REQUEST ['package_tar_name'] = $tar_name;
}else{
	$tar_name = $_REQUEST ['package_tar_name'];
}
chdir ( PACKAGE_DIR );
$file_name = DEPLOY_PACKAGE_DIR.'/'.$tar_name;
$source_dir = 'cok';

$tar_cmd = "tar -czf $file_name  --exclude=cok/SFS2X/logs/*log* --exclude=cok/SFS2X/logs/*/*log* $source_dir ";
echo_realtime ( '[run] ' . $tar_cmd );
echo_realtime ( '...' );
exec ( $tar_cmd, $out, $status );
if (0 == $status) {
	if (file_exists ( $file_name )) {
		echo_realtime ( '[succ] ' . "$file_name" );
	} else {
		echo_realtime ( '[err1]' );
	}
} else {
	echo_realtime ( '[err2]' );
}
chdir ( PUBLISH_DIR );

echo_realtime ( "COMPLETE. use time (s) = " . (microtime ( true ) - $s_t) );

//<END>




<?php
define ( 'PUBLISH_DIR', dirname ( __DIR__ ) );
define ( 'SCRIPTS_DIR', dirname ( __DIR__ ) .'/scripts');
define ( 'DBSCRIPTS_DIR', dirname ( __DIR__ ) .'/db');

require_once SCRIPTS_DIR.'/base.inc.php';
require_once DBSCRIPTS_DIR.'/db.inc.php';

echo str_pad ( " ", 256 ) . PHP_EOL;

define ( 'CONFIG_DIR', PUBLISH_DIR.'/kaifu/config' );
define ( 'DEPLOY_PACKAGE_DIR', PUBLISH_DIR.'/kaifu/package' );

if (! isset ( $_REQUEST ['ref_server_id'] ) || $_REQUEST ['ref_server_id'] == 'latest') {
	$db_tbl = 'cokdb_admin_deploy.tbl_webserver';
	$curr_max_server_id = get_curr_max_id( 'svr_id', $db_tbl );
	$_REQUEST ['ref_server_id'] = $curr_max_server_id;
}

$_REQUEST ['ref_server_ip_inner'] = get_server_ip_inner($_REQUEST ['ref_server_id']);
$curr_date = date ( 'ymd' );
$tar_name = "smartfoxserver-{$curr_date}_S{$_REQUEST ['ref_server_id']}_{$_REQUEST['ref_server_ip_inner']}.tgz";
$_REQUEST ['package_tar_name'] = $tar_name;

// process
$all_stime = microtime ( true );

echo_realtime ( "[".basename(__FILE__)."] start..." );
echo_realtime ( "REQUEST parameters:" );
print_r ( $_REQUEST );

echo_realtime ( "[prepare_get_latest_package] start..." );
require_once SCRIPTS_DIR.'/prepare_get_latest_package.php';
echo_realtime ( "[prepare_get_latest_package] end." );


echo_realtime ( "[".basename(__FILE__)."] end. ALL TIME (s) = " . (microtime ( true ) - $all_stime) );


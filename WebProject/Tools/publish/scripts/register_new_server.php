<?php
defined('PUBLISH_DIR') || define ( 'PUBLISH_DIR', dirname ( __DIR__ ) );
defined('SCRIPTS_DIR') || define ( 'SCRIPTS_DIR', dirname ( __DIR__ ) .'/scripts');
defined('DBSCRIPTS_DIR') || define ( 'DBSCRIPTS_DIR', dirname ( __DIR__ ) .'/db');

require_once SCRIPTS_DIR.'/base.inc.php';
require_once DBSCRIPTS_DIR.'/db.inc.php';

$need_params = array (
		'server_id',
		'server_ip_inner',
		'server_ip_pub',
		'db_ip_port',
);
validate_params($need_params);

$db_id = $_REQUEST['server_id'];
$db_info = get_db_info($db_id);
$db_ref = $db_info['db_ref'];
$old_svr_info = get_server_info($db_id);

$is_test = 1;
$open_time = time();
if (!empty($old_svr_info)) {
	$is_test = $old_svr_info['is_test'];
	$open_time = $old_svr_info['open_time'];
}

$data = array();
$data ['svr_id'] = $_REQUEST['server_id'];
$data ['ip_inner'] = $_REQUEST['server_ip_inner'];
$data ['ip_pub'] = $_REQUEST['server_ip_pub'];
$data ['ver_client'] = '';
$data ['ver_server'] = '';
$data ['db_ref'] = $db_ref;
$data ['is_test'] = $is_test;
$data ['open_time'] = $open_time;

add_webserver ($data);
unset($data);

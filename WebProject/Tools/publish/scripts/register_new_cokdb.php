<?php
defined('PUBLISH_DIR') || define ( 'PUBLISH_DIR', dirname ( __DIR__ ) );
defined('SCRIPTS_DIR') || define ( 'SCRIPTS_DIR', dirname ( __DIR__ ) .'/scripts');
defined('DBSCRIPTS_DIR') || define ( 'DBSCRIPTS_DIR', dirname ( __DIR__ ) .'/db');

require_once SCRIPTS_DIR.'/base.inc.php';
require_once DBSCRIPTS_DIR.'/db.inc.php';

$need_params = array (
		'server_id',
		'db_ip_inner',
		'db_ip_pub',
);
validate_params($need_params);

$db_id = $_REQUEST['server_id'];
$dbname = PREFIX_DBNAME.$db_id;

$data = array();
$data ['db_id'] = $db_id;
$data ['ip_inner'] = $_REQUEST['db_ip_inner'];
$data ['ip_pub'] = $_REQUEST['db_ip_pub'];
$data ['dbname'] = $dbname;
$data ['port'] = '3306';
$data ['slave_ip_inner'] = $_REQUEST['slave_ip_inner'];
$data ['slave_ip_pub'] = $_REQUEST['slave_ip_pub'];

add_dbserver($data);
unset($data);

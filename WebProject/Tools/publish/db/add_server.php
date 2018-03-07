#!/home/elex/php/bin/php
<?php
require_once './db.inc.php';

// foreach ( $temp as $value ) {
// 	$kv = explode ( "\t", $value );
// 	$_REQUEST ['ip_inner'] = $kv [0];
// 	$_REQUEST ['ip_pub'] = $kv [1];
// 	$_REQUEST ['ver_client'] = $kv [2];
// 	$_REQUEST ['ver_server'] = $kv [3];
// 	$_REQUEST ['db_ref'] = $kv [5];
// 	add_webserver ();
// }

$data = array();
$data['svr_id'] = 1;
$data['ip_inner'] = '10.1.16.211';
$data['port'] = 9933;
$data['svr_name'] = 'COK1-test';
$data['ip_pub'] = '10.1.16.211';
$data['ver_client'] = '';
$data['ver_server'] = '';
$data['db_ref'] = '10.1.16.211:3306/cokdb1';
$data['is_test'] = '1';

add_webserver ($data);


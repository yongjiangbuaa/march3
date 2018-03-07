#!/home/elex/php/bin/php
<?php
require_once './db.inc.php';

// foreach ( $temp as $value ) {
// 	$kv = explode ( "\t", $value );
// 	$_REQUEST ['ip_inner'] = $kv [1];
// 	$_REQUEST ['ip_pub'] = $kv [4];
// 	$_REQUEST ['dbname'] = $kv [3];
// 	$_REQUEST ['port'] = $kv [2];
// 	add_dbserver();
// }

$data = array();
$data['db_id'] = 1;
$data['ip_inner'] = '10.1.16.211';
$data['port'] = 3306;
$data['dbname'] = 'cokdb1';
$data['ip_pub'] = '10.1.16.211';
$data['slave_ip_inner'] = '10.1.16.211';
$data['slave_ip_pub'] = '10.1.16.211';

add_dbserver($data);

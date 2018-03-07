<?php
set_time_limit(0);
define('ADMIN_ROOT', '/data/htdocs/ifadmin/admin');

// http://cok.eleximg.com/cok/config/1.0.86/config_1.0.1480_zh_TW.zip
$cdnbase = 'http://cok.eleximg.com/cok/config';
$dir_updown = ADMIN_ROOT. '/cache/updown';
if (!file_exists($dir_updown)) {
	mkdir($dir_updown, 0777, true);
}

$notifyfile = $dir_updown.'/notify_verup.txt';

$appver = $_REQUEST['appver'];
$configver = $_REQUEST['configver'];

file_put_contents($notifyfile, "$appver=$configver");

echo "OK";
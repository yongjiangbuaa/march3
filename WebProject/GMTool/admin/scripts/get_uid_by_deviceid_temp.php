<?php
define('IN_ADMIN',true);
define('APP_ROOT', realpath(dirname(__DIR__) . '/../gameengine/test'));
define('ADMIN_ROOT', realpath(dirname(__DIR__) . '/'));
ini_set('mbstring.internal_encoding','UTF-8');
include ADMIN_ROOT.'/config.inc.php';
include ADMIN_ROOT.'/admins.php';
include_once ADMIN_ROOT.'/servers.php';
$page = new BasePage();
date_default_timezone_set('UTC');
set_time_limit(0);
ini_set('memory_limit', '1024M');


$handle = fopen('182.203.103.132.log', 'r');
while (($buffer = fgets($handle)) !== false) {
	$buffer = trim($buffer);
	if (empty($buffer)) continue;
	$d = explode(' ', $buffer);
	
	$deviceid = $d[3];
	
	$sql = "select gameuid from usermapping where mappingtype='device' and mappingvalue='$deviceid'";
	$rec = cobar_query_global_db_cobar($sql);
	$gameuid = $rec[0]['gameuid'];
	
	file_put_contents('182.203.103.132_uid.log', "$buffer $gameuid\n", FILE_APPEND);
}



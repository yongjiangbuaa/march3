<?php
$table = $_REQUEST ["table"];
$values = $_REQUEST ["values"];
$mysql_dir = realpath(dirname(__FILE__) . '/mysql');
//require_once realpath(dirname(__FILE__) . '/functions.php');
require_once $mysql_dir."/XMysql.php";
//$values['id'] = getGUID();
$mysqlParams = array(
		'mysql_host' => '10.18.6.16',
		'mysql_port' => '3306',
		'mysql_user' => 'root',
		'mysql_passwd' => 'admin123',
		'mysql_db' => 'warfaredb'
	);
$mysql = XMysql::singleton($mysqlParams)->addBatch($table,$values); 
echo 'OK';
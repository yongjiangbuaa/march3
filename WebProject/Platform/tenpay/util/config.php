<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
include dirname(__FILE__) . '/lib/DBUtil.php';

$appid = '1105514303';
$appkey = '9CAGGCdzuknDj1S0wcrJHCoF5wCOPP84';
//$appkey = 'jF6svP7Nx8y1gINBpfMcMqUmrvzdn64r';
$server = 'ysdk.qq.com';
//$server = 'ysdktest.qq.com';
//$server = '119.147.19.43';
//mysql -uroot -P3306 -h10.66.119.180 -pamSgufcl5898M
function getPDO(){
	$db_host		= '10.82.60.173';
	$db_port		= '3306';
	$db_user		= 'gow';
	$db_read_user	= 'cok_read';
	$db_password	= 'ZPV48MZH6q9V8oVNtu';
	$db_read_password	= 'hH5qJ2ql';
	$db_database	= 'cokdb_global';
	$dbParam = array(
			'mysql_host'=>$db_host,
			'mysql_port'=>$db_port,
			'mysql_user'=>$db_user,
			'mysql_passwd'=>$db_password,
			'mysql_db'=>$db_database,
	);
	$db = DBUtil::newInstance()->init($dbParam);
	if(!$db->isConnected())
		return null;
	return $db;
// $db_host		= '127.0.0.1';
// $db_port		= '3306';
// $db_user		= 'root';
// $db_read_user	= 'root';
// $db_password	= '';
// $db_read_password	= '';
// $db_database	= 'tenpay';
}
?>
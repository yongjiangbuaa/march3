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
$server_type = 0;
if ($db_id > 900000) {
	$server_type = 2;
}

// $svr = get_server_info($_REQUEST['ref_server_id']);
// $db_ref = $svr['db_ref'];//ip:3306/cokdb2
// $pos1 = strpos($db_ref, ':');
// $pos2 = strpos($db_ref, '/');
// $db_ref_ip = substr($db_ref, 0, $pos1);
// $db_ref_name = substr($db_ref, $pos2 + 1);
$db_ref_ip = GLOBAL_DB_SERVER_IP;
$db_ref_name = GLOBAL_TEMPLATE_DBNAME;

// # export database tables schema
$sqlfile = DBSCRIPTS_DIR."/cokdbsql/onlinedb_{$db_ref_name}.sql";
$cmd = "mysqldump --compact -uroot -p".GLOBAL_DB_SERVER_PWD." -h{$db_ref_ip} -d {$db_ref_name} > $sqlfile";
run_local_exec($cmd, $out, $status);

// # create database
$cmd = "mysql -uroot -p".GLOBAL_DB_SERVER_PWD." -h{$_REQUEST['db_ip_inner']} -e 'create database $dbname'";
run_local_exec($cmd, $out, $status);

// # create tables
$cmd ="mysql -uroot -p".GLOBAL_DB_SERVER_PWD." -h{$_REQUEST['db_ip_inner']} $dbname < $sqlfile";
run_local_exec($cmd, $out, $status);

$cmd ="mysql -uroot -p".GLOBAL_DB_SERVER_PWD." -h{$_REQUEST['db_ip_inner']} $dbname -e ".'"insert into server_info(uid,activityTime) values('."'server'".',0);"';
run_local_exec($cmd, $out, $status);

//$cmd ="mysql -uroot -p".GLOBAL_DB_SERVER_PWD." -h{$_REQUEST['db_ip_inner']} $dbname -e ".'"'."insert into activity (id,name,type,openTime,startTime,endTime) values (222, 'snowball', 1, 2, 2, 9223372036854775807),(333, 'sock', 3, 2, 2, 9223372036854775807),(444, 'login', 2, 2, 2, 9223372036854775807),(555,'AprilFoolsDay',5,1426521600000,1426521600000,9223372036854775807);".'"';
//run_local_exec($cmd, $out, $status);

$cmd ="mysql -uroot -p".GLOBAL_DB_SERVER_PWD." -h{$_REQUEST['db_ip_inner']} $dbname -e ".'"'."insert into switches values ('guide_skip_1', 1),('guide_skip_2',1);".'"';
run_local_exec($cmd, $out, $status);

$cmd ="mysql -uroot -p".GLOBAL_DB_SERVER_PWD." -h".GLOBAL_DB_SERVER_IP." cokdb_global -e ".'"'."insert into server_info (id,type) values ($db_id,$server_type);".'"';
run_local_exec($cmd, $out, $status);

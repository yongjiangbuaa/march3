<?php
define ( 'PUBLISH_DIR', dirname ( __DIR__ ) );
define ( 'SCRIPTS_DIR', dirname ( __DIR__ ) .'/scripts');
define ( 'DBSCRIPTS_DIR', dirname ( __DIR__ ) .'/db');

define ( 'CONFIG_DIR', PUBLISH_DIR.'/kaifu/config' );
define ( 'DEPLOY_PACKAGE_DIR', PUBLISH_DIR.'/kaifu/package' );

if (!file_exists(CONFIG_DIR)) {
	mkdir(CONFIG_DIR, 0777, true);
}
if (!file_exists(DEPLOY_PACKAGE_DIR)) {
	mkdir(DEPLOY_PACKAGE_DIR, 0777, true);
}

require_once SCRIPTS_DIR.'/base.inc.php';
require_once DBSCRIPTS_DIR.'/db.inc.php';

echo str_pad ( " ", 256 ) . PHP_EOL;

//验证参数
$need_params = array (
		'server_id_list',		//服编号
		'slave_ip_inner',		//DB服ip内网
		'slave_ip_pub',		//DB服ip外网
);
validate_params($need_params);


$servers = $_REQUEST['server_id_list'];//78,95
$slave_ip_inner = $_REQUEST['slave_ip_inner'];
$slave_ip_pub = $_REQUEST['slave_ip_pub'];

$sql = "select * from cokdb_admin_deploy.tbl_db where db_id in($servers);";
$ret = query_global_deploy_db($sql);
echo "CURR slave db\n";
print_r($ret);

$sql = "update cokdb_admin_deploy.tbl_db set slave_ip_inner='$slave_ip_inner',slave_ip_pub='$slave_ip_pub' where db_id in($servers);";
echo "$sql\n";
$ret = query_global_deploy_db($sql);

if ($ret) {
	echo "OK\n";
}else {
	echo "NG\n";
}

$sql = "select * from cokdb_admin_deploy.tbl_db where db_id in($servers);";
$ret = query_global_deploy_db($sql);
echo "NEW slave db\n";
print_r($ret);



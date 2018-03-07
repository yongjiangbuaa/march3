<?php

//『前提』
//		基本包OK。「执行prepare」
//		数据库模板OK。「cokdb_template」
//		发布管理数据库。 cokdb_admin_deploy 中，已经把原有的server和db收录完毕。
//		翻译账号。ms_account_list

// TOOD:
//		app vs clientxml version mapping. -> db
//		deploy|update time, version, comment -> db
//		ALL template use ->db and can edit at GM

//1[准备]、从「现役最新服ref_server_id」拉取tar包(/usr/local/cok logs排除)，放到/publish/packages/下，并解压。
//2、根据新服配置，编辑config
//3、tar包后上传到新服
//4、新服上解压、启动服务


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
		'server_id',		//服编号
		'server_ip_pub',	//应用服ip外网
		'server_ip_inner',	//应用服ip内网
		'db_ip_inner',		//DB服ip内网
		'db_ip_pub',		//DB服ip外网
);
validate_params($need_params);

//新服参数
$port = '3306';
$_REQUEST['db_ip_port'] = $_REQUEST['db_ip_inner'] .':'. $port;
if (! isset ( $_REQUEST ['db_name'] )) {
	$db_index = $_REQUEST ['server_id'];
	$db_name = PREFIX_DBNAME . $db_index;
	$_REQUEST ['db_name'] = $db_name; //数据库名称
}
$server_id = $_REQUEST ['server_id'];
$config_dir = CONFIG_DIR."/S$server_id";
$_REQUEST['output_config_dir'] = $config_dir;
if (!file_exists($config_dir)) {
	mkdir($config_dir, 0777, true);
}

//验证参数、环境
$exist = get_db_info($_REQUEST['server_id']);
if ($exist['ip_inner'] == $_REQUEST['db_ip_inner'] && $exist['port'] == $port && $exist['dbname'] == $db_name) {
	print_r($exist);
	die('dbserver ip '.$_REQUEST['db_ip_inner'].$_REQUEST['server_ip_pub']." $port $db_name".' exists.');
}

//服务器故障时修复，此时服的状态为 非test
$_REQUEST['is_test'] = 0;

// process
$all_stime = microtime ( true );
echo_realtime ( "[kaifu] start..." );
echo_realtime ( "REQUEST parameters:" );
print_r ( $_REQUEST );

//更新cokdb_admin_deploy.tbl_db
require_once SCRIPTS_DIR.'/register_new_cokdb.php';
//更新cokdb_admin_deploy.tbl_webserver
require_once SCRIPTS_DIR.'/register_new_server.php';

//根据模板sfs生成新sfs
$last_sid = 1;
foreach (glob(PACKAGE_SFS2X_DIR."/extensions/COK*") as $file) {
	if (strpos($file, 'COK')!==false) {
		$tokens = explode('/', $file);
		$cokname = $tokens[count($tokens)-1];
		$last_sid = substr($cokname, 3);
		if ($last_sid > 1) {
			break;
		}
	}
}
$cmd_arr = array();
$cmd_arr[] = "mv %s/extensions/COK$last_sid %s/extensions/COK$server_id";
$cmd_arr[] = "mv %s/gameconfig/rmiServer$last_sid.xml %s/gameconfig/rmiServer$server_id.xml";
$cmd_arr[] = "mv %s/zones/COK$last_sid.zone.xml %s/zones/COK$server_id.zone.xml";
foreach ($cmd_arr as $cmd) {
	$cmd = sprintf($cmd, PACKAGE_SFS2X_DIR, PACKAGE_SFS2X_DIR);
	run_local_exec($cmd);
}
$db_local_ip_port_dbname = "{$_REQUEST['db_ip_port']}\/$db_name";
run_local_exec("sed -i 's/<name>COK$last_sid<\/name>/<name>COK$server_id<\/name>/g' ".PACKAGE_ZONES_DIR."/COK$server_id.zone.xml", $out, $status);
run_local_exec("sed -i 's/<connectionString>jdbc:mysql.*/<connectionString>jdbc:mysql:\/\/$db_local_ip_port_dbname?characterEncoding=utf-8\&amp;autoReconnect=true<\/connectionString>/g' ".PACKAGE_ZONES_DIR."/COK$server_id.zone.xml", $out, $status);
run_local_exec("sed -i 's/-Djava.rmi.server.hostname=.*/-Djava.rmi.server.hostname={$_REQUEST['server_ip_inner']}/g' ".PACKAGE_SFS2X_DIR."/sfs2x-service.vmoptions", $out, $status);
$configfile = PACKAGE_SFS2X_DIR."/extensions/COK$server_id/config.properties";
run_local_exec("sed -i 's/realtime_local.jdbc.url=.*/realtime_local.jdbc.url=jdbc:mysql:\/\/$db_local_ip_port_dbname?characterEncoding=utf-8/g' ".$configfile);
run_local_exec("sed -i 's/syn_world_redis=0/syn_world_redis=1/g' ".$configfile);

//生成servers.xml/mybatis-crocess.xml/rmiClient.xml
require_once SCRIPTS_DIR.'/generate_servers.xml.php';
require_once SCRIPTS_DIR.'/generate_mybatis-cross.xml.php';
require_once SCRIPTS_DIR.'/generate_rmiClient.xml.php';
// 复制config文件到标准包
run_local_exec("cp -pf $config_dir/servers.xml ".PACKAGE_RESOURCE_DIR."/servers.xml", $out, $status);
run_local_exec("cp -pf $config_dir/mybatis-cross.xml ".PACKAGE_GAMECONFIG_DIR."/mybatis-cross.xml", $out, $status);
run_local_exec("cp -pf $config_dir/rmiClient.xml ".PACKAGE_GAMECONFIG_DIR."/rmiClient.xml", $out, $status);
// 所有服共用
run_local_exec("cp -pf $config_dir/servers.xml ".PUBLISH_DIR."/update/config/servers.xml", $out, $status);
run_local_exec("cp -pf $config_dir/mybatis-cross.xml ".PUBLISH_DIR."/update/config/mybatis-cross.xml", $out, $status);
run_local_exec("cp -pf $config_dir/rmiClient.xml ".PUBLISH_DIR."/update/config/rmiClient.xml", $out, $status);
run_local_exec("chmod a+w ".PUBLISH_DIR."/update/config/*.xml");
run_local_exec("chown elex:elex ".PUBLISH_DIR."/update/config/*.xml");

$curr_date = date ( 'ymd' );
$tar_name = "smartfoxserver-{$curr_date}_S{$_REQUEST ['server_id']}_{$_REQUEST['server_ip_inner']}.tgz";
$_REQUEST ['package_tar_name'] = $tar_name;
require_once SCRIPTS_DIR.'/tar_package.php';

require_once SCRIPTS_DIR.'/upload_package.php';

// delete Redis world{sid}
echo_realtime ( "[kaifu] redis delete world{$_REQUEST ['server_id']}" );
$client_redis = new Redis();
$client_redis->connect($_REQUEST['server_ip_inner']);
$client_redis->del("world{$_REQUEST ['server_id']}");

//require_once SCRIPTS_DIR.'/start_sfs_service.php';

//require_once SCRIPTS_DIR.'/check_service_status.php';

echo_realtime ( "[kaifu]end. ALL TIME (s) = " . (microtime ( true ) - $all_stime) );


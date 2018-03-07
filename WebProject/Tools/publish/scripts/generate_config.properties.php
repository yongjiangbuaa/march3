<?php
defined('PUBLISH_DIR') || define ( 'PUBLISH_DIR', dirname ( __DIR__ ) );
defined('SCRIPTS_DIR') || define ( 'SCRIPTS_DIR', dirname ( __DIR__ ) .'/scripts');
defined('DBSCRIPTS_DIR') || define ( 'DBSCRIPTS_DIR', dirname ( __DIR__ ) .'/db');
require_once SCRIPTS_DIR.'/base.inc.php';
require_once DBSCRIPTS_DIR.'/db.inc.php';

$template = file_get_contents('/usr/local/cok/SFS2X/extensions/COK/config.properties');
$environment_list = array();

$server_list = get_server_list();
$out_dir = PACKAGE_RESOURCE_DIR;
if (isset($_REQUEST['output_config_dir'])) {
	$out_dir = $_REQUEST['output_config_dir'];
}

// TODO
$publish_redisserver_ip = '';
// TODO
$publish_dbserver_ip = '';

$cnt = 0;
foreach ($server_list as $record) {
	$id = $record['svr_id'];
	if ($id == 0) {
		continue;
	}
	$db_ref = $record['db_ref'];
	
	$content = str_replace('debug=true', 'debug=false', $template);
	$content = str_replace('admin123', '', $content);// TODO
	
	$content = str_replace('127.0.0.1:3306/cokdb1', $db_ref, $content);
	$content = str_replace($publish_dbserver_ip.':3306/cokdb1', $db_ref, $content);

	$content = str_replace('global.redis.ip=127.0.0.1', 'global.redis.ip='.GLOBAL_REDIS_SERVER_IP, $content);
	$content = str_replace('global.redis.ip='.$publish_redisserver_ip, 'global.redis.ip='.GLOBAL_REDIS_SERVER_IP, $content);
	
	$content = str_replace('127.0.0.1:3306/cokdb_global', GLOBAL_DB_SERVER_IP.':3306/cokdb_global', $content);
	$content = str_replace($publish_dbserver_ip.':3306/cokdb_global', GLOBAL_DB_SERVER_IP.':3306/cokdb_global', $content);
	
	if ($id > 5) {
		$content = str_replace('realtime_territory_clear_old=true', 'realtime_territory_clear_old=false', $content);
	}
	if ($id <= 50) {
		$content = str_replace('realtime_translationTarget=en|ru|de|ja', 'realtime_translationTarget=en|ru|de', $content);
	}
	
	$realtime_ms_client_id = '';
	$realtime_ms_client_secret = '';

	$pattern = "/^realtime_ms_client_id=.*?$/m";
	$replacement = 'realtime_ms_client_id='.$realtime_ms_client_id;
	$content = preg_replace($pattern, $replacement, $content);
	$pattern = "/^realtime_ms_client_secret=.*?$/m";
	$replacement = 'realtime_ms_client_secret='.$realtime_ms_client_secret;
	$content = preg_replace($pattern, $replacement, $content);
	
// 	if (in_array($id, array(152,188,191,194,197,199,236,263))) {
// 		$content = str_replace('syn_world_xml=0', 'syn_world_xml=1', $content);
// 	}
	if (false) {
		$content = str_replace('syn_world_redis=0', 'syn_world_redis=1', $content);
	}
	
// 	$pattern = '/realtime_app_(\d)\.(\d)\.(\d+)=2\|/';
// 	$replacement = 'realtime_app_${1}.${2}.${3}=0|';
// 	$content = preg_replace($pattern, $replacement, $content);
	
// 	if ($id >= 500 || in_array($id, array(77,302,329))) {
// 		$content .= "\nmobile_steal_account=true\n";
// 	}
	
	if (strpos($out_dir, 'onlineconfig') > 0) {
		$fn = $out_dir . '/onlineconfig'.$id;
	}else{
		$fn = $out_dir . '/config.properties';
	}
	file_put_contents ($fn, $content );
	$cnt++;
// 	echo_realtime ( "done. " . $fn );
}
echo_realtime ( "done. $out_dir filecnt=" . $cnt );

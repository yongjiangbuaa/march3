<?php
defined('PUBLISH_DIR') || define ( 'PUBLISH_DIR', dirname ( __DIR__ ) );
defined('SCRIPTS_DIR') || define ( 'SCRIPTS_DIR', dirname ( __DIR__ ) .'/scripts');
defined('DBSCRIPTS_DIR') || define ( 'DBSCRIPTS_DIR', dirname ( __DIR__ ) .'/db');
require_once SCRIPTS_DIR.'/base.inc.php';
require_once DBSCRIPTS_DIR.'/db.inc.php';

$server_list = get_server_list();
$out_dir = PACKAGE_RESOURCE_DIR;
if (isset($_REQUEST['output_config_dir'])) {
	$out_dir = $_REQUEST['output_config_dir'];
}

$t0 = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>';
$t1 = '<tns:database xmlns:tns="http://www.iw.com/sns/platform/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">';
$t2 = '  <Group id="1">';
file_put_contents ( $out_dir . '/servers.xml', "$t0\n$t1\n$t2\n" );

$client_redis = new Redis();
$client_redis->connect(GLOBAL_REDIS_SERVER_IP);
$server_name_map = $client_redis->hGetAll("server_king_info");

foreach ($server_list as $record) {
	$id = $record['svr_id'];
	$name = null;
	if ($id == 0) {
		$id = 700000;
		$ip = $record['ip_pub'];
		$name = $record['svr_name'];
	}else{
		$ip = "s{$id}.coq.elexapp.com";
		if (!empty($server_name_map) && !empty($server_name_map[$id])) {
			$info = json_decode($server_name_map[$id], true);
			$name = $info['name'];
// 			$name = str_replace('\u001b', '', $name);
		}
		if (empty($name)) {
			$name = 'Kingdom#'.$id;
		}
	}
	
	$specialchars = array(
			'&' => '&amp;',
			'<' => '&lt;',
			'>' => '&gt;',
			'"' => '&quot;',
			"'" => '&apos;',
	);
	foreach ($specialchars as $s => $r) {
		$name = str_replace($s, $r, $name);
	}
	
	$db_ref = $record['db_ref'];
	$port = $record['port'];
	
	$darr = explode('/', $db_ref);
	$tmp = explode(':', $darr[0]);
	$db_ip = $tmp[0];
	$db_name = $darr[1];
	$server_info = array (
			'id' => $id,
			'name' => $name,
			'ip' => $ip,
			'port' => $port,
			'zone' => $record['zone'],
			'recommend' => $record['is_recommend'] == 1 ? 'true' : 'false',
			'hot' => $record['is_hot'] == 1 ? 'true' : 'false',
			'new' => $record['is_new'] == 1 ? 'true' : 'false',
			'test' => $record['is_test'] == 1 ? 'true' : 'false',
			'open_time' => date ( 'Y-n-j G:i:s', $record['open_time']),
			'inner_ip' => $record['ip_inner'],
			'db_ip' => $db_ip,
			'db_name' => $db_name,
	);
	
	if (false && $id == 565) {
		$server_info['test'] = 'true';
		$xml_item = build_xml_item ( $server_info );
		file_put_contents ( $out_dir . '/servers.xml', "$xml_item\n", FILE_APPEND );
		$server_info['test'] = 'false';
		$xml_item = build_xml_item ( $server_info );
		file_put_contents ( $out_dir . '/servers.xml', "$xml_item\n", FILE_APPEND );
	}else{
		$xml_item = build_xml_item ( $server_info );
		file_put_contents ( $out_dir . '/servers.xml', "$xml_item\n", FILE_APPEND );
	}
}
file_put_contents ( $out_dir . '/servers.xml', "  </Group>\n</tns:database>\n", FILE_APPEND );
echo_realtime ( "done. " . $out_dir . '/servers.xml' );

// <ItemSpec id="3" name="server3" ip="184.173.110.106" port="8088" zone="COK3" recommend="false" new="true" test="false" open_time="2014-9-19 9:00:00"/>
function build_xml_item($server_info) {
	foreach ( $server_info as $key => $value ) {
		$nodes [] = "$key=" . '"' . $value . '"';
	}
	return str_pad ( ' ', 4 ) . '<ItemSpec ' . implode ( ' ', $nodes ) . '/>';
}
